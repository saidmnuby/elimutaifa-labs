@extends('layouts.app')

@section('title', 'Sign-in verification · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap">
    <div class="panel auth-panel">
        <p class="eyebrow">Account security</p>
        <h1>Enter your sign-in code</h1>
        <p class="auth-intro">We sent a 6-digit code to {{ $email }}. It expires in 10 minutes.</p>
        <form method="post" action="{{ route('login.code.verify') }}">
            @csrf
            <div class="field"><label for="code">Email code</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>@error('code')<div class="form-error">{{ $message }}</div>@enderror</div>
            <button class="button full" type="submit">Verify and sign in</button>
        </form>
        <form method="post" action="{{ route('login.code.resend') }}" style="margin-top:12px">@csrf<button class="button secondary full" type="submit">Send a new code</button></form>
    </div>
</section>
@endsection
