@extends('layouts.app')

@section('title', 'Enter reset code · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap"><div class="panel auth-panel">
    <p class="eyebrow">Account recovery</p><h1>Enter your email code</h1>
    <p class="auth-intro">Enter the 6-digit code sent to {{ session('password_reset_email') }}. It expires in 10 minutes.</p>
    @if (session('email_delivery_configured') === false)<div class="validation-summary">Email delivery is not configured yet. Contact the platform admin.</div>@endif
    <form method="post" action="{{ route('password.code.verify') }}">@csrf
        <div class="field"><label for="code">Email code</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>@error('code')<div class="form-error">{{ $message }}</div>@enderror</div>
        <button class="button full" type="submit">Verify code</button>
    </form>
    <form method="post" action="{{ route('password.code.resend') }}" style="margin-top:12px">@csrf<button class="button secondary full" type="submit">Send a new code</button></form>
</div></section>
@endsection
