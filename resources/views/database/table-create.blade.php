@extends('layouts.app')

@section('title', 'Create table · '.$database->database_name)

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">{{ $database->project->name }} · {{ $database->database_name }}</p><h1>Create table</h1><p class="muted" style="margin-top:7px">Define the columns for a new table.</p></div>
    <a href="{{ route('database.show', $database) }}">Back to database</a>
</div>

<section class="panel" style="max-width:900px">
    <form method="post" action="{{ route('database.tables.store', $database) }}" id="create-table-form">
        @csrf
        <div class="field">
            <label for="table_name">Table name</label>
            <input id="table_name" name="table_name" value="{{ old('table_name') }}" maxlength="64" pattern="[A-Za-z][A-Za-z0-9_]*" required>
            @error('table_name')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="section-head"><h2>Columns</h2><button class="button small secondary" type="button" id="add-column">Add column</button></div>
        @error('columns')<div class="validation-summary">{{ $message }}</div>@enderror
        <div id="column-list" class="project-list">
            @foreach (old('columns', [['name' => 'id', 'type' => 'integer', 'primary' => '1', 'auto_increment' => '1'], ['name' => 'name', 'type' => 'varchar']]) as $index => $column)
                <div class="project-card column-definition">
                    <div class="field"><label>Column name</label><input name="columns[{{ $index }}][name]" value="{{ $column['name'] ?? '' }}" maxlength="64" pattern="[A-Za-z][A-Za-z0-9_]*" required>@error("columns.$index.name")<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label>Type</label><select name="columns[{{ $index }}][type]" class="column-type" required>@foreach (['varchar' => 'Varchar', 'text' => 'Text', 'integer' => 'Integer', 'bigint' => 'Big integer', 'decimal' => 'Decimal', 'boolean' => 'Boolean', 'date' => 'Date', 'datetime' => 'Date and time'] as $value => $label)<option value="{{ $value }}" @selected(($column['type'] ?? 'varchar') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="field column-length"><label>Length</label><input type="number" name="columns[{{ $index }}][length]" min="1" max="255" value="{{ $column['length'] ?? 255 }}"></div>
                    <div class="check-grid"><label class="check-option"><input type="checkbox" name="columns[{{ $index }}][nullable]" value="1" @checked(!empty($column['nullable']))><span><strong>Allow NULL</strong></span></label><label class="check-option"><input type="checkbox" name="columns[{{ $index }}][primary]" value="1" @checked(!empty($column['primary']))><span><strong>Primary key</strong></span></label><label class="check-option"><input type="checkbox" name="columns[{{ $index }}][auto_increment]" value="1" @checked(!empty($column['auto_increment']))><span><strong>Auto increment</strong></span></label></div>
                    <button class="button small danger remove-column" type="button" style="margin-top:12px">Remove column</button>
                </div>
            @endforeach
        </div>
        <p class="muted" style="margin:15px 0">Auto increment works with one integer or big integer primary key. Database user must have CREATE permission.</p>
        <button class="button" type="submit">Create table</button>
    </form>
</section>
<template id="column-template">
    <div class="project-card column-definition">
        <div class="field"><label>Column name</label><input data-name="name" maxlength="64" pattern="[A-Za-z][A-Za-z0-9_]*" required></div>
        <div class="field"><label>Type</label><select data-name="type" class="column-type" required><option value="varchar">Varchar</option><option value="text">Text</option><option value="integer">Integer</option><option value="bigint">Big integer</option><option value="decimal">Decimal</option><option value="boolean">Boolean</option><option value="date">Date</option><option value="datetime">Date and time</option></select></div>
        <div class="field column-length"><label>Length</label><input data-name="length" type="number" min="1" max="255" value="255"></div>
        <div class="check-grid"><label class="check-option"><input data-name="nullable" type="checkbox" value="1"><span><strong>Allow NULL</strong></span></label><label class="check-option"><input data-name="primary" type="checkbox" value="1"><span><strong>Primary key</strong></span></label><label class="check-option"><input data-name="auto_increment" type="checkbox" value="1"><span><strong>Auto increment</strong></span></label></div>
        <button class="button small danger remove-column" type="button" style="margin-top:12px">Remove column</button>
    </div>
</template>
<script>
    (() => {
        const list = document.getElementById('column-list');
        const template = document.getElementById('column-template');
        let nextIndex = list.querySelectorAll('.column-definition').length;
        document.getElementById('add-column').addEventListener('click', () => {
            const fragment = template.content.cloneNode(true);
            fragment.querySelectorAll('[data-name]').forEach((field) => {
                field.name = `columns[${nextIndex}][${field.dataset.name}]`;
            });
            list.appendChild(fragment);
            nextIndex += 1;
        });
        list.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-column')) {
                const cards = list.querySelectorAll('.column-definition');
                if (cards.length > 1) event.target.closest('.column-definition').remove();
            }
        });
        list.addEventListener('change', (event) => {
            if (event.target.classList.contains('column-type')) {
                const lengthField = event.target.closest('.column-definition').querySelector('.column-length');
                lengthField.hidden = event.target.value !== 'varchar';
            }
        });
        list.querySelectorAll('.column-type').forEach((field) => {
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
    })();
</script>
@endsection
