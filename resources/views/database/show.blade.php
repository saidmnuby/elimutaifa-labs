@extends('layouts.app')

@section('title', $databaseRecord->database_name.' · Database manager')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Database module · {{ $databaseRecord->project->name }}</p>
        <h1>{{ $databaseRecord->database_name }}</h1>
        <p class="muted" style="margin-top:7px">Database user: {{ $databaseRecord->database_user }} · Host: {{ $databaseRecord->host }}</p>
    </div>
    <a href="{{ route('modules.show', 'database') }}">Back to Database</a>
</div>

@if ($connectionError)
    <div class="notice warning">{{ $connectionError }}</div>
@else
    <section class="panel" style="margin-bottom:20px">
        <div class="section-head"><div><h2>Database tables</h2><span class="muted">{{ count($tables) }} tables</span></div><a class="button" href="{{ route('database.tables.create', $databaseRecord) }}">Create table</a></div>
        @if (empty($tables))
            <div class="empty-state"><strong>No tables yet</strong>Use the SQL workspace below to create the first table.</div>
        @else
            <div class="project-list">
                @foreach ($tables as $table)
                    <article class="project-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                        <strong>{{ $table }}</strong>
                        <a class="button small" href="{{ route('database.tables.show', ['database' => $databaseRecord, 'table' => $table]) }}">Browse / structure</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="panel">
        <div class="section-head"><h2>SQL workspace</h2></div>
        <div class="notice warning" style="margin-bottom:16px">Commands run only through this project's database account. Changes take effect immediately. Query results are limited to 100 rows.</div>
        @if ($queryMessage)<div class="success">{{ $queryMessage }}</div>@endif
        @if ($queryError)<div class="validation-summary">{{ $queryError }}</div>@endif
        <form method="post" action="{{ route('database.query', $databaseRecord) }}">
            @csrf
            <div class="field">
                <label for="sql">SQL command</label>
                <textarea id="sql" name="sql" maxlength="10000" required placeholder="SELECT * FROM your_table">{{ old('sql') }}</textarea>
                @error('sql')<small class="error">{{ $message }}</small>@enderror
            </div>
            <button class="button" type="submit">Run query</button>
        </form>

        @if ($queryColumns)
            <div class="table-wrap" style="margin-top:20px"><table>
                <thead><tr>@foreach ($queryColumns as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($queryRows as $row)
                        <tr>
                            @foreach ($queryColumns as $column)
                                @php($value = data_get($row, $column))
                                <td>{{ is_null($value) ? 'NULL' : (is_scalar($value) ? $value : json_encode($value)) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </section>
@endif
@endsection
