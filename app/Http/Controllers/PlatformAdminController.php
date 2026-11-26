<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\ProjectDatabase;
use App\Models\ProjectDatabaseRequest;
use App\Models\ServiceModule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlatformAdminController extends Controller
{
    public function developers(): View
    {
        $developerQuery = User::where('role', User::ROLE_DEVELOPER);

        return view('admin.developers', [
            'developers' => (clone $developerQuery)->latest()->paginate(20),
            'metrics' => [
                'total' => (clone $developerQuery)->count(),
                'approved' => (clone $developerQuery)->where('status', User::STATUS_ACTIVE)->count(),
                'pending' => (clone $developerQuery)->where('status', User::STATUS_PENDING)->count(),
                'rejected' => (clone $developerQuery)->where('status', User::STATUS_REJECTED)->count(),
                'suspended' => (clone $developerQuery)->where('status', User::STATUS_SUSPENDED)->count(),
            ],
        ]);
    }

    public function updateDeveloperStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== User::ROLE_DEVELOPER) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended,rejected'],
        ]);

        $oldStatus = $user->status;
        if ($validated['status'] === User::STATUS_REJECTED && $oldStatus !== User::STATUS_PENDING) {
            return back()->withErrors(['developer' => 'An approved account cannot be rejected. Suspend it instead.']);
        }
        $user->forceFill(['status' => $validated['status']])->save();

        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'action' => 'developer.status_changed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'details' => ['from' => $oldStatus, 'to' => $user->status],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "The account status for {$user->email} was updated.");
    }

    public function projects(): View
    {
        $projectQuery = Project::query();

        return view('admin.projects.index', [
            'projects' => Project::with(['owner', 'modules', 'repositories', 'domains', 'databases', 'databaseRequests'])
                ->latest()
                ->paginate(20),
            'metrics' => [
                'total' => (clone $projectQuery)->count(),
                'approved' => (clone $projectQuery)->where('status', Project::STATUS_ACTIVE)->count(),
                'pending' => (clone $projectQuery)->where('status', Project::STATUS_PENDING)->count(),
                'rejected' => (clone $projectQuery)->where('status', Project::STATUS_REJECTED)->count(),
                'suspended' => (clone $projectQuery)->where('status', Project::STATUS_SUSPENDED)->count(),
            ],
            'databasePrefix' => config('platform.cpanel_database_prefix'),
        ]);
    }

    public function rejectProject(Request $request, Project $project): RedirectResponse
    {
        if ($project->status !== Project::STATUS_PENDING) {
            return back()->withErrors(['project' => 'Only pending requests can be rejected.']);
        }

        $project->forceFill(['status' => Project::STATUS_REJECTED])->save();
        DB::table('project_modules')
            ->where('project_id', $project->id)
            ->whereIn('service_module_id', ServiceModule::where('key', 'github-deploy')->select('id'))
            ->update(['status' => Project::STATUS_REJECTED, 'updated_at' => now()]);

        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'project_id' => $project->id,
            'action' => 'project.rejected',
            'subject_type' => Project::class,
            'subject_id' => (string) $project->id,
            'details' => [],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "The deployment request for {$project->name} was rejected.");
    }

    public function databaseRequests(): View
    {
        $requests = ProjectDatabaseRequest::with(['project.owner'])
            ->where('status', ProjectDatabaseRequest::STATUS_PENDING)
            ->latest()
            ->paginate(20);

        return view('admin.database-requests', [
            'requests' => $requests,
            'prefix' => config('platform.cpanel_database_prefix'),
        ]);
    }

    public function approveDatabaseRequest(Request $request, ProjectDatabaseRequest $databaseRequest): RedirectResponse
    {
        $databaseRequest->load('project');
        $project = $databaseRequest->project;
        if ($databaseRequest->status !== ProjectDatabaseRequest::STATUS_PENDING) {
            return back()->withErrors(['database_request' => 'This request is no longer in the queue.']);
        }

        $module = ServiceModule::where('key', 'database')->firstOrFail();

        $validated = $request->validate([
            'database_host' => ['required', 'string', 'max:253', 'regex:/^(?:localhost|(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)*[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?|(?:\d{1,3}\.){3}\d{1,3})$/'],
            'database_name' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_]+$/', Rule::unique('project_databases', 'database_name')],
            'database_user' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_]+$/', Rule::unique('project_databases', 'database_user')],
            'database_password' => ['required', 'string', 'max:2000'],
            'created_and_assigned' => ['accepted'],
        ]);

        $prefix = (string) config('platform.cpanel_database_prefix');
        foreach (['database_name', 'database_user'] as $field) {
            if (! str_starts_with($validated[$field], $prefix) || strlen($validated[$field]) <= strlen($prefix)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => "The name must start with this cPanel prefix: {$prefix}",
                ]);
            }
        }

        DB::transaction(function () use ($request, $project, $databaseRequest, $module, $validated) {
            $lockedRequest = ProjectDatabaseRequest::whereKey($databaseRequest->id)->lockForUpdate()->firstOrFail();
            if ($lockedRequest->status !== ProjectDatabaseRequest::STATUS_PENDING) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'database_request' => 'This request has already been handled.',
                ]);
            }

            $database = ProjectDatabase::create([
                'project_id' => $project->id,
                'host' => $validated['database_host'],
                'database_name' => $validated['database_name'],
                'database_user' => $validated['database_user'],
                'encrypted_password' => $validated['database_password'],
                'status' => 'active',
            ]);
            $lockedRequest->forceFill([
                'status' => ProjectDatabaseRequest::STATUS_APPROVED,
                'reviewed_by' => $request->user()->id,
                'project_database_id' => $database->id,
                'reviewed_at' => now(),
            ])->save();

            DB::table('project_modules')
                ->where('project_id', $project->id)
                ->where('service_module_id', $module->id)
                ->update([
                    'status' => 'active',
                    'settings' => null,
                    'updated_at' => now(),
                ]);

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'project_id' => $project->id,
                'action' => 'database.approved',
                'subject_type' => ProjectDatabaseRequest::class,
                'subject_id' => (string) $databaseRequest->id,
                'details' => [
                    'project_id' => $project->id,
                    'database_name' => $validated['database_name'],
                    'database_user' => $validated['database_user'],
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.database-requests')->with('success', "The database for {$project->name} was approved and enabled.");
    }

    public function rejectDatabaseRequest(Request $request, ProjectDatabaseRequest $databaseRequest): RedirectResponse
    {
        if ($databaseRequest->status !== ProjectDatabaseRequest::STATUS_PENDING) {
            return back()->withErrors(['database_request' => 'This request is no longer in the queue.']);
        }

        DB::transaction(function () use ($request, $databaseRequest) {
            $databaseRequest->forceFill([
                'status' => ProjectDatabaseRequest::STATUS_REJECTED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ])->save();

            $hasActiveDatabase = $databaseRequest->project->databases()->where('status', 'active')->exists();
            DB::table('project_modules')
                ->where('project_id', $databaseRequest->project_id)
                ->whereIn('service_module_id', ServiceModule::where('key', 'database')->select('id'))
                ->update(['status' => $hasActiveDatabase ? 'active' : 'pending', 'updated_at' => now()]);

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'project_id' => $databaseRequest->project_id,
                'action' => 'database.rejected',
                'subject_type' => ProjectDatabaseRequest::class,
                'subject_id' => (string) $databaseRequest->id,
                'details' => ['requested_name_suffix' => $databaseRequest->requested_name_suffix],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'The database request was rejected.');
    }

    public function editProject(Project $project): View
    {
        $project->load(['modules', 'repositories', 'domains', 'databases']);

        return view('admin.projects.edit', [
            'project' => $project,
            'modules' => ServiceModule::orderBy('name')->get(),
            'selectedModuleIds' => $project->modules->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'databasePrefix' => config('platform.cpanel_database_prefix'),
        ]);
    }

    public function updateProject(Request $request, Project $project): RedirectResponse
    {
        $repository = $project->repositories()->first();
        $domain = $project->domains()->first();
        $database = $project->databases()->first();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'repository' => [
                'required', 'string', 'max:190',
                'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/',
                Rule::unique('repositories', 'full_name')->ignore($repository?->id),
            ],
            'branch' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9._\/-]+$/'],
            'hostname' => [
                'required', 'string', 'max:253',
                'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/',
                Rule::unique('project_domains', 'hostname')->ignore($domain?->id),
            ],
            'document_root' => [$project->status === Project::STATUS_ACTIVE ? 'required' : 'nullable', 'string', 'max:500'],
            'database_host' => ['nullable', 'string', 'max:253', 'required_with:database_name'],
            'database_name' => [
                'nullable', 'string', 'max:128', 'regex:/^[A-Za-z0-9_]+$/',
                'required_with:database_user',
                Rule::unique('project_databases', 'database_name')->ignore($database?->id),
            ],
            'database_user' => ['nullable', 'string', 'max:128', 'regex:/^[A-Za-z0-9_]+$/', 'required_with:database_name'],
            'database_password' => ['nullable', 'string', 'max:2000'],
            'database_status' => ['nullable', 'required_with:database_name', 'in:pending,active'],
            'secret_reference' => ['nullable', 'string', 'max:190', 'regex:/^[A-Z][A-Z0-9_]*$/'],
            'module_ids' => ['nullable', 'array'],
            'module_ids.*' => ['integer', 'distinct', 'exists:service_modules,id'],
        ]);

        if (($validated['database_status'] ?? null) === 'active'
            && filled($validated['database_name'] ?? null)
            && blank($validated['database_password'] ?? null)
            && (! $database || blank($database->encrypted_password))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'database_password' => 'Enter the database password before enabling access.',
            ]);
        }

        $documentRoot = filled($validated['document_root'] ?? null)
            ? rtrim(str_replace('\\', '/', trim($validated['document_root'])), '/')
            : null;
        if ($documentRoot !== null && (! str_starts_with($documentRoot, '/home/elimutai/') || str_contains($documentRoot, '..'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'document_root' => 'The path must be inside /home/elimutai/ and cannot contain ..',
            ]);
        }

        DB::transaction(function () use ($request, $project, $validated, $repository, $domain, $database, $documentRoot) {
            $project->forceFill([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ])->save();

            if ($repository) {
                $repository->forceFill([
                    'full_name' => $validated['repository'],
                    'github_installation_id' => strcasecmp($repository->full_name, $validated['repository']) === 0
                        ? $repository->github_installation_id
                        : null,
                    'branch' => $validated['branch'],
                ])->save();
            }

            if ($domain) {
                $domain->forceFill([
                    'hostname' => strtolower($validated['hostname']),
                    'document_root' => $documentRoot,
                    'status' => $project->status === Project::STATUS_ACTIVE ? 'active' : 'pending',
                ])->save();
            }

            $effectiveDatabasePassword = filled($validated['database_password'] ?? null)
                ? $validated['database_password']
                : $database?->encrypted_password;
            $databaseWillBeActive = $project->databases()->where('status', 'active')->exists()
                || (filled($validated['database_name'] ?? null)
                && ($validated['database_status'] ?? $database?->status) === 'active'
                && filled($effectiveDatabasePassword));
            $moduleSync = collect($validated['module_ids'] ?? [])
                ->mapWithKeys(function ($id) use ($validated, $project, $databaseWillBeActive) {
                    $module = ServiceModule::findOrFail($id);
                    $settings = null;
                    $status = match ($module->key) {
                        'github-deploy' => match ($project->status) {
                            Project::STATUS_ACTIVE => 'active',
                            Project::STATUS_REJECTED => 'rejected',
                            default => 'pending',
                        },
                        'database' => $databaseWillBeActive ? 'active' : 'pending',
                        default => 'pending',
                    };

                    return [$id => ['status' => $status, 'settings' => $settings]];
                })
                ->all();
            $project->modules()->sync($moduleSync);

            $databaseValues = [
                'host' => $validated['database_host'] ?? null,
                'database_name' => $validated['database_name'] ?? null,
                'database_user' => $validated['database_user'] ?? null,
                'secret_reference' => $validated['secret_reference'] ?? null,
                'status' => $validated['database_status'] ?? ($database?->status ?? 'pending'),
            ];
            if (filled($validated['database_password'] ?? null)) {
                $databaseValues['encrypted_password'] = $validated['database_password'];
            }
            if (! empty($databaseValues['database_name'])) {
                if ($database) {
                    $database->forceFill($databaseValues)->save();
                } else {
                    $project->databases()->create($databaseValues);
                }
            }

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'project_id' => $project->id,
                'action' => 'project.resources_updated',
                'subject_type' => Project::class,
                'subject_id' => (string) $project->id,
                'details' => ['fields' => ['project', 'repository', 'domain', 'hosting_path', 'modules', 'database_reference']],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.projects')->with('success', "Resources for {$project->name} were updated.");
    }

    public function approveProject(Request $request, Project $project): RedirectResponse
    {
        if ($project->status !== Project::STATUS_PENDING) {
            return back()->withErrors(['project' => 'This project is no longer waiting for approval.']);
        }

        $validated = $request->validate([
            'document_root' => ['required', 'string', 'max:500'],
        ]);

        $documentRoot = rtrim(str_replace('\\', '/', trim($validated['document_root'])), '/');
        if (! str_starts_with($documentRoot, '/home/elimutai/') || str_contains($documentRoot, '..')) {
            throw ValidationException::withMessages([
                'document_root' => 'The path must be inside /home/elimutai/ and cannot contain ..',
            ]);
        }

        DB::transaction(function () use ($request, $project, $documentRoot) {
            $project->load(['domains', 'repositories']);

            $project->domains->firstOrFail()->forceFill([
                'document_root' => $documentRoot,
                'status' => 'active',
            ])->save();

            $project->repositories->firstOrFail()->forceFill([
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ])->save();

            $project->forceFill(['status' => Project::STATUS_ACTIVE])->save();
            DB::table('project_modules')
                ->where('project_id', $project->id)
                ->whereIn('service_module_id', ServiceModule::where('key', 'github-deploy')->select('id'))
                ->update(['status' => 'active', 'updated_at' => now()]);

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'project_id' => $project->id,
                'action' => 'project.approved',
                'subject_type' => Project::class,
                'subject_id' => (string) $project->id,
                'details' => ['document_root' => $documentRoot],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', "Project {$project->name} was approved.");
    }
}
