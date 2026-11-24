@extends('layouts.app')

@section('title', 'Change password · Elimutaifa Labs')

@section('content')
<section class="auth-wrap"><div class="panel auth-panel">
    <p class="eyebrow">Account security</p><h1>Change your password</h1>
    <p class="auth-intro">Enter your current password, then choose a new one.</p>
    <form method="post" action="{{ route('password.change') }}">@csrf @method('PUT')
        <div class="field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required>@error('current_password')<div class="form-error">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" required>@error('password')<div class="form-error">{{ $message }}</div>@enderror<div class="muted" style="font-size:12px;margin-top:5px">At least 12 characters, including letters and numbers.</div></div>
        <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
        <button class="button full" type="submit">Change password</button>
    </form>
</div></section>
@endsection
