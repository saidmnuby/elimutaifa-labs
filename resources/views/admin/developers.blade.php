@extends('layouts.app')

@section('title', 'Developers · Elimutaifa Labs')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Admin</p><h1>Developers</h1><p class="muted" style="margin-top:7px">Review accounts before assigning hosting projects.</p></div>
    <span class="badge">{{ $developers->total() }} accounts</span>
</div>
<section class="panel">
    <div class="table-wrap"><table>
        <thead><tr><th>Developer</th><th>GitHub</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($developers as $developer)
            <tr>
                <td><strong>{{ $developer->name }}</strong><br><span class="muted">{{ $developer->email }}</span></td>
                <td>{{ $developer->github_username ? '@'.$developer->github_username : '—' }}</td>
                <td><span class="badge {{ $developer->status }}">{{ ucfirst($developer->status) }}</span></td>
                <td>{{ $developer->created_at->format('d M Y') }}</td>
                <td>
                    @if ($developer->status === \App\Models\User::STATUS_PENDING)
                        <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="button small" type="submit">Approve</button></form>
                    @elseif ($developer->status === \App\Models\User::STATUS_ACTIVE)
                        <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="suspended"><button class="button small danger" type="submit">Suspend</button></form>
                    @else
                        <form method="post" action="{{ route('admin.developers.status', $developer) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="button small secondary" type="submit">Reactivate</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No developer accounts have registered yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $developers->links() }}</div>
</section>
@endsection
