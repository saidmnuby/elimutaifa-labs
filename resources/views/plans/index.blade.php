@extends('layouts.app')

@section('title', 'Plans & pricing · TechwareTz Control Center (TCC)')

@section('content')
<style>
    .plans-shell{max-width:980px;margin:0 auto}
    .plans-intro{text-align:center;max-width:700px;margin:0 auto 30px}
    .plans-intro h1{font-size:34px;letter-spacing:-.8px}
    .plans-intro p{margin:11px auto 0;color:var(--muted);line-height:1.65}
    .plans-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;align-items:stretch}
    .plan-card{position:relative;display:flex;flex-direction:column;padding:26px;border:1px solid var(--line);border-radius:17px;background:#fff;box-shadow:0 5px 18px #1018280b}
    .plan-card.plus{border:2px solid var(--blue);box-shadow:0 12px 32px #0868d71c}
    .plan-top{display:flex;justify-content:space-between;align-items:center;gap:10px}
    .plan-name{font-size:20px;font-weight:800;letter-spacing:-.3px}
    .plan-current,.plan-featured{padding:5px 9px;border-radius:999px;background:#eff8ff;color:#175cd3;font-size:11px;font-weight:800}
    .plan-price{margin:18px 0 4px;font-size:32px;font-weight:850;letter-spacing:-1px;color:var(--ink)}
    .plan-price small{font-size:14px;font-weight:550;letter-spacing:0;color:var(--muted)}
    .plan-price-options{display:grid;gap:3px;margin:12px 0 17px;padding:12px 13px;border-radius:10px;background:#f6f9fc;font-size:14px}
    .plan-price-options span{display:flex;justify-content:space-between;gap:12px}
    .plan-summary{min-height:45px;color:var(--muted);font-size:13px;line-height:1.55}
    .plan-divider{height:1px;margin:20px 0 16px;background:var(--line)}
    .plan-features{display:grid;gap:12px;margin:0;padding:0;list-style:none}
    .plan-features li{display:flex;gap:10px;align-items:flex-start;color:#344054;font-size:13px;line-height:1.5}
    .plan-check{display:grid;place-items:center;flex:0 0 20px;width:20px;height:20px;border-radius:50%;background:#ecfdf3;color:#039855;font-size:12px;font-weight:900}
    .plan-action{width:100%;margin-top:auto;padding-top:22px}
    .plan-action .button{width:100%;min-height:46px}
    .plans-footnote{margin:18px auto 0;max-width:780px;color:var(--muted);font-size:12px;text-align:center;line-height:1.6}
    @media(max-width:700px){.plans-intro h1{font-size:28px}.plans-grid{grid-template-columns:1fr;gap:14px}.plan-card{padding:22px}.plan-summary{min-height:0}}
</style>

<div class="plans-shell">
    <header class="plans-intro">
        <p class="eyebrow">Plans for your projects</p>
        <h1>Choose the plan that fits your work</h1>
        <p>Compare project hosting and database limits. Each project connects one GitHub repository to one subdomain.</p>
    </header>

    <div class="plans-grid">
        <article class="plan-card">
            <div class="plan-top">
                <h2 class="plan-name">Free</h2>
                @unless ($user->isPlus())<span class="plan-current">Your plan</span>@endunless
            </div>
            <div class="plan-price">TZS 0 <small>/ always</small></div>
            <p class="plan-summary">Start hosting projects and managing their databases with no subscription fee.</p>
            <div class="plan-divider"></div>
            <ul class="plan-features">
                <li><span class="plan-check">✓</span><span>Up to <strong>5 projects</strong>, each connected to one GitHub repository and one subdomain.</span></li>
                <li><span class="plan-check">✓</span><span>Up to <strong>50 MB of deployable files per project</strong>, including generated build files.</span></li>
                <li><span class="plan-check">✓</span><span>Up to <strong>5 databases per project/subdomain</strong>.</span></li>
                <li><span class="plan-check">✓</span><span>GitHub push-to-deploy after project approval.</span></li>
                <li><span class="plan-check">✓</span><span>Database workspace with SQL tools, import, and export.</span></li>
            </ul>
            <div class="plan-action">
                @unless ($user->isPlus())
                    <span class="button secondary" aria-current="true">Current plan</span>
                @else
                    <span class="badge active" style="justify-content:center;width:100%;padding:12px">Free plan available</span>
                @endunless
            </div>
        </article>

        <article class="plan-card plus">
            <div class="plan-top">
                <h2 class="plan-name">Plus</h2>
                <span class="plan-featured">More room to grow</span>
            </div>
            <div class="plan-price">Plus <small>monthly or yearly</small></div>
            <div class="plan-price-options">
                <span><strong>Monthly</strong><strong>@if ($pricing['monthly'] < $pricing['base_monthly'])<s class="muted">TZS {{ number_format($pricing['base_monthly']) }}</s> @endif TZS {{ number_format($pricing['monthly']) }} / month</strong></span>
                <span><strong>Yearly</strong><strong>@if ($pricing['yearly'] < $pricing['base_yearly'])<s class="muted">TZS {{ number_format($pricing['base_yearly']) }}</s> @endif TZS {{ number_format($pricing['yearly']) }} / year</strong></span>
            </div>
            @if ($pricing['offer_active'])<p class="badge active" style="display:inline-flex;margin:0 0 12px">{{ $pricing['offer_title'] }}</p>@endif
            <p class="plan-summary">Host more projects and databases without the Free plan's count and package-size caps.</p>
            <div class="plan-divider"></div>
            <ul class="plan-features">
                <li><span class="plan-check">✓</span><span><strong>Unlimited projects, repositories, and subdomains.</strong></span></li>
                <li><span class="plan-check">✓</span><span><strong>Unlimited project files and deployments.</strong></span></li>
                <li><span class="plan-check">✓</span><span><strong>Unlimited databases per project.</strong></span></li>
                <li><span class="plan-check">✓</span><span>GitHub push-to-deploy after project approval.</span></li>
                <li><span class="plan-check">✓</span><span>Database workspace with SQL tools, import, and export.</span></li>
            </ul>
            <div class="plan-action">
                @if ($user->role === \App\Models\User::ROLE_DEVELOPER && $user->status === \App\Models\User::STATUS_ACTIVE)
                    <a class="button" href="{{ route('plans.checkout') }}">{{ $user->isPlus() ? 'Renew Plus' : 'Get Plus' }}</a>
                    @if ($user->isPlus() && $user->plus_expires_at)
                        <p class="muted" style="font-size:12px;text-align:center;margin-top:8px">Active until {{ $user->plus_expires_at->format('d M Y') }}</p>
                    @endif
                @else
                    <span class="badge pending" style="justify-content:center;width:100%;padding:12px">Admin approval required</span>
                @endif
            </div>
        </article>
    </div>

    <p class="plans-footnote">Pay by bank or mobile money and submit your transaction reference. Plus is activated after an admin verifies the payment. Hosting remains subject to available resources.</p>
</div>
@endsection
