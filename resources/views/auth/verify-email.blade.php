@extends('layouts.app')

@section('title', 'Verify your email · Elimutaifa Labs')

@section('content')
<section class="auth-wrap">
    <div class="panel auth-panel">
        <p class="eyebrow">One quick security step</p>
        <h1>Verify your email</h1>
        <p class="auth-intro">Enter the 6-digit code sent to <strong>{{ auth()->user()->email }}</strong>. The code expires in 10 minutes.</p>
        @if (session('mail_error'))<div class="validation-summary">{{ session('mail_error') }}</div>@endif
        <form method="post" action="{{ route('email.verify') }}">
            @csrf
            <div class="field"><label for="code">Email code</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>@error('code')<div class="form-error">{{ $message }}</div>@enderror</div>
            <button class="button full" type="submit">Verify email</button>
        </form>
        <form method="post" action="{{ route('email.verify.resend') }}" style="margin-top:12px">@csrf<button class="button secondary full" type="submit">Send a new code</button></form>
        <div class="auth-footer">You must verify your email before using projects or database services.</div>
    </div>
</section>
@endsection
