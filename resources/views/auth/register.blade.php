@extends('layouts.app')

@section('title', 'Create account · Elimutaifa Labs')

@section('content')
<section class="auth-wrap">
    <div class="panel auth-panel">
        <p class="eyebrow">Join the platform</p>
        <h1>Create a developer account</h1>
        <p class="auth-intro">Your account is reviewed before hosting projects are activated.</p>
        <form method="post" action="{{ url('/register') }}">
            @csrf
            <div class="field">
                <label for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="github_username">GitHub username <span class="muted">(optional)</span></label>
                <input id="github_username" name="github_username" value="{{ old('github_username') }}" autocomplete="username" maxlength="39" placeholder="your-github-name">
                @error('github_username')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                <div class="muted" style="font-size:12px;margin-top:5px">Use at least 12 characters, including letters and numbers.</div>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="button full" type="submit">Create account</button>
        </form>
        <div class="auth-footer">Already registered? <a href="{{ route('login') }}">Sign in</a></div>
    </div>
</section>
@endsection
