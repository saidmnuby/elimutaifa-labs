<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\ServiceModule;
use App\Models\User;
use App\Support\GitHubAppClient;
use App\Support\SystemMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectSubmissionController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless(ServiceModule::where('key', 'github-deploy')->where('is_enabled', true)->exists(), 404);

        $repositories = $request->session()->get('github_app_repositories', []);

        return view('projects.create', [
            'githubInstallationRepositories' => is_array($repositories) ? $repositories : [],
            'githubInstallationAccount' => $request->session()->get('github_app_account_login'),
            'githubAppConnected' => (int) $request->session()->get('github_app_installation_id', 0) > 0
                && is_array($repositories)
                && $repositories !== [],
            'projectCount' => $request->user()->projects()
                ->whereIn('status', [Project::STATUS_PENDING, Project::STATUS_ACTIVE, Project::STATUS_SUSPENDED])
                ->count(),
            'projectLimit' => $request->user()->projectLimit(),
        ]);
    }

    public function store(Request $request, GitHubAppClient $github, SystemMailer $mailer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'repository' => [
                'required', 'string', 'max:190',
                'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/',
                Rule::unique('repositories', 'full_name'),
            ],
            'branch' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9._\/-]+$/'],
            'hostname' => [
                'required', 'string', 'max:253',
                'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/',
                Rule::unique('project_domains', 'hostname'),
            ],
            'github_username' => ['nullable', 'string', 'max:39', 'regex:/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/'],
        ]);

        $githubModule = ServiceModule::where('key', 'github-deploy')->where('is_enabled', true)->firstOrFail();

        $developer = $request->user();
        abort_unless(
            $developer->role === User::ROLE_DEVELOPER && $developer->status === User::STATUS_ACTIVE,
            403
        );

        if (! empty($validated['github_username'])
            && (! $developer->github_verified_at
                || mb_strtolower($validated['github_username']) !== mb_strtolower((string) $developer->github_username))) {
            throw ValidationException::withMessages([
                'github_username' => 'Connect and verify this GitHub account before submitting it, or leave this field blank.',
            ]);
        }

        $installationId = (int) $request->session()->get('github_app_installation_id', 0);
        if ($installationId < 1 || ! $github->isConfigured()) {
            throw ValidationException::withMessages([
                'repository' => 'Connect the GitHub App and choose a repository before submitting your request.',
            ]);
        }

        try {
            $accessibleRepositories = $github->repositories($installationId);
        } catch (\Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'repository' => 'We could not verify access to this GitHub repository. Please try again later.',
            ]);
        }

        $verifiedRepository = collect($accessibleRepositories)->first(fn (array $repo) =>
            strcasecmp($repo['full_name'], $validated['repository']) === 0
        );

        if (! $verifiedRepository) {
            throw ValidationException::withMessages([
                'repository' => 'The GitHub App cannot access this repository. Choose a repository you allowed when connecting the App.',
            ]);
        }

        $project = DB::transaction(function () use ($request, $validated, $developer, $githubModule, $installationId, $verifiedRepository) {
            $lockedDeveloper = User::whereKey($developer->id)->lockForUpdate()->firstOrFail();
            $projectLimit = $lockedDeveloper->projectLimit();
            $projectCount = $lockedDeveloper->projects()
                ->whereIn('status', [Project::STATUS_PENDING, Project::STATUS_ACTIVE, Project::STATUS_SUSPENDED])
                ->count();
            if ($projectLimit !== null && $projectCount >= $projectLimit) {
                throw ValidationException::withMessages([
                    'repository' => "Your {$lockedDeveloper->planLimits()['label']} plan allows up to {$projectLimit} projects. Upgrade your plan to register more.",
                ]);
            }

            $slugBase = Str::slug($validated['name']) ?: 'project';
            $slug = $slugBase;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.Str::lower(Str::random(5));
            }

            $project = Project::create([
                'owner_id' => $developer->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'status' => Project::STATUS_PENDING,
            ]);

            $repository = $project->repositories()->create([
                'provider' => 'github',
                'full_name' => $verifiedRepository['full_name'],
                'github_installation_id' => $installationId,
                'branch' => $validated['branch'],
            ]);

            $project->domains()->create([
                'hostname' => Str::lower($validated['hostname']),
                'document_root' => null,
                'status' => 'pending',
            ]);

            $project->modules()->attach($githubModule->id, ['status' => 'pending']);

            AuditEvent::create([
                'actor_id' => $developer->id,
                'project_id' => $project->id,
                'action' => 'project.submitted',
                'subject_type' => Project::class,
                'subject_id' => (string) $project->id,
                'details' => ['repository_id' => $repository->id],
                'ip_address' => $request->ip(),
            ]);

            return $project;
        });

        $mailer->admins('New project request · TechwareTz Control Center (TCC)', [
            'eyebrow' => 'Project request',
            'title' => 'A project needs review',
            'intro' => 'A developer submitted a project for hosting approval.',
            'details' => [
                'Project' => $project->name,
                'Developer' => $developer->name,
                'Repository' => $verifiedRepository['full_name'],
                'Domain' => Str::lower($validated['hostname']),
            ],
            'button_label' => 'Review projects',
            'button_url' => route('admin.projects'),
        ]);

        return redirect()->route('dashboard')->with('success', "Your request for {$project->name} was sent for review.");
    }
}
