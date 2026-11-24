@extends('layouts.app')

@section('title', 'Change email · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap"><div class="panel auth-panel">
    <p class="eyebrow">System settings</p>
    <h1>Change your email</h1>
    <p class="auth-intro">Your verified email is <strong>{{ auth()->user()->email }}</strong>.</p>
    <div class="notice" style="margin-bottom:20px">We will send a code to the new address. Your current email stays active until you verify it.</div>
    <form method="post" action="{{ route('email.change.request') }}">
        @csrf
        <div class="field"><label for="email">New email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>@error('email')<div class="form-error">{{ $message }}</div>@enderror</div>
        <button class="button full" type="submit">Send verification code</button>
    </form>
</div></section>
@endsection
