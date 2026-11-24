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

<section class="panel" style="margin-bottom:20px">
    <div class="section-head"><div><h2>Application connection details</h2><span class="muted">For this database only</span></div></div>
    <p class="muted" style="margin-bottom:14px">Use these values in your application’s private <code>.env</code> file. Keep that file out of GitHub.</p>
    <button class="button secondary" id="reveal-database-credentials" type="button"
        data-url="{{ route('database.credentials', $databaseRecord) }}">Show credentials</button>
    <div id="database-credentials-panel" hidden style="margin-top:16px">
        <label for="database-environment-example" style="display:block;font-weight:650;margin-bottom:6px">.env settings</label>
        <textarea id="database-environment-example" readonly rows="6" spellcheck="false" style="width:100%;font:13px/1.5 ui-monospace,SFMono-Regular,Consolas,monospace"></textarea>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
            <button class="button small" id="copy-database-credentials" type="button">Copy settings</button>
            <button class="button small secondary" id="hide-database-credentials" type="button">Hide credentials</button>
        </div>
        <p id="database-credentials-message" class="muted" role="status" aria-live="polite" style="margin-top:8px">Credentials will be hidden automatically after one minute.</p>
    </div>
</section>

@if ($connectionError)
    <div class="notice warning">{{ $connectionError }}</div>
@else
    <section class="panel" style="margin-bottom:20px">
        <div class="section-head"><div><h2>Database tables</h2><span class="muted">{{ count($tables) }} tables</span></div><div style="display:flex;gap:8px;flex-wrap:wrap"><a class="button secondary" href="{{ route('database.export', $databaseRecord) }}">Download SQL backup</a><a class="button" href="{{ route('database.tables.create', $databaseRecord) }}">Create table</a></div></div>
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

<script>
(() => {
    const revealButton = document.getElementById('reveal-database-credentials');
    const panel = document.getElementById('database-credentials-panel');
    const output = document.getElementById('database-environment-example');
    const message = document.getElementById('database-credentials-message');
    const copyButton = document.getElementById('copy-database-credentials');
    const hideButton = document.getElementById('hide-database-credentials');
    let hideTimer;

    const hideCredentials = () => {
        window.clearTimeout(hideTimer);
        output.value = '';
        panel.hidden = true;
        revealButton.hidden = false;
        revealButton.disabled = false;
        message.textContent = 'Credentials will be hidden automatically after one minute.';
    };

    revealButton.addEventListener('click', async () => {
        revealButton.disabled = true;
        revealButton.textContent = 'Loading…';
        message.textContent = '';

        try {
            const response = await fetch(revealButton.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Credentials could not be loaded.');
            }

            output.value = result.environment;
            panel.hidden = false;
            revealButton.hidden = true;
            hideTimer = window.setTimeout(hideCredentials, 60000);
        } catch (error) {
            message.textContent = error.message;
            revealButton.disabled = false;
        } finally {
            revealButton.textContent = 'Show credentials';
        }
    });

    copyButton.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(output.value);
            message.textContent = 'Settings copied. Keep them private.';
        } catch (error) {
            output.focus();
            output.select();
            message.textContent = document.execCommand('copy')
                ? 'Settings copied. Keep them private.'
                : 'Select and copy the settings manually.';
        }
    });

    hideButton.addEventListener('click', hideCredentials);
})();
</script>
@endsection
