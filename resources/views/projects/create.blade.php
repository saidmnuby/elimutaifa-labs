@extends('layouts.app')

@section('title', 'Request a project · Elimutaifa Labs')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Developer · Project request</p>
        <h1>Submit a project</h1>
        <p class="muted" style="margin-top:7px">Admin will review your repository and domain, then assign the hosting folder.</p>
    </div>
    <a href="{{ route('dashboard') }}">Back to workspace</a>
</div>

<section class="panel" style="max-width:820px">
    <form method="post" action="{{ route('projects.store') }}">
        @csrf
        <div class="field">
            <label for="name">Project name</label>
            <input id="name" name="name" value="{{ old('name') }}" maxlength="120" required>
            @error('name')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="description">Short description <span class="muted">(optional)</span></label>
            <textarea id="description" name="description" maxlength="2000">{{ old('description') }}</textarea>
            @error('description')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="repository">GitHub repository</label>
            <input id="repository" name="repository" value="{{ old('repository') }}" placeholder="your-account/project-repo" maxlength="190" required data-github-live-check="repository" data-github-check-url="{{ route('github.verify.repository') }}" data-status-target="github-repository-live-status">
            @if (session('github_app_installation_id'))
                <small class="badge active" style="margin-top:7px">GitHub App connected to {{ '@'.session('github_app_account_login') }}</small>
                <a class="button secondary small" style="margin-top:8px" href="{{ route('github.app.install') }}">Change selected GitHub repositories</a>
            @elseif (auth()->user()->github_verified_at)
                <small class="muted">Enter <code>owner/repository</code>. Connect the GitHub App and select the repository to verify access, including private repositories.</small>
                <a class="button secondary small" style="margin-top:8px" href="{{ route('github.app.install') }}">Connect GitHub App and select repository</a>
            @else
                <small class="muted">First verify your GitHub account below. Then connect the GitHub App and select the repository.</small>
            @endif
            <small id="github-repository-live-status" class="live-check" role="status" aria-live="polite" hidden></small>
            @error('repository')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="github_username">GitHub account <span class="muted">(optional)</span></label>
            <input id="github_username" name="github_username" value="{{ auth()->user()->github_verified_at ? auth()->user()->github_username : old('github_username', auth()->user()->github_username) }}" autocomplete="username" maxlength="39" placeholder="your-github-name" @if (auth()->user()->github_verified_at) readonly @endif>
            @if (auth()->user()->github_verified_at)
                <small class="badge active" style="margin-top:7px">Ownership verified with GitHub</small>
            @else
                <small class="muted">Connect your GitHub account to confirm your identity. Repository access is checked separately using the GitHub App.</small>
                <a class="button secondary small" style="margin-top:8px" href="{{ route('github.oauth.project') }}">Verify ownership with GitHub</a>
            @endif
            @error('github_username')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="branch">Deployment branch</label>
            <input id="branch" name="branch" value="{{ old('branch', 'main') }}" maxlength="120" required>
            @error('branch')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="hostname">Requested domain or subdomain</label>
            <input id="hostname" name="hostname" value="{{ old('hostname') }}" placeholder="project.elimutaifa.co.tz" maxlength="253" required>
            @error('hostname')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="notice warning" style="margin:18px 0">This sends a GitHub deployment request for review. Database requests are managed separately in the Database module. It does not deploy files or choose a hosting folder.</div>
        <button class="button" type="submit">Send project for approval</button>
    </form>
</section>
@include('components.github-live-checks')
@endsection
