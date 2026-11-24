@extends('layouts.app')

@section('title', 'Developer hosting and database workspace | TechwareTz Control Center (TCC)')
@section('meta_description', 'Deploy your GitHub projects to hosting and manage project databases from one developer workspace. Submit a project, connect its repository, and track deployment approval.')
@section('robots', 'index, follow, max-image-preview:large')
@section('canonical', url('/'))
@section('og_title', 'Developer hosting and database workspace | TechwareTz Control Center (TCC)')

@push('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "TechwareTz Control Center (TCC)",
  "url": @json(url('/')),
  "logo": @json(asset('images/techwaretz-tcc-mark.png')),
  "description": "A developer workspace for GitHub project deployment and project database management."
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "TechwareTz Control Center (TCC)",
  "url": @json(url('/')),
  "description": "Developer hosting and database workspace."
}
</script>
@endpush

@section('content')
<style>
    .landing{overflow:hidden;border:1px solid #dbe5f0;border-radius:22px;background:linear-gradient(135deg,#fff 0%,#f1f8ff 62%,#e8f8ff 100%);box-shadow:0 16px 40px #123b5b12}
    .landing-hero{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(240px,.8fr);gap:34px;align-items:center;padding:clamp(28px,6vw,68px)}
    .landing h1{max-width:700px;font-size:clamp(34px,5vw,54px);letter-spacing:-1.7px;line-height:1.08}
    .landing-lead{max-width:640px;margin-top:18px;color:#53677f;font-size:18px;line-height:1.65}
    .landing-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:26px}
    .landing .button{min-height:48px;padding:0 20px}.landing .button.secondary{background:#fff;border:1px solid #cbd9e8;color:#164a82}
    .landing-art{display:flex;justify-content:center;align-items:center;min-height:230px;border-radius:20px;background:radial-gradient(circle at 50% 40%,#fff 0,#e2f6ff 56%,#cdeaff 100%)}
    .landing-art img{width:min(72%,230px);height:auto;filter:drop-shadow(0 16px 18px #064b8624)}
    .landing-features{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;padding:0 clamp(20px,6vw,68px) clamp(28px,5vw,56px)}
    .landing-feature{padding:20px;border:1px solid #dbe5f0;border-radius:14px;background:#ffffffd9}
    .landing-feature .icon{display:grid;place-items:center;width:36px;height:36px;margin-bottom:13px;border-radius:11px;background:#e6f4ff;color:#0868d7;font-size:15px;font-weight:800}
    .landing-feature h2{margin-bottom:7px;font-size:16px}.landing-feature p{color:#607187;font-size:14px;line-height:1.55}
    .landing-note{padding:17px clamp(20px,6vw,68px);border-top:1px solid #dbe5f0;color:#607187;font-size:13px}
    @media(max-width:760px){.landing-hero{grid-template-columns:1fr;gap:24px}.landing-art{min-height:170px}.landing-art img{width:130px}.landing-features{grid-template-columns:1fr}}
</style>
<section class="landing">
    <div class="landing-hero">
        <div>
            <p class="eyebrow">A simpler workspace for developers</p>
            <h1>Build your project. We help get it online.</h1>
            <p class="landing-lead">Connect a GitHub repository, request hosting deployment, and manage your project database from one place.</p>
            <div class="landing-actions">
                <a class="button" href="{{ route('register') }}">Create a developer account</a>
                <a class="button secondary" href="{{ route('login') }}">Sign in</a>
            </div>
        </div>
        <div class="landing-art" aria-hidden="true"><img src="{{ asset('images/techwaretz-tcc-mark.png') }}" alt=""></div>
    </div>
    <div class="landing-features">
        <article class="landing-feature"><span class="icon" aria-hidden="true">GH</span><h2>GitHub deployment</h2><p>Register your repository and follow its hosting deployment request through review and activation.</p></article>
        <article class="landing-feature"><span class="icon" aria-hidden="true">DB</span><h2>Project databases</h2><p>Request a database for each project and use the workspace to manage tables and records.</p></article>
        <article class="landing-feature"><span class="icon" aria-hidden="true">✓</span><h2>One developer workspace</h2><p>Keep your approved projects and available services together in a clear account dashboard.</p></article>
    </div>
    <div class="landing-note">TechwareTz Control Center (TCC) · Project hosting and database services for developers</div>
</section>
@endsection
