@extends('layouts.app')

@section('title', 'Projects · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Admin</p><h1>Projects</h1><p class="muted" style="margin-top:7px">Review requests, approve or reject deployments, and manage assigned resources.</p></div>
</div>
<section class="cards developer-metrics">
    <article class="card metric"><div class="metric-label">Total projects</div><div class="metric-value">{{ $metrics['total'] }}</div><div class="metric-note">All registered projects</div></article>
    <article class="card metric"><div class="metric-label">Approved</div><div class="metric-value">{{ $metrics['approved'] }}</div><div class="metric-note">Active projects</div></article>
    <article class="card metric"><div class="metric-label">Awaiting review</div><div class="metric-value">{{ $metrics['pending'] }}</div><div class="metric-note">Need admin action</div></article>
    <article class="card metric"><div class="metric-label">Rejected</div><div class="metric-value">{{ $metrics['rejected'] }}</div><div class="metric-note">Deployment requests declined</div></article>
    <article class="card metric"><div class="metric-label">Suspended</div><div class="metric-value">{{ $metrics['suspended'] }}</div><div class="metric-note">Project access disabled</div></article>
</section>
<section class="panel">
    @if ($projects->isEmpty())
        <div class="empty-state"><strong>No project requests yet</strong>Approved developers can submit a project from their workspace.</div>
    @else
        <div class="table-wrap"><table>
            <thead><tr><th>Project</th><th>Developer</th><th>Repository</th><th>Domain</th><th>Database name request</th><th>Requested services</th><th>Status / action</th></tr></thead>
            <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong><br><span class="muted">{{ $project->slug }}</span></td>
                    <td>{{ $project->owner->name }}</td>
                    <td>{{ $project->repositories->first()?->full_name ?? '—' }}</td>
                    <td>{{ $project->domains->first()?->hostname ?? '—' }}</td>
                    <td>
                        @php($pendingDatabaseNames = $project->databaseRequests->where('status', 'pending')->pluck('requested_name_suffix')->map(fn ($suffix) => $databasePrefix.$suffix))
                        @if ($pendingDatabaseNames->isNotEmpty()){{ $pendingDatabaseNames->join(', ') }}@else—@endif
                        @if ($project->databases->isNotEmpty())<br><span class="muted">Assigned: {{ $project->databases->pluck('database_name')->join(', ') }}</span>@endif
                    </td>
                    <td>{{ $project->modules->pluck('name')->join(', ') ?: '—' }}</td>
                    <td>
                        <span class="badge {{ $project->status }}">{{ ucfirst($project->status) }}</span>
                        <p style="margin-top:8px"><a class="button small secondary" href="{{ route('admin.projects.edit', $project) }}">Edit resources</a></p>
                        @if ($project->status === \App\Models\Project::STATUS_PENDING)
                            <form method="post" action="{{ route('admin.projects.approve', $project) }}" style="margin-top:10px;min-width:230px">
                                @csrf
                                @method('PATCH')
                                <div class="field" style="margin-bottom:8px">
                                    <label for="document_root_{{ $project->id }}">Assign hosting folder</label>
                                    <input id="document_root_{{ $project->id }}" name="document_root" placeholder="/home/elimutai/domain.example" required>
                                    @error('document_root')<small class="error">{{ $message }}</small>@enderror
                                </div>
                                <button class="button small" type="submit">Approve project</button>
                            </form>
                            <form method="post" action="{{ route('admin.projects.reject', $project) }}" style="margin-top:6px">
                                @csrf
                                @method('PATCH')
                                <button class="button small danger" type="submit">Reject request</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="pagination">{{ $projects->links() }}</div>
    @endif
</section>
@endsection
