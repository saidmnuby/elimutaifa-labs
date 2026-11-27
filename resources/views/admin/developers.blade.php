@extends('layouts.app')

@section('title', 'Developers · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Admin</p><h1>Developers</h1><p class="muted" style="margin-top:7px">Review accounts before assigning hosting projects.</p></div>
    <span class="badge">{{ $developers->total() }} accounts</span>
</div>
<section class="cards">
    <article class="card metric"><div class="metric-label">Total developers</div><div class="metric-value">{{ $metrics['total'] }}</div><div class="metric-note">Registered accounts</div></article>
    <article class="card metric"><div class="metric-label">Approved</div><div class="metric-value">{{ $metrics['approved'] }}</div><div class="metric-note">Can request services</div></article>
    <article class="card metric"><div class="metric-label">Awaiting review</div><div class="metric-value">{{ $metrics['pending'] }}</div><div class="metric-note">Need admin action</div></article>
    <article class="card metric"><div class="metric-label">Rejected</div><div class="metric-value">{{ $metrics['rejected'] }}</div><div class="metric-note">Registration not approved</div></article>
    <article class="card metric"><div class="metric-label">Suspended</div><div class="metric-value">{{ $metrics['suspended'] }}</div><div class="metric-note">Access temporarily disabled</div></article>
    <article class="card metric"><div class="metric-label">Free plan</div><div class="metric-value">{{ $metrics['free'] }}</div><div class="metric-note">5 projects · 5 databases each</div></article>
    <article class="card metric"><div class="metric-label">Plus plan</div><div class="metric-value">{{ $metrics['plus'] }}</div><div class="metric-note">No project or database count cap</div></article>
</section>
<section class="panel">
    <div class="table-wrap"><table>
        <thead><tr><th>Developer</th><th>GitHub</th><th>Plan</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($developers as $developer)
            <tr>
                <td><strong>{{ $developer->name }}</strong><br><span class="muted">{{ $developer->email }}</span></td>
                <td>
                    @if ($developer->github_username)
                        {{ '@'.$developer->github_username }}
                        @if ($developer->github_verified_at)<br><span class="badge active">Ownership verified</span>@else<br><span class="badge pending">Not verified</span>@endif
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="badge {{ $developer->isPlus() ? 'active' : 'pending' }}">{{ ucfirst($developer->effectivePlan()) }}</span>
                </td>
                <td><span class="badge {{ $developer->status }}">{{ ucfirst($developer->status) }}</span></td>
                <td>{{ $developer->created_at->format('d M Y') }}</td>
                <td>
                    @if ($developer->status === \App\Models\User::STATUS_PENDING)
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="button small" type="submit">Approve</button></form>
                            <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="button small danger" type="submit">Reject</button></form>
                        </div>
                    @elseif ($developer->status === \App\Models\User::STATUS_ACTIVE)
                        <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="suspended"><button class="button small danger" type="submit">Suspend</button></form>
                    @else
                        <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="button small secondary" type="submit">Reactivate</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No developer accounts have registered yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $developers->links() }}</div>
</section>
@endsection
