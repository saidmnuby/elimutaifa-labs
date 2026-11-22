@extends('layouts.app')

@section('title', 'Edit project resources · Elimutaifa Labs')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Admin · {{ $project->name }}</p>
        <h1>Edit project resources</h1>
        <p class="muted" style="margin-top:7px">Correct a typo or update an assigned resource. This does not delete the hosting folder or database.</p>
    </div>
    <a href="{{ route('admin.projects') }}">Back to projects</a>
</div>

<section class="panel" style="max-width:900px">
    <form method="post" action="{{ route('admin.projects.update', $project) }}">
        @csrf
        @method('PATCH')

        <div class="field">
            <label for="name">Project name</label>
            <input id="name" name="name" value="{{ old('name', $project->name) }}" maxlength="120" required>
            @error('name')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" maxlength="2000">{{ old('description', $project->description) }}</textarea>
            @error('description')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="repository">GitHub repository</label>
            <input id="repository" name="repository" value="{{ old('repository', $project->repositories->first()?->full_name) }}" placeholder="account/repository" maxlength="190" required>
            @error('repository')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="branch">Deployment branch</label>
            <input id="branch" name="branch" value="{{ old('branch', $project->repositories->first()?->branch ?? 'main') }}" maxlength="120" required>
            @error('branch')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="hostname">Domain or subdomain</label>
            <input id="hostname" name="hostname" value="{{ old('hostname', $project->domains->first()?->hostname) }}" maxlength="253" required>
            @error('hostname')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="document_root">Hosting folder</label>
            <input id="document_root" name="document_root" value="{{ old('document_root', $project->domains->first()?->document_root) }}" placeholder="/home/elimutai/domain.example" maxlength="500" @required($project->status === \App\Models\Project::STATUS_ACTIVE)>
            <small class="muted">Must be inside `/home/elimutai/`. Required for active projects.</small>
            @error('document_root')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label>Enabled services</label>
            <div class="check-grid">
                @foreach ($modules as $module)
                    <label class="check-option" for="edit_module_{{ $module->id }}">
                        <input id="edit_module_{{ $module->id }}" type="checkbox" name="module_ids[]" value="{{ $module->id }}" @checked(in_array((string) $module->id, old('module_ids', $selectedModuleIds), true))>
                        <span><strong>{{ $module->name }}</strong><small>{{ $module->description }}</small></span>
                    </label>
                @endforeach
            </div>
            @error('module_ids.*')<small class="error">{{ $message }}</small>@enderror
        </div>

        @php($database = $project->databases->first())
        <h2 style="margin:24px 0 12px">Database resource</h2>
        <p class="muted" style="margin-bottom:14px">Optional. Enter database name and user together. If a database is already listed, blank fields leave its record unchanged. This form does not create, rename, or delete the cPanel database.</p>
        <div class="field">
            <label for="database_host">Database host</label>
            <input id="database_host" name="database_host" value="{{ old('database_host', $database?->host) }}" maxlength="253">
            @error('database_host')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="database_name">Database name</label>
            <input id="database_name" name="database_name" value="{{ old('database_name', $database?->database_name) }}" maxlength="128">
            @error('database_name')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="database_user">Database user</label>
            <input id="database_user" name="database_user" value="{{ old('database_user', $database?->database_user) }}" maxlength="128">
            @error('database_user')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="database_status">Database access status</label>
            <select id="database_status" name="database_status">
                <option value="pending" @selected(old('database_status', $database?->status ?? 'pending') === 'pending')>Pending setup</option>
                <option value="active" @selected(old('database_status', $database?->status ?? 'pending') === 'active')>Active</option>
            </select>
            @error('database_status')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="database_password">Database password</label>
            <input id="database_password" name="database_password" type="password" autocomplete="new-password" maxlength="2000">
            <small class="muted">Leave blank to keep the saved password. If entered, the portal encrypts it and never displays it again.</small>
            @error('database_password')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="secret_reference">Secret reference <span class="muted">(not the password)</span></label>
            <input id="secret_reference" name="secret_reference" value="{{ old('secret_reference', $database?->secret_reference) }}" maxlength="190" placeholder="DATABASE_PASSWORD_PROJECT_A">
            @error('secret_reference')<small class="error">{{ $message }}</small>@enderror
        </div>

        <button class="button" type="submit">Save resource changes</button>
    </form>
</section>
@endsection
