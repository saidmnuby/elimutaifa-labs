@extends('layouts.app')

@section('title', 'Developer hosting and database workspace | TechwareTz Control Center (TCC)')
@section('meta_description', 'Deploy your GitHub projects to hosting and manage project databases from one developer workspace. Submit a project, connect its repository, and track deployment approval.')
@section('robots', 'index, follow, max-image-preview:large')
@section('canonical', url('/'))
@section('og_title', 'Developer hosting and database workspace | TechwareTz Control Center (TCC)')

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Organization",
  "name": "TechwareTz Control Center (TCC)",
  "url": @json(url('/')),
  "logo": @json(asset('images/techwaretz-brand.png')),
  "description": "A developer workspace for GitHub project deployment and project database management."
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
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
    .landing-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:clamp(28px,4vw,54px);align-items:center;padding:clamp(38px,6vw,76px)}
    .landing h1{max-width:700px;font-size:clamp(42px,4.5vw,62px);letter-spacing:-2px;line-height:1.04}
    .landing-lead{max-width:620px;margin-top:20px;color:#53677f;font-size:18px;line-height:1.7}
    .landing-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}
    .landing .button{min-height:50px;padding:0 22px}.landing .button.secondary{background:#fff;border:1px solid #cbd9e8;color:#164a82}
    .landing-art{display:flex;justify-content:center;align-items:center;min-height:300px;border-radius:24px;background:radial-gradient(circle at 50% 40%,#fff 0,#e2f6ff 56%,#cdeaff 100%)}
    .landing-art img{display:block;width:min(78%,310px);height:auto;filter:drop-shadow(0 16px 18px #064b8624)}
    .landing-features{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;padding:0 clamp(38px,6vw,76px) clamp(38px,5vw,64px)}
    .landing-feature{min-height:168px;padding:22px;border:1px solid #dbe5f0;border-radius:16px;background:#ffffffd9}
    .landing-feature .icon{display:grid;place-items:center;width:38px;height:38px;margin-bottom:14px;border-radius:11px;background:#e6f4ff;color:#0868d7;font-size:15px;font-weight:800}
    .landing-feature h2{margin-bottom:8px;font-size:16px}.landing-feature p{color:#607187;font-size:14px;line-height:1.6}
    .landing-note{padding:18px clamp(38px,6vw,76px);border-top:1px solid #dbe5f0;color:#607187;font-size:13px}

    /* Tablet */
    @media (min-width:761px) and (max-width:1024px){
        .landing-hero{grid-template-columns:minmax(0,1.1fr) minmax(230px,.9fr);gap:24px;padding:36px}
        .landing h1{font-size:clamp(38px,5vw,50px)}
        .landing-lead{font-size:16px}
        .landing-art{min-height:245px}
        .landing-art img{width:min(82%,250px)}
        .landing-features{grid-template-columns:repeat(2,minmax(0,1fr));padding:0 36px 36px}
        .landing-feature{min-height:150px}
        .landing-feature:last-child{grid-column:1/-1;min-height:auto}
        .landing-note{padding:16px 36px}
    }

    /* Mobile */
    @media (max-width:760px){
        .landing{border-radius:17px}
        .landing-hero{grid-template-columns:1fr;gap:22px;padding:26px 22px 22px}
        .landing h1{max-width:560px;font-size:clamp(36px,9vw,48px);letter-spacing:-1.5px;line-height:1.06}
        .landing-lead{margin-top:14px;font-size:16px;line-height:1.6}
        .landing-actions{display:grid;grid-template-columns:1fr;gap:10px;margin-top:22px}
        .landing .button{width:100%;min-height:48px}
        .landing-art{min-height:0;padding:14px;border-radius:15px}
        .landing-art img{width:min(58vw,190px)}
        .landing-features{grid-template-columns:1fr;gap:11px;padding:0 16px 20px}
        .landing-feature{min-height:0;padding:17px}
        .landing-feature .icon{width:34px;height:34px;margin-bottom:10px}
        .landing-note{padding:15px 20px;font-size:12px;line-height:1.5}
    }

    @media (max-width:380px){
        .landing-hero{padding:23px 17px 18px}
        .landing h1{font-size:34px}
        .landing-lead{font-size:15px}
        .landing-features{padding-right:12px;padding-left:12px}
    }
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
        <div class="landing-art" aria-hidden="true"><img src="{{ asset('images/techwaretz-brand.png') }}" alt=""></div>
    </div>
    <div class="landing-features">
        <article class="landing-feature"><span class="icon" aria-hidden="true">GH</span><h2>GitHub deployment</h2><p>Register your repository and follow its hosting deployment request through review and activation.</p></article>
        <article class="landing-feature"><span class="icon" aria-hidden="true">DB</span><h2>Project databases</h2><p>Request a database for each project and use the workspace to manage tables and records.</p></article>
        <article class="landing-feature"><span class="icon" aria-hidden="true">✓</span><h2>One developer workspace</h2><p>Keep your approved projects and available services together in a clear account dashboard.</p></article>
    </div>
    <div class="landing-note">TechwareTz Control Center (TCC) · Project hosting and database services for developers</div>
</section>
@endsection
