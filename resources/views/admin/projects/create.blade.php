@extends('layouts.app')

@section('title', 'Register project · Elimutaifa Labs')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Admin · Projects</p><h1>Register a project</h1><p class="muted" style="margin-top:7px">Only approved repository and hosting details are stored here. Do not enter database passwords.</p></div>
    <a href="{{ route('admin.projects') }}">Back to projects</a>
</div>
<section class="panel" style="max-width:820px">
    @if ($developers->isEmpty())
        <div class="notice warning">Approve a developer account before registering a project.</div>
        <a class="button" href="{{ route('admin.developers') }}">Review developers</a>
    @else
    <form method="post" action="{{ route('admin.projects.store') }}">
        @csrf
        <div class="field"><label for="owner_id">Developer</label><select id="owner_id" name="owner_id" required><option value="">Select an approved developer</option>@foreach ($developers as $developer)<option value="{{ $developer->id }}" @selected(old('owner_id') == $developer->id)>{{ $developer->name }} — {{ $developer->email }}</option>@endforeach</select>@error('owner_id')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="name">Project name</label><input id="name" name="name" value="{{ old('name') }}" maxlength="120" required>@error('name')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="description">Description <span class="muted">(optional)</span></label><textarea id="description" name="description" maxlength="2000">{{ old('description') }}</textarea>@error('description')<div class="form-error">{{ $message }}</div>@enderror</div>

        <h2 style="margin:26px 0 14px">Approved source</h2>
        <div class="field"><label for="repository">GitHub repository</label><input id="repository" name="repository" value="{{ old('repository') }}" placeholder="owner/repository" required>@error('repository')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="branch">Deployment branch</label><input id="branch" name="branch" value="{{ old('branch', 'main') }}" required>@error('branch')<div class="form-error">{{ $message }}</div>@enderror</div>

        <h2 style="margin:26px 0 14px">Hosting destination</h2>
        <div class="field"><label for="hostname">Domain or subdomain</label><input id="hostname" name="hostname" value="{{ old('hostname') }}" placeholder="project.elimutaifa.co.tz" required>@error('hostname')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="document_root">Hosting folder</label><input id="document_root" name="document_root" value="{{ old('document_root') }}" placeholder="/home/elimutai/project.elimutaifa.co.tz" required>@error('document_root')<div class="form-error">{{ $message }}</div>@enderror</div>

        <h2 style="margin:26px 0 14px">Project database</h2>
        <div class="field"><label for="database_host">Database host</label><input id="database_host" name="database_host" value="{{ old('database_host', 'localhost') }}" required>@error('database_host')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="database_name">Database name</label><input id="database_name" name="database_name" value="{{ old('database_name') }}" required>@error('database_name')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="database_user">Database username</label><input id="database_user" name="database_user" value="{{ old('database_user') }}" required>@error('database_user')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="secret_reference">Password secret name <span class="muted">(reference only)</span></label><input id="secret_reference" name="secret_reference" value="{{ old('secret_reference') }}" placeholder="DB_WORDS_GAME_PASSWORD">@error('secret_reference')<div class="form-error">{{ $message }}</div>@enderror<div class="muted" style="font-size:12px;margin-top:5px">Enter a secret label only, never the password itself.</div></div>

        <h2 style="margin:26px 0 14px">Services to enable</h2>
        <div class="check-grid">
            @foreach ($modules as $module)
                <label class="check-option"><input type="checkbox" name="module_ids[]" value="{{ $module->id }}" @checked(in_array($module->id, old('module_ids', [])))><span><strong>{{ $module->name }}</strong><small>{{ $module->description }}</small></span></label>
            @endforeach
        </div>
        @error('module_ids')<div class="form-error">{{ $message }}</div>@enderror
        <button class="button" type="submit" style="margin-top:24px">Save project as pending</button>
    </form>
    @endif
</section>
@endsection
