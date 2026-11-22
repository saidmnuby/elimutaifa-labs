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
            <small class="muted">Enter <code>owner/repository</code>. Public repos are checked live. A private repo or a typo may look the same here, so admin will review access. Repos owned by an organization are allowed.</small>
            <small id="github-repository-live-status" class="live-check" role="status" aria-live="polite" hidden></small>
            @error('repository')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="github_username">GitHub account <span class="muted">(optional)</span></label>
            <input id="github_username" name="github_username" value="{{ auth()->user()->github_verified_at ? auth()->user()->github_username : old('github_username', auth()->user()->github_username) }}" autocomplete="username" maxlength="39" placeholder="your-github-name" @if (auth()->user()->github_verified_at) readonly @endif>
            @if (auth()->user()->github_verified_at)
                <small class="badge active" style="margin-top:7px">Ownership verified with GitHub</small>
            @else
                <small class="muted">Optional. If you enter a username, verify that you own it with GitHub. This does not grant repository access.</small>
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
