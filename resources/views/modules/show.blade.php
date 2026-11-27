@extends('layouts.app')

@section('title', $module->name.' · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Developer module</p>
        <h1>{{ $module->name }}</h1>
        <p class="muted" style="margin-top:7px">{{ $module->description }}</p>
    </div>
    @if ($module->key === 'github-deploy' && auth()->user()->status === \App\Models\User::STATUS_ACTIVE)
        <a class="button" href="{{ route('projects.create') }}">Register repository</a>
    @elseif ($module->key === 'database' && auth()->user()->status === \App\Models\User::STATUS_ACTIVE)
        <a class="button" href="{{ route('database.request.create') }}">Request a database</a>
    @endif
</div>

@if (! auth()->user()->isAdmin())
    @php($databaseLimit = auth()->user()->databaseLimitPerProject())
    <div class="notice" style="margin-bottom:18px">
        <strong>{{ ucfirst(auth()->user()->plan) }} plan</strong>
        @if ($module->key === 'github-deploy')
            · Projects: {{ $projectCount }}{{ $projectLimit === null ? ' / unlimited' : ' / '.$projectLimit }}
        @else
            · Up to {{ $databaseLimit === null ? 'unlimited' : $databaseLimit }} databases per project
        @endif
        @if ($module->key === 'github-deploy' && $projectLimit !== null && $projectCount >= $projectLimit)
            <span> You have reached your project limit.</span>
        @endif
    </div>
@endif

<section class="panel">
    @if (auth()->user()->status !== \App\Models\User::STATUS_ACTIVE)
        <div class="notice warning">Your developer account must be approved before you can use project services.</div>
    @elseif ($projects->isEmpty())
        <div class="empty-state">
            <strong>{{ $module->key === 'database' ? 'No projects available yet' : 'No deployment requests yet' }}</strong>
            {{ $module->key === 'database' ? 'Create a project from GitHub deployment first, then request its database here.' : 'Register a repository to send the first deployment request to admin.' }}
        </div>
    @elseif ($module->key === 'github-deploy')
        <div class="project-list">
        @foreach ($projects as $project)
                <article class="project-card">
                    <div class="project-title"><h2>{{ $project->name }}</h2><span class="badge {{ $project->status }}">{{ ucfirst($project->status) }}</span></div>
                    @if ($project->description)<p class="muted">{{ $project->description }}</p>@endif
                    @foreach ($project->repositories as $repository)
                        <div class="project-meta"><span>Repository: {{ $repository->full_name }}</span><span>Branch: {{ $repository->branch }}</span></div>
                    @endforeach
                    @foreach ($project->domains as $domain)
                        <div class="project-meta"><span>Requested domain: {{ $domain->hostname }}</span></div>
                    @endforeach
                    @if ($project->status === \App\Models\Project::STATUS_ACTIVE)
                        <p class="muted" style="margin-top:10px">Deployment history and live run controls will appear here in a later release.</p>
                    @elseif ($project->status === \App\Models\Project::STATUS_REJECTED)
                        <p class="muted" style="margin-top:10px">This deployment request was rejected by admin. Contact admin to correct and resubmit it.</p>
                    @else
                        <p class="muted" style="margin-top:10px">Waiting for admin to review the deployment request.</p>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        <div class="project-list">
            @foreach ($projects as $project)
                @php($databaseModule = $project->modules->firstWhere('key', 'database'))
                @php($databaseUsage = $project->databases->whereIn('status', ['pending', 'active'])->count() + $project->databaseRequests->where('status', 'pending')->count())
                <article class="project-card">
                    <div class="project-title"><h2>{{ $project->name }}</h2><span class="badge {{ $project->status }}">{{ ucfirst($project->status) }} project</span></div>
                    @if ($project->databaseRequests->isNotEmpty())
                        <div class="project-list" style="margin:12px 0">
                            @foreach ($project->databaseRequests as $databaseRequest)
                                <div class="project-card" style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
                                    <span>{{ config('platform.cpanel_database_prefix') }}{{ $databaseRequest->requested_name_suffix }}</span>
                                    <span class="badge {{ $databaseRequest->status === 'approved' ? 'active' : ($databaseRequest->status === 'rejected' ? 'rejected' : 'pending') }}">{{ ucfirst($databaseRequest->status) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($project->databases->isNotEmpty())
                        <h3 style="margin:14px 0 8px">Your databases</h3>
                        <div class="project-list">
                            @foreach ($project->databases as $database)
                                <div class="project-card" style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
                                    <div><strong>{{ $database->database_name }}</strong><br><span class="muted">{{ $database->database_user }} · {{ $database->host }}</span></div>
                                    @if ($database->status === 'active' && $databaseModule?->pivot?->status === 'active')
                                        <a class="button small" href="{{ route('database.show', $database) }}">Manage database</a>
                                    @else
                                        <span class="badge pending">Setup pending</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <p class="muted" style="margin:14px 0 10px">Databases: {{ $databaseUsage }}{{ $databaseLimit === null ? ' / unlimited' : ' / '.$databaseLimit }} for this project.</p>
                    @if ($databaseLimit === null || $databaseUsage < $databaseLimit)
                        <a class="button small secondary" href="{{ route('database.request.create', ['project_id' => $project->id]) }}">Request another database</a>
                    @else
                        <span class="badge pending">Database limit reached</span>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
