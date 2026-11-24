@extends('layouts.app')

@section('title', 'Activity log · Elimutaifa Labs')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">{{ $isAdmin ? 'Platform oversight' : 'Account history' }}</p><h1>Activity log</h1><p class="muted" style="margin-top:7px">{{ $isAdmin ? 'Daily activity from developers and their projects.' : 'A record of activity on your account and projects.' }}</p></div>
    <span class="badge active">{{ $todayCount }} today</span>
</div>

<section class="panel" style="margin-bottom:18px">
    <form method="get" action="{{ route('activities.index') }}" style="display:flex;flex-wrap:wrap;align-items:end;gap:12px">
        <div class="field" style="margin:0;min-width:190px;flex:0 1 220px"><label for="date">Activity date <span class="muted">(blank for all dates)</span></label><input id="date" name="date" type="date" value="{{ $selectedDate }}"></div>
        @if ($isAdmin)
            <div class="field" style="margin:0;min-width:220px;flex:0 1 300px"><label for="developer_id">Developer</label><select id="developer_id" name="developer_id"><option value="">All developers</option>@foreach ($developers as $developer)<option value="{{ $developer->id }}" @selected((string) $selectedDeveloper === (string) $developer->id)>{{ $developer->name }} · {{ $developer->email }}</option>@endforeach</select></div>
        @endif
        <button class="button small" type="submit">Filter activity</button>
        <a class="button small secondary" href="{{ route('activities.index', ['date' => '']) }}">All dates</a>
    </form>
</section>

<section class="panel">
    @if ($activities->isEmpty())
        <div class="empty-state"><strong>No activity for this date</strong>Activity will appear here when an account or project action is recorded.</div>
    @else
        <div class="table-wrap"><table>
            <thead><tr><th>Time</th>@if ($isAdmin)<th>Developer</th>@endif<th>Activity</th><th>Project</th><th>Details</th><th>Source</th></tr></thead>
            <tbody>
                @foreach ($activities as $activity)
                    <tr>
                        <td><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->timezone('Africa/Dar_es_Salaam')->format('H:i:s') }}</time></td>
                        @if ($isAdmin)<td>{{ $activity->developer_name }}</td>@endif
                        <td><strong>{{ $activity->activity_label }}</strong></td>
                        <td>{{ $activity->project_name ?? '—' }}</td>
                        <td class="muted">{{ $activity->activity_summary ?? '—' }}</td>
                        <td class="muted">{{ $activity->actor_id ? ($activity->ip_address ?? '—') : 'Automated' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        {{ $activities->links() }}
    @endif
</section>
@endsection
