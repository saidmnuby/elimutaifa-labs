@extends('layouts.app')

@section('title', 'Choose a new password · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap"><div class="panel auth-panel">
    <p class="eyebrow">Account recovery</p><h1>Choose a new password</h1>
    <p class="auth-intro">Use at least 12 characters, including letters and numbers.</p>
    <form method="post" action="{{ route('password.reset') }}">@csrf
        <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" required>@error('password')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
        <button class="button full" type="submit">Save new password</button>
    </form>
</div></section>
@endsection
