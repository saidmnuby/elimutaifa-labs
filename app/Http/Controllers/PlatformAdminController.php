<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\Repository;
use App\Models\ServiceModule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlatformAdminController extends Controller
{
    public function developers(): View
    {
        return view('admin.developers', [
            'developers' => User::where('role', User::ROLE_DEVELOPER)->latest()->paginate(20),
        ]);
    }

    public function updateDeveloperStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== User::ROLE_DEVELOPER) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        $oldStatus = $user->status;
        $user->forceFill(['status' => $validated['status']])->save();

        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'action' => 'developer.status_changed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'details' => ['from' => $oldStatus, 'to' => $user->status],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Hali ya akaunti ya {$user->email} imebadilishwa.");
    }

    public function projects(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::with(['owner', 'modules', 'repositories', 'domains', 'databases'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function createProject(): View
    {
        return view('admin.projects.create', [
            'developers' => User::where('role', User::ROLE_DEVELOPER)
                ->where('status', User::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(),
            'modules' => ServiceModule::where('is_enabled', true)->orderBy('name')->get(),
        ]);
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owner_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'repository' => ['required', 'string', 'max:190', 'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', 'unique:repositories,full_name'],
            'branch' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9._\/-]+$/'],
            'hostname' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', 'unique:project_domains,hostname'],
            'document_root' => ['required', 'string', 'max:500'],
            'database_host' => ['required', 'string', 'max:253'],
            'database_name' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/', 'unique:project_databases,database_name'],
            'database_user' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'secret_reference' => ['nullable', 'string', 'max:190', 'regex:/^[A-Z][A-Z0-9_]*$/'],
            'module_ids' => ['nullable', 'array'],
            'module_ids.*' => ['integer', 'distinct', 'exists:service_modules,id'],
        ]);

        $owner = User::findOrFail($validated['owner_id']);
        if ($owner->role !== User::ROLE_DEVELOPER || $owner->status !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'owner_id' => 'Chagua developer aliyeidhinishwa na aliye active.',
            ]);
        }

        $documentRoot = rtrim(str_replace('\\', '/', $validated['document_root']), '/');
        if (! str_starts_with($documentRoot, '/home/elimutai/') || str_contains($documentRoot, '..')) {
            throw ValidationException::withMessages([
                'document_root' => 'Path lazima iwe ndani ya /home/elimutai/ na isiwe na ..',
            ]);
        }

        $repositoryName = $validated['repository'];
        $project = DB::transaction(function () use ($request, $validated, $owner, $documentRoot, $repositoryName) {
            $slugBase = Str::slug($validated['name']) ?: 'project';
            $slug = $slugBase;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.Str::lower(Str::random(5));
            }

            $project = Project::create([
                'owner_id' => $owner->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'status' => Project::STATUS_PENDING,
            ]);

            $repository = $project->repositories()->create([
                'provider' => 'github',
                'full_name' => $repositoryName,
                'branch' => $validated['branch'],
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            $project->domains()->create([
                'hostname' => Str::lower($validated['hostname']),
                'document_root' => $documentRoot,
                'status' => 'pending',
            ]);

            $project->databases()->create([
                'host' => $validated['database_host'],
                'database_name' => $validated['database_name'],
                'database_user' => $validated['database_user'],
                'secret_reference' => $validated['secret_reference'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($validated['module_ids'] ?? [] as $moduleId) {
                $project->modules()->attach($moduleId, ['status' => 'pending']);
            }

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'project_id' => $project->id,
                'action' => 'project.created',
                'subject_type' => Project::class,
                'subject_id' => (string) $project->id,
                'details' => ['repository_id' => $repository->id, 'owner_id' => $owner->id],
                'ip_address' => $request->ip(),
            ]);

            return $project;
        });

        return redirect()->route('admin.projects')->with('success', "Project {$project->name} imesajiliwa ikiwa pending.");
    }
}
