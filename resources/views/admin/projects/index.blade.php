@extends('layouts.app')

@section('title', 'Projects · Elimutaifa Labs')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Admin</p><h1>Projects</h1><p class="muted" style="margin-top:7px">Each project owns its approved repository and assigned hosting resources.</p></div>
    <a class="button" href="{{ route('admin.projects.create') }}">Register project</a>
</div>
<section class="panel">
    @if ($projects->isEmpty())
        <div class="empty-state"><strong>No projects registered</strong>Approve a developer first, then register their repository and resources.</div>
    @else
        <div class="table-wrap"><table>
            <thead><tr><th>Project</th><th>Developer</th><th>Repository</th><th>Domain</th><th>Database</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong><br><span class="muted">{{ $project->slug }}</span></td>
                    <td>{{ $project->owner->name }}</td>
                    <td>{{ $project->repositories->first()?->full_name ?? '—' }}</td>
                    <td>{{ $project->domains->first()?->hostname ?? '—' }}</td>
                    <td>{{ $project->databases->first()?->database_name ?? '—' }}</td>
                    <td><span class="badge {{ $project->status }}">{{ ucfirst($project->status) }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="pagination">{{ $projects->links() }}</div>
    @endif
</section>
@endsection
