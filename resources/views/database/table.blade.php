@extends('layouts.app')

@section('title', $table.' · '.$database->database_name)

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">{{ $database->project->name }} · {{ $database->database_name }}</p><h1>{{ $table }}</h1><p class="muted" style="margin-top:7px">Browse records and inspect the table structure.</p></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap"><a class="button secondary" href="{{ route('database.show', $database) }}">All tables</a><a class="button" href="{{ route('database.rows.create', [$database, $table]) }}">Add record</a></div>
</div>

<section class="panel" style="margin-bottom:20px">
    <div class="section-head"><h2>Structure</h2><span class="badge">{{ count($columns) }} columns</span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>
        <tbody>@foreach ($columns as $column)<tr><td><strong>{{ $column['Field'] }}</strong></td><td>{{ $column['Type'] }}</td><td>{{ $column['Null'] }}</td><td>{{ $column['Key'] ?: '—' }}</td><td>{{ $column['Default'] ?? '—' }}</td><td>{{ $column['Extra'] ?: '—' }}</td></tr>@endforeach</tbody>
    </table></div>
    @if (!$primaryKey)<div class="notice warning" style="margin-top:14px">This table has no single primary key. Records can be viewed and added, but cannot be safely edited or deleted from the manager.</div>@endif
</section>

<section class="panel">
    <div class="section-head"><div><h2>Records</h2><span class="muted">Showing up to 25 per page</span></div><span class="badge">{{ $rows->total() }} total</span></div>
    @if ($rows->isEmpty())
        <div class="empty-state"><strong>No records yet</strong>Add a record to this table.</div>
    @else
        <div class="table-wrap"><table>
            <thead><tr>@foreach ($columns as $column)<th>{{ $column['Field'] }}</th>@endforeach @if ($primaryKey)<th>Actions</th>@endif</tr></thead>
            <tbody>
                @foreach ($rows as $row)
                    @php($rowKeyValue = $primaryKey ? data_get($row, $primaryKey) : null)
                    <tr>
                        @foreach ($columns as $column)
                            @php($value = data_get($row, $column['Field']))
                            <td>{{ is_null($value) ? 'NULL' : (is_scalar($value) ? \Illuminate\Support\Str::limit((string) $value, 180) : json_encode($value)) }}</td>
                        @endforeach
                        @if ($primaryKey)
                            <td><div style="display:flex;gap:6px;align-items:center">
                                <a class="button small secondary" href="{{ route('database.rows.edit', ['database' => $database, 'table' => $table, 'rowKey' => $rowKeyValue]) }}">Edit</a>
                                <form method="post" action="{{ route('database.rows.destroy', ['database' => $database, 'table' => $table, 'rowKey' => $rowKeyValue]) }}" onsubmit="return confirm('Delete this record permanently?')">@csrf @method('DELETE')<button class="button small danger" type="submit">Delete</button></form>
                            </div></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="pagination">{{ $rows->links() }}</div>
    @endif
</section>

<section class="panel" style="margin-top:20px">
    <details>
        <summary style="cursor:pointer;font-weight:700;color:#b42318">Delete table</summary>
        <p class="muted" style="margin:12px 0">This permanently deletes <strong>{{ $table }}</strong> and all records in it.</p>
        <form method="post" action="{{ route('database.tables.destroy', [$database, $table]) }}" onsubmit="return confirm('Delete this entire table and all its data?')">
            @csrf @method('DELETE')
            <div class="field"><label for="confirm_table">Type the table name to confirm</label><input id="confirm_table" name="confirm_table" required autocomplete="off"></div>
            <button class="button danger" type="submit">Delete table permanently</button>
        </form>
    </details>
</section>
@endsection
