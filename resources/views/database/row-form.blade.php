@extends('layouts.app')

@section('title', ($editing ? 'Edit record' : 'Add record').' · '.$table)

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">{{ $database->project->name }} · {{ $database->database_name }} · {{ $table }}</p><h1>{{ $editing ? 'Edit record' : 'Add record' }}</h1></div>
    <a href="{{ route('database.tables.show', [$database, $table]) }}">Back to table</a>
</div>

<section class="panel" style="max-width:850px">
    <form method="post" action="{{ $editing ? route('database.rows.update', ['database' => $database, 'table' => $table, 'rowKey' => $rowKey]) : route('database.rows.store', [$database, $table]) }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        @error('data')<div class="validation-summary">{{ $message }}</div>@enderror
        @foreach ($columns as $column)
            @php
                $name = $column['Field'];
                $extra = strtolower($column['Extra'] ?? '');
                $isGenerated = str_contains($extra, 'auto_increment') || str_contains($extra, 'generated');
                $isPrimary = ($column['Key'] ?? '') === 'PRI';
                $type = strtolower($column['Type'] ?? '');
                $value = old('data.'.$name, $row ? data_get($row, $name) : ($column['Default'] ?? ''));
                $inputType = str_starts_with($type, 'int') || str_starts_with($type, 'bigint') || str_starts_with($type, 'decimal') || str_starts_with($type, 'float') || str_starts_with($type, 'double') ? 'number' : (str_starts_with($type, 'date') && !str_starts_with($type, 'datetime') ? 'date' : (str_starts_with($type, 'datetime') || str_starts_with($type, 'timestamp') ? 'datetime-local' : 'text'));
            @endphp
            @if ($isGenerated && !$editing)
                <p class="muted" style="margin:0 0 15px"><strong>{{ $name }}</strong> is generated automatically ({{ $column['Type'] }}).</p>
            @elseif ($isPrimary && $editing)
                <div class="field"><label>{{ $name }} · primary key</label><input value="{{ $value }}" disabled><small class="muted">Primary key cannot be changed here.</small></div>
            @else
                <div class="field">
                    <label for="field_{{ $loop->index }}">{{ $name }} <span class="muted">{{ $column['Type'] }}{{ ($column['Null'] ?? 'NO') === 'YES' ? ' · nullable' : ' · required' }}</span></label>
                    @if (str_contains($type, 'text') || str_contains($type, 'blob'))
                        <textarea id="field_{{ $loop->index }}" name="data[{{ $name }}]" rows="4">{{ $value }}</textarea>
                    @else
                        <input id="field_{{ $loop->index }}" name="data[{{ $name }}]" type="{{ $inputType }}" value="{{ $inputType === 'datetime-local' && $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : $value }}" @if ($inputType === 'number') step="any" @endif>
                    @endif
                </div>
            @endif
        @endforeach
        <button class="button" type="submit">{{ $editing ? 'Save changes' : 'Add record' }}</button>
    </form>
</section>
@endsection
