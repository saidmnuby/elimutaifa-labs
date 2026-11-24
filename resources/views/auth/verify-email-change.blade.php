@extends('layouts.app')

@section('title', 'Verify new email · TechwareTz Control Center (TCC)')

@section('content')
<section class="auth-wrap"><div class="panel auth-panel">
    <p class="eyebrow">System settings · Step 2 of 2</p>
    <h1>Verify your new email</h1>
    <p class="auth-intro">Enter the 6-digit code sent to <strong>{{ $newEmail }}</strong>. It expires in 10 minutes.</p>
    <form method="post" action="{{ route('email.change.verify') }}">
        @csrf
        <div class="field"><label for="code">Verification code</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>@error('code')<div class="form-error">{{ $message }}</div>@enderror</div>
        <button class="button full" type="submit">Verify and change email</button>
    </form>
    <form method="post" action="{{ route('email.change.resend') }}" style="margin-top:12px">@csrf<button class="button secondary full" type="submit">Send a new code</button></form>
    <form method="post" action="{{ route('email.change.cancel') }}" style="margin-top:10px">@csrf<button class="link-button full" type="submit">Cancel email change</button></form>
</div></section>
@endsection
