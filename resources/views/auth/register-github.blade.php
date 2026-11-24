@extends('layouts.app')

@section('title', 'Connect GitHub · Elimutaifa Labs')

@section('content')
<style>
    .signup-card{width:min(500px,100%);margin:20px auto}.signup-steps{display:flex;gap:8px;margin:22px 0 26px}.signup-step{flex:1;border-top:3px solid #e4e7ec;padding-top:8px;color:#667085;font-size:11px;font-weight:650}.signup-step.active{border-color:#175cd3;color:#175cd3}.github-choice{padding:16px;border:1px solid #e4e7ec;border-radius:10px;margin-bottom:16px}.github-choice strong{display:block;margin-bottom:3px}
</style>
<section class="auth-wrap signup-card"><div class="panel auth-panel">
    <p class="eyebrow">Create your account · Step 3 of 3</p><h1>Connect GitHub</h1>
    <div class="signup-steps"><div class="signup-step active">1 · Account</div><div class="signup-step active">2 · Email</div><div class="signup-step active">3 · GitHub (optional)</div></div>
    <p class="auth-intro">Verify your GitHub username now, or skip and add it later.</p>
    @if ($githubProfile)
        <div class="github-choice"><strong>GitHub verified</strong><span class="muted">{{ '@'.$githubProfile['login'] }}</span></div>
        <form method="post" action="{{ route('register.complete') }}">@csrf<button class="button full" type="submit">Create account</button></form>
        <a class="button secondary full" style="margin-top:10px" href="{{ route('github.oauth.register') }}">Use a different account</a>
    @else
        <div class="github-choice"><strong>Optional ownership check</strong><span class="muted">We only confirm the account name. Repository access is not requested.</span></div>
        @error('github_username')<div class="form-error" style="margin-bottom:12px">{{ $message }}</div>@enderror
        <a class="button full" href="{{ route('github.oauth.register') }}">Verify GitHub username</a>
        <form method="post" action="{{ route('register.complete') }}" style="margin-top:10px">@csrf<button class="button secondary full" type="submit">Skip for now</button></form>
    @endif
    <form method="post" action="{{ route('register.restart') }}" style="margin-top:12px;text-align:center">@csrf<button class="link-button" type="submit">Start over</button></form>
</div></section>
@endsection
