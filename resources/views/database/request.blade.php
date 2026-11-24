@extends('layouts.app')

@section('title', 'Request database · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Database module</p>
        <h1>Request a database</h1>
        <p class="muted" style="margin-top:7px">This request is separate from GitHub deployment. Admin will create and assign the database.</p>
    </div>
    <a href="{{ route('modules.show', 'database') }}">Back to Database</a>
</div>

<section class="panel" style="max-width:760px">
    @if ($projects->isEmpty())
        <div class="notice warning">Hakuna project inayoweza kuombewa database kwa sasa. Kamilisha ombi la GitHub deployment kwanza, au subiri ombi la database lililopo likaguliwe.</div>
        <a class="button" href="{{ route('modules.show', 'github-deploy') }}">Go to GitHub deployment</a>
    @else
    <form method="post" action="{{ route('database.request.store') }}">
        @csrf
        <div class="field">
            <label for="project_id">Select project</label>
            <select id="project_id" name="project_id" required>
                <option value="">Choose your project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) old('project_id', $selectedProjectId) === (string) $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            @error('project_id')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="field">
            <label for="database_name_suffix">Preferred database name</label>
            <div style="display:flex;align-items:center;gap:0">
                <span style="padding:10px 12px;border:1px solid #cfd4dc;border-right:0;border-radius:8px 0 0 8px;background:#f2f4f7;color:#475467">{{ $databasePrefix }}</span>
                <input id="database_name_suffix" name="database_name_suffix" value="{{ old('database_name_suffix') }}" maxlength="55" pattern="[A-Za-z][A-Za-z0-9_]*" placeholder="words_game" required style="border-radius:0 8px 8px 0">
            </div>
            <small class="muted">Enter only the part after the prefix. Example: <code>words_game</code> becomes <code>{{ $databasePrefix }}words_game</code>.</small>
            @error('database_name_suffix')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="notice warning" style="margin:18px 0">This submits a database request only. It does not change GitHub deployment settings or create the database by itself.</div>
        <button class="button" type="submit">Send database request</button>
    </form>
    @endif
</section>
@endsection
