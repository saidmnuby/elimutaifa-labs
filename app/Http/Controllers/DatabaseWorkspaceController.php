<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\ProjectDatabase;
use App\Models\ProjectDatabaseRequest;
use App\Models\ServiceModule;
use App\Models\User;
use App\Support\SystemMailer;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class DatabaseWorkspaceController extends Controller
{
    public function requestForm(Request $request): View
    {
        abort_unless(ServiceModule::where('key', 'database')->where('is_enabled', true)->exists(), 404);

        $projects = $request->user()->projects()
            ->where('projects.status', '!=', Project::STATUS_SUSPENDED)
            ->orderBy('name')
            ->get();
        $selectedProjectId = $projects->contains('id', (int) $request->query('project_id'))
            ? (int) $request->query('project_id')
            : null;

        return view('database.request', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'databasePrefix' => config('platform.cpanel_database_prefix'),
        ]);
    }

    public function storeRequest(Request $request, SystemMailer $mailer): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'database_name_suffix' => ['required', 'string', 'max:55', 'regex:/^[A-Za-z][A-Za-z0-9_]*$/'],
        ]);

        $project = $request->user()->projects()
            ->whereKey($validated['project_id'])
            ->where('projects.status', '!=', Project::STATUS_SUSPENDED)
            ->firstOrFail();
        $this->authorizeProject($project);

        $module = ServiceModule::where('key', 'database')->where('is_enabled', true)->firstOrFail();
        $databaseRequest = DB::transaction(function () use ($request, $project, $validated, $module) {
            $lockedProject = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            $databaseLimit = $lockedProject->owner->databaseLimitPerProject();
            $databaseCount = $lockedProject->databases()
                ->whereIn('status', ['pending', 'active'])
                ->count()
                + $lockedProject->databaseRequests()
                    ->where('status', ProjectDatabaseRequest::STATUS_PENDING)
                    ->count();

            if ($databaseLimit !== null && $databaseCount >= $databaseLimit) {
                throw ValidationException::withMessages([
                    'project_id' => "The {$lockedProject->owner->planLimits()['label']} plan allows up to {$databaseLimit} databases for each project.",
                ]);
            }

            $databaseRequest = ProjectDatabaseRequest::create([
                'project_id' => $lockedProject->id,
                'requested_name_suffix' => strtolower($validated['database_name_suffix']),
                'status' => ProjectDatabaseRequest::STATUS_PENDING,
            ]);

            if (! $lockedProject->modules()->where('service_modules.id', $module->id)->exists()) {
                $lockedProject->modules()->attach($module->id, ['status' => 'pending']);
            }

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'project_id' => $lockedProject->id,
                'action' => 'database.requested',
                'subject_type' => ProjectDatabaseRequest::class,
                'subject_id' => (string) $databaseRequest->id,
                'details' => ['project_id' => $lockedProject->id, 'requested_name_suffix' => $databaseRequest->requested_name_suffix],
                'ip_address' => $request->ip(),
            ]);

            return $databaseRequest;
        });

        $mailer->admins('New database request · TechwareTz Control Center (TCC)', [
            'eyebrow' => 'Database request',
            'title' => 'A database needs review',
            'intro' => 'A developer requested a database for a project.',
            'details' => [
                'Project' => $project->name,
                'Developer' => $request->user()->name,
                'Requested name' => $databaseRequest->requested_name_suffix,
            ],
            'button_label' => 'Review database requests',
            'button_url' => route('admin.database-requests'),
        ]);

        return redirect()->route('modules.show', 'database')->with('success', 'Your database request was sent for review.');
    }

    public function show(ProjectDatabase $database): View
    {
        $this->authorizeDatabase($database);

        $tables = [];
        $connectionError = null;
        try {
            if (blank($database->encrypted_password)) {
                $connectionError = 'The database password has not been added to the portal yet.';
            } else {
                $tables = $this->listTables($this->connection($database));
            }
        } catch (Throwable) {
            $connectionError = 'We could not read the connection. Please ask an administrator to check or update the database details.';
        }

        return view('database.show', [
            'databaseRecord' => $database,
            'tables' => $tables,
            'connectionError' => $connectionError,
            'queryRows' => [],
            'queryColumns' => [],
            'queryMessage' => null,
            'queryError' => null,
        ]);
    }

    public function credentials(Request $request, ProjectDatabase $database): JsonResponse
    {
        $this->authorizeDatabase($database);

        if (blank($database->encrypted_password)) {
            return response()->json([
                'message' => 'The database password has not been added to the portal yet.',
            ], 409)->header('Cache-Control', 'private, no-store, no-cache, max-age=0');
        }

        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'project_id' => $database->project_id,
            'action' => 'database.credentials.viewed',
            'subject_type' => ProjectDatabase::class,
            'subject_id' => (string) $database->id,
            'details' => ['database_id' => $database->id],
            'ip_address' => $request->ip(),
        ]);

        $environment = implode("\n", [
            'DB_CONNECTION=mysql',
            'DB_HOST='.$this->dotenvValue($database->host),
            'DB_PORT=3306',
            'DB_DATABASE='.$this->dotenvValue($database->database_name),
            'DB_USERNAME='.$this->dotenvValue($database->database_user),
            'DB_PASSWORD='.$this->dotenvValue($database->encrypted_password),
        ]);

        return response()->json([
            'environment' => $environment,
        ])->header('Cache-Control', 'private, no-store, no-cache, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function export(Request $request, ProjectDatabase $database)
    {
        $this->authorizeDatabase($database);
        $connection = $this->connection($database);
        $pdo = $connection->getPdo();
        $tableNames = $this->listTables($connection);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', $database->database_name).'-backup-'.now()->format('Y-m-d-His').'.sql';

        return Response::streamDownload(function () use ($pdo, $tableNames, $database, $request) {
            echo "-- SQL backup for {$database->database_name}\n";
            echo '-- Exported at '.now()->toIso8601String()."\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tableNames as $table) {
                $identifier = '`'.str_replace('`', '``', $table).'`';
                $create = $pdo->query("SHOW CREATE TABLE {$identifier}")->fetch(\PDO::FETCH_ASSOC);
                $createSql = $create['Create Table'] ?? null;
                if (! is_string($createSql)) {
                    continue;
                }

                echo "DROP TABLE IF EXISTS {$identifier};\n".$createSql.";\n\n";
                $statement = $pdo->query("SELECT * FROM {$identifier}");
                $columns = [];
                for ($index = 0; $index < $statement->columnCount(); $index++) {
                    $meta = $statement->getColumnMeta($index);
                    $columns[] = '`'.str_replace('`', '``', $meta['name']).'`';
                }
                if ($columns === []) {
                    continue;
                }

                $columnList = implode(', ', $columns);
                $batch = [];
                while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
                    $values = array_map(static function ($value) use ($pdo): string {
                        if ($value === null) {
                            return 'NULL';
                        }
                        $quoted = $pdo->quote((string) $value, \PDO::PARAM_STR);
                        if ($quoted === false) {
                            throw new \RuntimeException('Could not quote a database value for export.');
                        }

                        return $quoted;
                    }, $row);
                    $batch[] = '('.implode(', ', $values).')';

                    if (count($batch) === 100) {
                        echo 'INSERT INTO '.$identifier.' ('.$columnList.") VALUES\n".implode(",\n", $batch).";\n";
                        $batch = [];
                    }
                }

                if ($batch !== []) {
                    echo 'INSERT INTO '.$identifier.' ('.$columnList.") VALUES\n".implode(",\n", $batch).";\n";
                }
                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
            ActivityLogger::record($request, 'database.exported', $database->project_id, ProjectDatabase::class, $database->id, ['table_count' => count($tableNames)]);
        }, $filename, ['Content-Type' => 'application/sql; charset=UTF-8']);
    }

    public function import(Request $request, ProjectDatabase $database): RedirectResponse
    {
        $this->authorizeDatabase($database);
        abort_unless(extension_loaded('pdo_mysql') && defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS'), 503, 'SQL import is unavailable on this server.');

        $request->validate([
            'sql_dump' => ['required', 'file', 'max:20480'],
        ]);
        $file = $request->file('sql_dump');
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return back()->withErrors(['sql_dump' => 'Choose a .sql dump file.']);
        }

        $sql = file_get_contents($file->getRealPath());
        if (! is_string($sql) || trim($sql) === '') {
            return back()->withErrors(['sql_dump' => 'The SQL dump is empty or could not be read.']);
        }
        if (preg_match('/(?:^|;)\s*(?:USE\s+|CREATE\s+(?:DATABASE|SCHEMA)\b|DROP\s+(?:DATABASE|SCHEMA)\b|GRANT\b|REVOKE\b|LOAD\s+DATA\b)/im', $sql)
            || preg_match('/\b(?:INTO\s+(?:OUTFILE|DUMPFILE)|LOAD_FILE\s*\()/i', $sql)) {
            return back()->withErrors(['sql_dump' => 'This dump contains server-level commands. Remove database selection, privilege, or file access commands and try again.']);
        }

        try {
            $connection = $this->connection($database);
            $config = $connection->getConfig();
            $pdo = new \PDO(
                'mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4',
                $config['username'],
                $config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
                ],
            );
            $pdo->exec($sql);

            ActivityLogger::record($request, 'database.imported', $database->project_id, ProjectDatabase::class, $database->id, [
                'filename' => basename($file->getClientOriginalName()),
                'bytes' => $file->getSize(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['sql_dump' => 'Import failed. The dump may contain unsupported SQL or exceed this database user’s permissions. Some earlier statements may already have been applied.']);
        }

        return redirect()->route('database.show', $database)->with('success', 'SQL dump imported successfully.');
    }

    public function browseTable(ProjectDatabase $database, string $table): View
    {
        $this->authorizeDatabase($database);
        try {
            $connection = $this->connection($database);
            $tables = $this->listTables($connection);
        } catch (Throwable) {
            abort(503, 'Database connection could not be opened.');
        }
        abort_unless(in_array($table, $tables, true), 404);

        try {
            $columns = Schema::connection($this->connectionName($database))->getColumnListing($table);
            $rows = $connection->table($table)->paginate(25);
        } catch (Throwable) {
            abort(503, 'Database table could not be opened.');
        }

        return view('database.table', compact('database', 'table', 'tables', 'columns', 'rows'));
    }

    public function runQuery(Request $request, ProjectDatabase $database): View
    {
        $this->authorizeDatabase($database);
        $validated = $request->validate(['sql' => ['required', 'string', 'max:10000']]);
        $sql = trim($validated['sql']);
        $sql = rtrim($sql, ';');
        $verb = strtoupper(strtok($sql, " \t\r\n"));
        $readVerbs = ['SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN'];
        $allowedStatement = preg_match('/^(?:SELECT\b|SHOW\s+(?:TABLES|COLUMNS|INDEX|INDEXES|CREATE\s+TABLE|TABLE\s+STATUS)\b|DESCRIBE\b|DESC\b|EXPLAIN\b|INSERT\s+INTO\b|UPDATE\b|DELETE\s+FROM\b|CREATE\s+(?:TABLE|VIEW|(?:UNIQUE\s+)?INDEX)\b|ALTER\s+TABLE\b|DROP\s+(?:TABLE|VIEW|INDEX)\b|TRUNCATE\s+TABLE\b|RENAME\s+TABLE\b)/i', $sql);
        $tables = [];
        $queryRows = [];
        $queryColumns = [];
        $queryMessage = null;
        $queryError = null;
        $connectionError = null;

        if (! $allowedStatement
            || str_contains($sql, ';')
            || preg_match('/\b(?:CREATE|DROP)\s+DATABASE\b|\bUSE\b|\b(?:GRANT|REVOKE|SHUTDOWN)\b|\bSHOW\s+(?:DATABASES|SCHEMAS)\b|\bINTO\s+(?:OUTFILE|DUMPFILE)\b|\bLOAD\s+DATA\b|\bLOAD_FILE\s*\(|\b(?:mysql|information_schema|performance_schema|sys)\s*\./i', $sql)
            || str_contains($sql, '/*') || preg_match('/--|(^|\s)#/', $sql)) {
            $queryError = 'This query is not allowed. Run one command in this database only.';
        } else {
            try {
                $connection = $this->connection($database);
                if (in_array($verb, $readVerbs, true)) {
                    if ($verb === 'SELECT' && ! preg_match('/\bLIMIT\s+\d+/i', $sql)) {
                        $sql .= ' LIMIT 100';
                    }
                    if ($verb === 'SELECT' && preg_match('/\bLIMIT\s+(\d+)/i', $sql, $limit) && (int) $limit[1] > 100) {
                        throw new \InvalidArgumentException('Maximum query result is 100 rows.');
                    }
                    $queryRows = $connection->select($sql);
                    $queryColumns = isset($queryRows[0]) ? array_keys((array) $queryRows[0]) : [];
                    $queryMessage = count($queryRows).' rows returned (maximum 100).';
                    ActivityLogger::record($request, 'database.query_executed', $database->project_id, ProjectDatabase::class, $database->id, ['operation' => $verb]);
                } else {
                    $connection->unprepared($sql);
                    $queryMessage = 'Database command completed.';
                    ActivityLogger::record($request, 'database.query_executed', $database->project_id, ProjectDatabase::class, $database->id, ['operation' => $verb]);
                }
            } catch (\InvalidArgumentException $exception) {
                $queryError = $exception->getMessage();
            } catch (Throwable $exception) {
                report($exception);

                $queryError = 'Query failed. Check its syntax and the permissions of this project database user.';
            }
        }

        try {
            $tables = $this->listTables($this->connection($database));
        } catch (Throwable) {
            $connectionError = 'The database connection failed. Please ask an administrator to check the connection details.';
        }

        return view('database.show', [
            'databaseRecord' => $database,
            'tables' => $tables,
            'connectionError' => $connectionError,
            'queryRows' => $queryRows,
            'queryColumns' => $queryColumns,
            'queryMessage' => $queryMessage,
            'queryError' => $queryError,
        ]);
    }

    private function authorizeProject(Project $project): void
    {
        $user = auth()->user();
        abort_unless(
            $user->role === User::ROLE_DEVELOPER
                && $user->status === User::STATUS_ACTIVE
                && $project->owner_id === $user->id
                && $project->status !== Project::STATUS_SUSPENDED,
            403
        );
    }

    private function authorizeDatabase(ProjectDatabase $database): void
    {
        $this->authorizeProject($database->project()->firstOrFail());
        abort_unless($database->status === 'active', 403);
        abort_unless($database->project->modules()
            ->where('service_modules.key', 'database')
            ->where('service_modules.is_enabled', true)
            ->wherePivot('status', 'active')
            ->exists(), 403);
    }

    private function connectionName(ProjectDatabase $database): string
    {
        return 'developer_project_database_'.$database->id;
    }

    private function dotenvValue(string $value): string
    {
        $escaped = str_replace(
            ['\\', '"', '$', "\r", "\n"],
            ['\\\\', '\\"', '\\$', '\\r', '\\n'],
            $value,
        );

        return '"'.$escaped.'"';
    }

    private function connection(ProjectDatabase $database)
    {
        $connectionName = $this->connectionName($database);
        config(['database.connections.'.$connectionName => [
            'driver' => 'mysql',
            'host' => $database->host,
            'port' => 3306,
            'database' => $database->database_name,
            'username' => $database->database_user,
            'password' => $database->encrypted_password,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? [
                \PDO::ATTR_EMULATE_PREPARES => false,
            ] : [],
        ]]);
        DB::purge($connectionName);

        return DB::connection($connectionName);
    }

    private function listTables($connection): array
    {
        return collect($connection->select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0] ?? null)
            ->filter(fn ($table) => is_string($table))
            ->values()
            ->all();
    }
}
