@extends('layouts.app')

@section('title', 'Workspace · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Workspace</p>
        <h1>{{ auth()->user()->isAdmin() ? 'Platform overview' : 'Your projects' }}</h1>
        <p class="muted" style="margin-top:7px">Hosting services are attached to projects and can grow through approved modules.</p>
    </div>
    <span class="badge {{ auth()->user()->status }}">{{ ucfirst(auth()->user()->status) }} account</span>
</div>

@if (auth()->user()->status === \App\Models\User::STATUS_PENDING)
    <div class="notice warning"><strong>Account awaiting review.</strong> Your account is registered. Hosting access will be enabled after admin approval.</div>
@elseif (auth()->user()->status === \App\Models\User::STATUS_SUSPENDED)
    <div class="notice warning"><strong>Account suspended.</strong> Contact the platform administrator for help.</div>
@endif

@if (auth()->user()->isAdmin())
    <section class="cards">
        <article class="card metric"><div class="metric-label">Registered projects</div><div class="metric-value">{{ $totalProjects }}</div><div class="metric-note">Across all developers</div></article>
        <article class="card metric"><div class="metric-label">Developers awaiting review</div><div class="metric-value">{{ $pendingDevelopers }}</div><div class="metric-note">Accounts needing activation</div></article>
        <article class="card metric"><div class="metric-label">Available service modules</div><div class="metric-value">{{ $moduleCount }}</div><div class="metric-note">Modules admins can assign</div></article>
    </section>
    <section class="panel">
        <div class="section-head"><h2>Platform foundation</h2><span class="badge active">Ready for project registry</span></div>
        <p class="muted">Developer accounts, projects, approved repositories, domains, database records, modules, deployments, and audit events have dedicated records. New modules are added by the platform owner, then assigned to projects.</p>
    </section>
@else
    <section class="panel">
        <div class="section-head">
            <div><h2>Your projects</h2><span class="muted">{{ $projectCount }} total</span></div>
            @if (auth()->user()->status === \App\Models\User::STATUS_ACTIVE)
                <a class="button" href="{{ route('projects.create') }}">Create project request</a>
            @endif
        </div>
        @if ($projects->isEmpty())
            <div class="empty-state"><strong>No projects yet</strong>Start by submitting a project request. Admin will review it and assign its hosting folder.</div>
        @else
            <div class="project-list">
                @foreach ($projects as $project)
                    <article class="project-card">
                        <div class="project-title"><h2>{{ $project->name }}</h2><span class="badge {{ $project->status }}">{{ ucfirst($project->status) }}</span></div>
                        @if ($project->description)<p class="muted">{{ $project->description }}</p>@endif
                        <div class="project-meta">
                            @foreach ($project->repositories as $repository)<span>GitHub: {{ $repository->full_name }} ({{ $repository->branch }})</span>@endforeach
                            @foreach ($project->domains as $domain)<span>Domain: {{ $domain->hostname }}</span>@endforeach
                            @foreach ($project->modules as $module)<span>Service: {{ $module->name }}</span>@endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endif
@endsection
