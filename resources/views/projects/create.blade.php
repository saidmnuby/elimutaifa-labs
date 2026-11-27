@extends('layouts.app')

@section('title', 'Connect a project · TechwareTz Control Center (TCC)')

@section('content')
@php
    $githubIdentityVerified = (bool) auth()->user()->github_verified_at;
    $readyForProjectDetails = $githubIdentityVerified && $githubAppConnected;
    $activeStep = ! $githubIdentityVerified ? 1 : ($githubAppConnected ? 3 : 2);
    $singleRepository = count($githubInstallationRepositories) === 1 ? $githubInstallationRepositories[0] : null;
    $selectedRepository = old('repository', $singleRepository);
@endphp

<style>
    .onboarding-shell{max-width:900px;margin:0 auto}
    .onboarding-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:24px}
    .onboarding-heading h1{font-size:32px;letter-spacing:-.8px}
    .onboarding-heading .muted{margin-top:8px;max-width:570px;line-height:1.55}
    .onboarding-back{white-space:nowrap;margin-top:5px}
    .onboarding-meta{display:inline-flex;align-items:center;gap:7px;margin-top:15px;padding:7px 11px;border:1px solid var(--line);border-radius:999px;background:#fff;color:#475467;font-size:12px;font-weight:650}
    .onboarding-meta svg{color:#667085}
    .onboarding-progress{padding:21px 24px 18px;margin-bottom:18px;border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 4px 16px #10182808}
    .onboarding-progress-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
    .onboarding-progress-top strong{font-size:13px}
    .onboarding-progress-top span{color:var(--muted);font-size:12px}
    .onboarding-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));position:relative;list-style:none;margin:0;padding:0}
    .onboarding-steps:before{content:"";position:absolute;left:16.667%;right:16.667%;top:16px;height:2px;background:#e4e7ec}
    .onboarding-steps:after{content:"";position:absolute;left:16.667%;top:16px;height:2px;width:calc((66.666% * {{ max(0, $activeStep - 1) }}) / 2);background:#1570ef;transition:width .25s ease}
    .onboarding-step{position:relative;z-index:1;display:grid;justify-items:center;text-align:center;gap:7px;color:#667085;font-size:12px}
    .onboarding-step-dot{display:grid;place-items:center;width:33px;height:33px;border:2px solid #e4e7ec;border-radius:50%;background:#fff;color:#667085;font-size:12px;font-weight:750}
    .onboarding-step.complete{color:#067647;font-weight:700}
    .onboarding-step.complete .onboarding-step-dot{border-color:#12b76a;background:#12b76a;color:#fff}
    .onboarding-step.current{color:#175cd3;font-weight:750}
    .onboarding-step.current .onboarding-step-dot{border-color:#1570ef;background:#eff8ff;color:#175cd3;box-shadow:0 0 0 4px #eff8ff}
    .onboarding-card{overflow:hidden;padding:0}
    .onboarding-card-head{padding:26px 30px 22px;border-bottom:1px solid var(--line);background:linear-gradient(180deg,#fcfdff 0%,#fff 100%)}
    .onboarding-kicker{display:inline-flex;align-items:center;gap:7px;margin-bottom:11px;color:#175cd3;font-size:12px;font-weight:750;letter-spacing:.04em;text-transform:uppercase}
    .onboarding-card-head h2{font-size:21px;letter-spacing:-.3px}
    .onboarding-card-head p{margin-top:7px;max-width:650px;color:var(--muted);font-size:14px;line-height:1.55}
    .onboarding-card-body{padding:25px 30px 30px}
    .onboarding-info{display:flex;gap:13px;align-items:flex-start;padding:15px 16px;border:1px solid #b2ddff;border-radius:11px;background:#f5faff;color:#1849a9;font-size:13px;line-height:1.5}
    .onboarding-info svg{flex:0 0 19px;margin-top:1px}
    .onboarding-info strong{display:block;margin-bottom:2px;color:#1849a9}
    .onboarding-primary{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:46px;margin-top:19px;padding:0 18px;border:0;border-radius:9px;background:#175cd3;color:white;font:inherit;font-size:14px;font-weight:750;text-decoration:none;box-shadow:0 2px 4px #1018281a;transition:background .15s ease,transform .15s ease}
    .onboarding-primary:hover{background:#1849a9;color:#fff;text-decoration:none;transform:translateY(-1px)}
    .onboarding-secondary{display:inline-flex;align-items:center;gap:7px;color:#175cd3;font-size:13px;font-weight:700;text-decoration:none}
    .onboarding-secondary:hover{text-decoration:underline}
    .onboarding-completed{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;margin-bottom:12px;border:1px solid #abefc6;border-radius:10px;background:#f6fef9}
    .onboarding-check{display:grid;place-items:center;flex:0 0 24px;height:24px;border-radius:50%;background:#12b76a;color:#fff;font-size:13px;font-weight:800}
    .onboarding-completed strong{display:block;font-size:13px;color:#067647}
    .onboarding-completed small{display:block;margin-top:3px;color:#475467;font-size:12px}
    .onboarding-completed .onboarding-secondary{margin:3px 0 0 auto;white-space:nowrap}
    .onboarding-form{border-top:1px solid var(--line);margin-top:22px;padding-top:23px}
    .onboarding-form-title{margin-bottom:19px}
    .onboarding-form-title h3{margin:0;font-size:16px}
    .onboarding-form-title p{margin-top:5px;color:var(--muted);font-size:13px;line-height:1.45}
    .onboarding-form .field{margin-bottom:18px}
    .onboarding-form .field label{font-size:13px}
    .repo-verified{display:block;margin-top:7px;color:#067647;font-size:12px;font-weight:700;min-height:18px}
    .repo-verified:empty{display:none}
    .onboarding-footer{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:20px;padding-top:18px;border-top:1px solid var(--line)}
    .onboarding-footer-note{max-width:430px;color:var(--muted);font-size:12px;line-height:1.45}
    .onboarding-submit{min-height:44px;padding:0 17px}
    @media(max-width:700px){
        .onboarding-heading{display:block}.onboarding-heading h1{font-size:27px}.onboarding-back{display:inline-block;margin-top:13px}
        .onboarding-progress{padding:17px 14px}
        .onboarding-step{font-size:10px;line-height:1.25}.onboarding-card-head{padding:22px 19px 19px}.onboarding-card-body{padding:20px 19px 22px}
        .onboarding-completed{flex-wrap:wrap}.onboarding-completed .onboarding-secondary{margin-left:36px}.onboarding-footer{align-items:stretch;flex-direction:column}.onboarding-submit{width:100%}
    }
</style>

<div class="onboarding-shell">
    <div class="notice" style="margin-bottom:18px">
        <strong>{{ ucfirst(auth()->user()->plan) }} plan</strong>
        · Projects used: {{ $projectCount }}{{ $projectLimit === null ? ' / unlimited' : ' / '.$projectLimit }}
        @if ($projectLimit !== null && $projectCount >= $projectLimit)
            <span> You have reached your project limit. Ask the admin to upgrade your plan.</span>
        @endif
    </div>
    <header class="onboarding-heading">
        <div>
            <p class="eyebrow">Developer · GitHub deployment</p>
            <h1>Connect your project</h1>
            <p class="muted">We’ll verify your GitHub account and repository, then collect the details needed for admin review.</p>
            <span class="onboarding-meta" aria-label="Three steps, about two minutes">
                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 5.8v4.5l3 1.7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                3 steps · about 2 minutes
            </span>
        </div>
        <a class="onboarding-back" href="{{ route('dashboard') }}">Back to dashboard</a>
    </header>

    <nav class="onboarding-progress" aria-label="Project setup progress">
        <div class="onboarding-progress-top">
            <strong>Project setup</strong>
            <span>Step {{ $activeStep }} of 3</span>
        </div>
        <ol class="onboarding-steps">
            <li class="onboarding-step {{ $githubIdentityVerified ? 'complete' : 'current' }}">
                <span class="onboarding-step-dot">{{ $githubIdentityVerified ? '✓' : '1' }}</span>
                <span>Verify account</span>
            </li>
            <li class="onboarding-step {{ $githubAppConnected ? 'complete' : ($activeStep === 2 ? 'current' : '') }}">
                <span class="onboarding-step-dot">{{ $githubAppConnected ? '✓' : '2' }}</span>
                <span>Connect repository</span>
            </li>
            <li class="onboarding-step {{ $activeStep === 3 ? 'current' : '' }}">
                <span class="onboarding-step-dot">3</span>
                <span>Project details</span>
            </li>
        </ol>
    </nav>

    <section class="panel onboarding-card" aria-labelledby="current-step-title">
        <div class="onboarding-card-head">
            <span class="onboarding-kicker">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 2.5 16 5v4.7c0 3.4-2.4 6.3-6 7.8-3.6-1.5-6-4.4-6-7.8V5l6-2.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="m7.5 9.9 1.7 1.7 3.4-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Step {{ $activeStep }} · {{ $activeStep === 1 ? 'Account verification' : ($activeStep === 2 ? 'Repository access' : 'Project details') }}
            </span>
            <h2 id="current-step-title">
                {{ $activeStep === 1 ? 'Verify the GitHub account you use' : ($activeStep === 2 ? 'Choose the repository to connect' : 'Tell us about your project') }}
            </h2>
            <p>
                @if ($activeStep === 1)
                    This confirms your identity. You’ll return here automatically when GitHub verification is complete.
                @elseif ($activeStep === 2)
                    You’ll choose which repository the read-only GitHub App can access. You’ll return here after approval.
                @else
                    Your GitHub account and repository access are verified. Select the repository and add the remaining project details.
                @endif
            </p>
        </div>

        <div class="onboarding-card-body">
            @if ($activeStep > 1)
                <div class="onboarding-completed">
                    <span class="onboarding-check" aria-hidden="true">✓</span>
                    <span><strong>GitHub account verified</strong><small>{{ '@'.auth()->user()->github_username }}</small></span>
                </div>
            @endif

            @if ($activeStep === 1)
                <div class="onboarding-info">
                    <svg width="19" height="19" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 9v4m0-6h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span><strong>What happens next</strong>GitHub confirms which account is yours. This step does not grant repository access.</span>
                </div>
                <a class="onboarding-primary" href="{{ route('github.oauth.project') }}">
                    Continue with GitHub
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @elseif ($activeStep === 2)
                <div class="onboarding-info">
                    <svg width="19" height="19" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 9v4m0-6h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span><strong>Access is read-only</strong>The App can check and read the repository you select. It cannot push changes to your GitHub repository.</span>
                </div>
                <a class="onboarding-primary" href="{{ route('github.app.install') }}">
                    Continue to GitHub
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <p class="muted" style="font-size:12px;margin-top:12px">Choose only the repository you want to connect to this project.</p>
            @else
                <div class="onboarding-completed">
                    <span class="onboarding-check" aria-hidden="true">✓</span>
                    <span><strong>Repository access verified</strong><small>{{ '@'.$githubInstallationAccount }} · {{ count($githubInstallationRepositories) }} {{ \Illuminate\Support\Str::plural('repository', count($githubInstallationRepositories)) }} available</small></span>
                    <a class="onboarding-secondary" href="{{ route('github.app.install', ['manage' => 1]) }}">Manage access</a>
                </div>

                <form method="post" action="{{ route('projects.store') }}" class="onboarding-form">
                    @csrf
                    <div class="onboarding-form-title">
                        <h3>Project information</h3>
                        <p>These details go to the admin for review. You can request a database separately later.</p>
                    </div>

                    <div class="field">
                        <label for="name">Project name</label>
                        <input id="name" name="name" value="{{ old('name') }}" maxlength="120" placeholder="e.g. Online shop" required>
                        @error('name')<small class="error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="description">What does your project do? <span class="muted">(optional)</span></label>
                        <textarea id="description" name="description" maxlength="2000" placeholder="A short description helps the admin understand your project.">{{ old('description') }}</textarea>
                        @error('description')<small class="error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="repository">Verified GitHub repository</label>
                        <select id="repository" name="repository" required data-repository-status="repository-access-status">
                            <option value="">Select a repository</option>
                            @foreach ($githubInstallationRepositories as $repositoryName)
                                <option value="{{ $repositoryName }}" @selected($selectedRepository === $repositoryName)>{{ $repositoryName }}</option>
                            @endforeach
                        </select>
                        <small id="repository-access-status" class="repo-verified" role="status" aria-live="polite"></small>
                        @error('repository')<small class="error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="branch">Deployment branch</label>
                        <input id="branch" name="branch" value="{{ old('branch', 'main') }}" maxlength="120" placeholder="main" required>
                        <small class="muted">Usually <code>main</code>.</small>
                        @error('branch')<small class="error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="hostname">Requested domain or subdomain</label>
                        <input id="hostname" name="hostname" value="{{ old('hostname') }}" placeholder="project.elimutaifa.co.tz" maxlength="253" required>
                        @error('hostname')<small class="error">{{ $message }}</small>@enderror
                    </div>

                    <div class="onboarding-footer">
                        <p class="onboarding-footer-note">Submitting sends a request for admin review. It does not start deployment or create a database.</p>
                        <button class="button onboarding-submit" type="submit" @disabled($projectLimit !== null && $projectCount >= $projectLimit)>Send project request</button>
                    </div>
                </form>
            @endif
        </div>
    </section>
</div>

<script>
    (() => {
        const select = document.getElementById('repository');
        const status = document.getElementById(select?.dataset.repositoryStatus ?? '');
        if (!select || !status) return;

        const updateStatus = () => {
            status.textContent = select.value ? `GitHub confirmed access to ${select.value}.` : '';
        };

        select.addEventListener('change', updateStatus);
        updateStatus();
    })();
</script>
@endsection
