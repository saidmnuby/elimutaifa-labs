@extends('layouts.app')

@section('title', 'Verify email · Elimutaifa Labs')

@section('content')
<style>
    .signup-card{width:min(500px,100%);margin:20px auto}.signup-steps{display:flex;gap:8px;margin:22px 0 26px}.signup-step{flex:1;border-top:3px solid #e4e7ec;padding-top:8px;color:#667085;font-size:11px;font-weight:650}.signup-step.active{border-color:#175cd3;color:#175cd3}
</style>
<section class="auth-wrap signup-card"><div class="panel auth-panel">
    <p class="eyebrow">Create your account · Step 2 of 3</p><h1>Verify your email</h1>
    <div class="signup-steps"><div class="signup-step active">1 · Account</div><div class="signup-step active">2 · Email</div><div class="signup-step">3 · GitHub (optional)</div></div>
    <p class="auth-intro">Enter the 6-digit code sent to <strong>{{ $email }}</strong>.</p>
    @if (session('mail_error'))<div class="validation-summary">{{ session('mail_error') }}</div>@endif
    <form method="post" action="{{ route('register.verify-email.submit') }}">@csrf
        <div class="field"><label for="code">Email code</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>@error('code')<div class="form-error">{{ $message }}</div>@enderror</div>
        <button class="button full" type="submit">Verify and continue</button>
    </form>
    <form method="post" action="{{ route('register.verify-email.resend') }}" style="margin-top:10px">@csrf<button class="button secondary full" type="submit">Resend code</button></form>
    <form method="post" action="{{ route('register.restart') }}" style="margin-top:10px">@csrf<button class="link-button" type="submit">Use a different email</button></form>
</div></section>
@endsection
