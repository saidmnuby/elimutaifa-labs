@extends('layouts.app')

@section('title', 'Reset password · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap"><div class="panel auth-panel">
    <p class="eyebrow">Account recovery</p><h1>Forgot your password?</h1>
    <p class="auth-intro">Enter your account email. We will send a short-lived code if the account exists.</p>
    <form method="post" action="{{ route('password.email') }}">@csrf
        <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<div class="form-error">{{ $message }}</div>@enderror</div>
        <button class="button full" type="submit">Send reset code</button>
    </form>
    <div class="auth-footer"><a href="{{ route('login') }}">Back to sign in</a></div>
</div></section>
@endsection
