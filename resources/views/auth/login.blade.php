@extends('layouts.app')

@section('title', 'Sign in · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap">
    <div class="panel auth-panel">
        <p class="eyebrow">Developer hosting</p>
        <h1>Sign in to your workspace</h1>
        <p class="auth-intro">Manage your registered projects and hosting services.</p>
        <form method="post" action="{{ url('/login') }}">
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div style="text-align:right;margin:-8px 0 16px;font-size:13px"><a href="{{ route('password.request') }}">Forgot password?</a></div>
            <label class="muted" style="display:flex;align-items:center;gap:8px;margin:0 0 18px;font-size:13px"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="button full" type="submit">Sign in</button>
        </form>
        <div class="auth-footer">New here? <a href="{{ route('register') }}">Create a developer account</a></div>
    </div>
</section>
@endsection
