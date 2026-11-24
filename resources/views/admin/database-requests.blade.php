@extends('layouts.app')

@section('title', 'Database requests · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Admin</p>
        <h1>Database requests</h1>
        <p class="muted" style="margin-top:7px">Review each request, create the database and restricted user in cPanel, then register and activate access here.</p>
    </div>
    <span class="badge">{{ $requests->total() }} pending</span>
</div>

<section class="panel">
    @if ($requests->isEmpty())
        <div class="empty-state"><strong>No database requests waiting</strong>New requests from developers will appear here.</div>
    @else
        <div class="project-list">
            @foreach ($requests as $databaseRequest)
                @php($requestedFullName = $prefix.$databaseRequest->requested_name_suffix)
                <article class="project-card">
                    <div class="project-title">
                        <div><h2>{{ $databaseRequest->project->name }}</h2><p class="muted">Developer: {{ $databaseRequest->project->owner->name }} · {{ $databaseRequest->project->owner->email }}</p></div>
                        <span class="badge pending">Pending</span>
                    </div>
                    <p class="muted" style="margin:8px 0 16px">Requested database name: <strong>{{ $requestedFullName }}</strong></p>
                    <details>
                        <summary class="button small">Review and approve</summary>
                        <div style="padding-top:16px">
                            <div class="notice warning" style="margin-bottom:16px">First create this database and its user in cPanel. Give the user privileges only on this project's database. Then enter the exact connection details below.</div>
                            <form method="post" action="{{ route('admin.database-requests.approve', $databaseRequest) }}">
                                @csrf
                                <div class="field">
                                    <label for="database_name_{{ $databaseRequest->id }}">Actual cPanel database name</label>
                                    <input id="database_name_{{ $databaseRequest->id }}" name="database_name" value="{{ old('database_name', $requestedFullName) }}" maxlength="128" pattern="[A-Za-z0-9_]+" required>
                                    @error('database_name')<small class="error">{{ $message }}</small>@enderror
                                </div>
                                <div class="field">
                                    <label for="database_user_{{ $databaseRequest->id }}">cPanel database user</label>
                                    <input id="database_user_{{ $databaseRequest->id }}" name="database_user" value="{{ old('database_user', $prefix.$databaseRequest->requested_name_suffix.'_user') }}" maxlength="128" pattern="[A-Za-z0-9_]+" required>
                                    @error('database_user')<small class="error">{{ $message }}</small>@enderror
                                </div>
                                <div class="field">
                                    <label for="database_host_{{ $databaseRequest->id }}">Database host</label>
                                    <input id="database_host_{{ $databaseRequest->id }}" name="database_host" value="{{ old('database_host', 'localhost') }}" maxlength="253" required>
                                    @error('database_host')<small class="error">{{ $message }}</small>@enderror
                                </div>
                                <div class="field">
                                    <label for="database_password_{{ $databaseRequest->id }}">Database user password</label>
                                    <input id="database_password_{{ $databaseRequest->id }}" name="database_password" type="password" autocomplete="new-password" maxlength="2000" required>
                                    <small class="muted">Saved encrypted. It will not be shown again.</small>
                                    @error('database_password')<small class="error">{{ $message }}</small>@enderror
                                </div>
                                <label class="check-option" style="margin:12px 0 18px">
                                    <input type="checkbox" name="created_and_assigned" value="1" required>
                                    <span><strong>Database is ready in cPanel</strong><small>I created this database and assigned the user to this database only.</small></span>
                                </label>
                                @error('created_and_assigned')<small class="error">{{ $message }}</small>@enderror
                                <button class="button" type="submit">Approve &amp; activate database</button>
                            </form>
                            <form method="post" action="{{ route('admin.database-requests.reject', $databaseRequest) }}" style="margin-top:10px" onsubmit="return confirm('Reject this database request?')">
                                @csrf
                                @method('PATCH')
                                <button class="button small danger" type="submit">Reject request</button>
                            </form>
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $requests->links() }}</div>
    @endif
</section>
@endsection
