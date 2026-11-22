<?php

namespace App\Http\Controllers;

use App\Models\ProjectDatabase;
use App\Models\ServiceModule;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class DatabaseTableController extends Controller
{
    public function show(ProjectDatabase $database, string $table): View
    {
        $this->authorizeDatabase($database);
        $connection = $this->connection($database);
        $this->assertTableExists($connection, $table);

        $columns = $this->columnMetadata($database, $table);
        $primaryKey = $this->primaryKey($columns);
        $query = $connection->table($table);
        if ($primaryKey) {
            $query->orderBy($primaryKey);
        }
        $rows = $query->paginate(25)->withQueryString();

        return view('database.table', compact('database', 'table', 'columns', 'primaryKey', 'rows'));
    }

    public function createTable(ProjectDatabase $database): View
    {
        $this->authorizeDatabase($database);

        return view('database.table-create', compact('database'));
    }

    public function storeTable(Request $request, ProjectDatabase $database): RedirectResponse
    {
        $this->authorizeDatabase($database);
        $validated = $request->validate([
            'table_name' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z][A-Za-z0-9_]*$/'],
            'columns' => ['required', 'array', 'min:1', 'max:30'],
            'columns.*.name' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z][A-Za-z0-9_]*$/'],
            'columns.*.type' => ['required', Rule::in(['varchar', 'text', 'integer', 'bigint', 'decimal', 'boolean', 'date', 'datetime'])],
            'columns.*.length' => ['nullable', 'integer', 'between:1,255'],
            'columns.*.nullable' => ['nullable', 'boolean'],
            'columns.*.primary' => ['nullable', 'boolean'],
            'columns.*.auto_increment' => ['nullable', 'boolean'],
        ]);

        $names = array_map(fn ($column) => strtolower($column['name']), $validated['columns']);
        if (count($names) !== count(array_unique($names))) {
            return back()->withInput()->withErrors(['columns' => 'Majina ya columns lazima yawe tofauti.']);
        }
        $primaryCount = collect($validated['columns'])->where('primary', true)->count();
        if ($primaryCount > 1) {
            return back()->withInput()->withErrors(['columns' => 'Kwa sasa chagua primary key moja tu.']);
        }

        foreach ($validated['columns'] as $column) {
            if (! empty($column['auto_increment'])
                && (empty($column['primary']) || ! in_array($column['type'], ['integer', 'bigint'], true))) {
                return back()->withInput()->withErrors(['columns' => 'Auto increment inahitaji primary key ya integer au bigint.']);
            }
        }

        $connectionName = $this->connectionName($database);
        $connection = $this->connection($database);
        if (Schema::connection($connectionName)->hasTable($validated['table_name'])) {
            return back()->withInput()->withErrors(['table_name' => 'Table hilo lipo tayari.']);
        }

        try {
            Schema::connection($connectionName)->create($validated['table_name'], function (Blueprint $blueprint) use ($validated) {
                foreach ($validated['columns'] as $column) {
                    $name = $column['name'];
                    $definition = match ($column['type']) {
                        'varchar' => $blueprint->string($name, (int) ($column['length'] ?? 255)),
                        'text' => $blueprint->text($name),
                        'integer' => ! empty($column['auto_increment']) ? $blueprint->increments($name) : $blueprint->integer($name),
                        'bigint' => ! empty($column['auto_increment']) ? $blueprint->bigIncrements($name) : $blueprint->bigInteger($name),
                        'decimal' => $blueprint->decimal($name, 10, 2),
                        'boolean' => $blueprint->boolean($name),
                        'date' => $blueprint->date($name),
                        'datetime' => $blueprint->dateTime($name),
                    };

                    if (! empty($column['nullable'])) {
                        $definition->nullable();
                    }
                    if (! empty($column['primary']) && empty($column['auto_increment'])) {
                        $definition->primary();
                    }
                }
            });
        } catch (Throwable) {
            return back()->withInput()->withErrors(['table_name' => 'Table halikuundwa. Kagua majina na ruhusa za database user.']);
        }

        return redirect()->route('database.tables.show', [$database, $validated['table_name']])->with('success', 'Table limeundwa.');
    }

    public function dropTable(Request $request, ProjectDatabase $database, string $table): RedirectResponse
    {
        $this->authorizeDatabase($database);
        $this->assertTableExists($this->connection($database), $table);
        $request->validate(['confirm_table' => ['required', Rule::in([$table])]]);

        Schema::connection($this->connectionName($database))->drop($table);

        return redirect()->route('database.show', $database)->with('success', "Table {$table} limefutwa.");
    }

    public function createRow(ProjectDatabase $database, string $table): View
    {
        $this->authorizeDatabase($database);
        $this->assertTableExists($this->connection($database), $table);
        $columns = $this->columnMetadata($database, $table);

        return view('database.row-form', [
            'database' => $database,
            'table' => $table,
            'columns' => $columns,
            'row' => null,
            'editing' => false,
            'primaryKey' => $this->primaryKey($columns),
        ]);
    }

    public function storeRow(Request $request, ProjectDatabase $database, string $table): RedirectResponse
    {
        $this->authorizeDatabase($database);
        $connection = $this->connection($database);
        $this->assertTableExists($connection, $table);
        $columns = $this->columnMetadata($database, $table);
        $validated = $request->validate(['data' => ['required', 'array']]);
        $values = $this->valuesFromRequest($validated['data'], $columns, false);

        if ($values === []) {
            return back()->withInput()->withErrors(['data' => 'Weka angalau thamani moja ya record.']);
        }

        try {
            $connection->table($table)->insert($values);
        } catch (Throwable) {
            return back()->withInput()->withErrors(['data' => 'Record haikuongezwa. Kagua thamani na required fields za table.']);
        }

        return redirect()->route('database.tables.show', [$database, $table])->with('success', 'Record imeongezwa.');
    }

    public function editRow(ProjectDatabase $database, string $table, string $rowKey): View
    {
        $this->authorizeDatabase($database);
        $connection = $this->connection($database);
        $this->assertTableExists($connection, $table);
        $columns = $this->columnMetadata($database, $table);
        $primaryKey = $this->primaryKey($columns);
        abort_unless($primaryKey, 404, 'Table needs a single primary key to edit records.');
        $row = $connection->table($table)->where($primaryKey, $rowKey)->firstOrFail();

        return view('database.row-form', compact('database', 'table', 'columns', 'row', 'primaryKey') + ['editing' => true, 'rowKey' => $rowKey]);
    }

    public function updateRow(Request $request, ProjectDatabase $database, string $table, string $rowKey): RedirectResponse
    {
        $this->authorizeDatabase($database);
        $connection = $this->connection($database);
        $this->assertTableExists($connection, $table);
        $columns = $this->columnMetadata($database, $table);
        $primaryKey = $this->primaryKey($columns);
        abort_unless($primaryKey, 404);
        $validated = $request->validate(['data' => ['required', 'array']]);
        $values = $this->valuesFromRequest($validated['data'], $columns, true);
        if ($values === []) {
            return back()->withInput()->withErrors(['data' => 'Hakuna mabadiliko ya kuhifadhi.']);
        }

        try {
            $updated = $connection->table($table)->where($primaryKey, $rowKey)->update($values);
        } catch (Throwable) {
            return back()->withInput()->withErrors(['data' => 'Mabadiliko hayajahifadhiwa. Kagua data na constraints za table.']);
        }
        abort_unless($connection->table($table)->where($primaryKey, $rowKey)->exists(), 404);

        return redirect()->route('database.tables.show', [$database, $table])->with('success', $updated ? 'Record imesasishwa.' : 'Hakuna mabadiliko mapya kwenye record.');
    }

    public function deleteRow(ProjectDatabase $database, string $table, string $rowKey): RedirectResponse
    {
        $this->authorizeDatabase($database);
        $connection = $this->connection($database);
        $this->assertTableExists($connection, $table);
        $primaryKey = $this->primaryKey($this->columnMetadata($database, $table));
        abort_unless($primaryKey, 404, 'Table needs a single primary key to delete records safely.');
        $deleted = $connection->table($table)->where($primaryKey, $rowKey)->delete();
        abort_unless($deleted === 1, 404);

        return redirect()->route('database.tables.show', [$database, $table])->with('success', 'Record imefutwa.');
    }

    private function authorizeDatabase(ProjectDatabase $database): void
    {
        $project = $database->project()->firstOrFail();
        $user = auth()->user();
        abort_unless(
            $user->role === User::ROLE_DEVELOPER
                && $user->status === User::STATUS_ACTIVE
                && $project->owner_id === $user->id
                && $project->status !== Project::STATUS_SUSPENDED,
            403
        );
        abort_unless($database->status === 'active', 403);
        abort_unless($project->modules()
            ->where('service_modules.key', 'database')
            ->where('service_modules.is_enabled', true)
            ->wherePivot('status', 'active')
            ->exists(), 403);
    }

    private function connectionName(ProjectDatabase $database): string
    {
        return 'developer_project_database_'.$database->id;
    }

    private function connection(ProjectDatabase $database)
    {
        $name = $this->connectionName($database);
        config(['database.connections.'.$name => [
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
            'options' => extension_loaded('pdo_mysql') ? [\PDO::ATTR_EMULATE_PREPARES => false] : [],
        ]]);
        DB::purge($name);

        return DB::connection($name);
    }

    private function assertTableExists($connection, string $table): void
    {
        abort_unless(in_array($table, $this->listTables($connection), true), 404);
    }

    private function listTables($connection): array
    {
        return collect($connection->select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0] ?? null)
            ->filter(fn ($name) => is_string($name))
            ->values()
            ->all();
    }

    private function columnMetadata(ProjectDatabase $database, string $table): array
    {
        $quoted = '`'.str_replace('`', '``', $table).'`';

        return array_map(fn ($column) => (array) $column, $this->connection($database)->select("SHOW COLUMNS FROM {$quoted}"));
    }

    private function primaryKey(array $columns): ?string
    {
        $keys = array_values(array_filter($columns, fn ($column) => ($column['Key'] ?? null) === 'PRI'));

        return count($keys) === 1 ? $keys[0]['Field'] : null;
    }

    private function valuesFromRequest(array $input, array $columns, bool $updating): array
    {
        $values = [];
        $primaryKey = $this->primaryKey($columns);
        foreach ($columns as $column) {
            $field = $column['Field'];
            if (($column['Extra'] ?? '') !== '' && str_contains(strtolower($column['Extra']), 'generated')) {
                continue;
            }
            if (str_contains(strtolower($column['Extra'] ?? ''), 'auto_increment')) {
                continue;
            }
            if ($updating && $field === $primaryKey) {
                continue;
            }
            if (! array_key_exists($field, $input)) {
                continue;
            }

            $value = $input[$field];
            if (is_array($value)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['data' => 'Values lazima ziwe text au number.']);
            }
            if ($value === '' && ($column['Null'] ?? 'NO') === 'YES') {
                $value = null;
            } elseif ($value === '' && ($column['Default'] ?? null) !== null && ! $updating) {
                continue;
            }
            $values[$field] = $value;
        }

        return $values;
    }
}
