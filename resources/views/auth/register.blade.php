@extends('layouts.app')

@section('title', 'Create account · Elimutaifa Labs')

@section('content')
<style>
    .signup-card{width:min(500px,100%);margin:20px auto}.signup-steps{display:flex;gap:8px;margin:22px 0 26px}.signup-step{flex:1;border-top:3px solid #e4e7ec;padding-top:8px;color:#667085;font-size:11px;font-weight:650}.signup-step.active{border-color:#175cd3;color:#175cd3}.signup-card h1{font-size:25px}.signup-card .auth-intro{margin:7px 0 22px}
</style>
<section class="auth-wrap signup-card">
    <div class="panel auth-panel">
        <p class="eyebrow">Create your account · Step 1 of 3</p>
        <h1>Your details</h1>
        <div class="signup-steps"><div class="signup-step active">1 · Account</div><div class="signup-step">2 · Email</div><div class="signup-step">3 · GitHub (optional)</div></div>
        <p class="auth-intro">Start with your name, email, and password.</p>
        <form method="post" action="{{ route('register') }}">
            @csrf
            <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required autofocus></div>
            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required><small class="muted">12+ characters, with letters and numbers.</small></div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
            <button class="button full" type="submit">Continue</button>
        </form>
        <div class="auth-footer">Already registered? <a href="{{ route('login') }}">Sign in</a></div>
    </div>
</section>
@endsection
