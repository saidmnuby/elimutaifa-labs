@extends('layouts.app')

@section('title', 'System settings · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Platform administration</p><h1>System settings</h1><p class="muted" style="margin-top:7px">Choose account security rules and admin email alerts.</p></div>
</div>

<form method="post" action="{{ route('admin.system-settings.update') }}" class="panel">
    @csrf
    @method('put')
    <section style="margin-bottom:28px">
        <div class="section-head"><div><h2>Authentication</h2><p class="muted" style="margin-top:4px">Control email checks for all accounts.</p></div></div>
        <label class="check-option" style="display:flex;align-items:flex-start;gap:12px;padding:16px;border:1px solid var(--line);border-radius:11px;margin-bottom:10px">
            <input type="checkbox" name="email_verification_required" value="1" @checked($settings['email_verification_required'])>
            <span><strong>Require email verification</strong><br><span class="muted">New accounts must verify their email before completing registration or using the workspace.</span></span>
        </label>
        <label class="check-option" style="display:flex;align-items:flex-start;gap:12px;padding:16px;border:1px solid var(--line);border-radius:11px">
            <input type="checkbox" name="login_email_2fa" value="1" @checked($settings['login_email_2fa'])>
            <span><strong>Send a sign-in code</strong><br><span class="muted">After the password, send a one-time code to the account email. Requires working SMTP.</span></span>
        </label>
    </section>

    <section style="margin-bottom:24px">
        <div class="section-head"><div><h2>Admin email notifications</h2><p class="muted" style="margin-top:4px">Choose which deployment updates active admins receive.</p></div></div>
        <label class="check-option" style="display:flex;align-items:flex-start;gap:12px;padding:16px;border:1px solid var(--line);border-radius:11px;margin-bottom:10px">
            <input type="checkbox" name="notify_deployment_succeeded" value="1" @checked($settings['notify_deployment_succeeded'])>
            <span><strong>Successful deployments</strong><br><span class="muted">Email admins when a project deployment succeeds.</span></span>
        </label>
        <label class="check-option" style="display:flex;align-items:flex-start;gap:12px;padding:16px;border:1px solid var(--line);border-radius:11px;margin-bottom:10px">
            <input type="checkbox" name="notify_deployment_failed" value="1" @checked($settings['notify_deployment_failed'])>
            <span><strong>Failed deployments</strong><br><span class="muted">Email admins when a project deployment fails or is cancelled.</span></span>
        </label>
        <div class="notice" style="margin:14px 0 0"><strong>Always sent:</strong> account registration, project, and database request emails. These alerts cannot be turned off here.</div>
    </section>

    <button class="button" type="submit">Save settings</button>
</form>
@endsection
