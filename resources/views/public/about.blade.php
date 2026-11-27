@extends('layouts.app')

@section('title', 'About us | TechwareTz Control Center')
@section('meta_description', 'Learn about TechwareTz Control Center, a developer workspace for GitHub deployment, project hosting and project databases.')
@section('robots', 'index, follow')
@section('canonical', route('about'))
@section('og_title', 'About TechwareTz Control Center')

@section('content')
<article class="public-page">
    <header class="public-hero">
        <p class="eyebrow">About us</p>
        <h1>Practical hosting tools for developers</h1>
        <p>TechwareTz Control Center (TCC) gives independent developers one place to request project hosting, connect a GitHub repository, follow deployments and manage project databases.</p>
    </header>

    <section class="public-section public-grid">
        <article class="public-card"><h2>Why we built TCC</h2><p>Getting a small project online can involve GitHub, hosting folders, subdomains, databases and credentials. TCC brings those steps into a guided workspace so developers can request what each project needs without sharing the main hosting login.</p></article>
        <article class="public-card"><h2>How we work</h2><p>Projects and services are reviewed and assigned by an administrator. Developers can see the status of their own projects, deployments and database requests in their account.</p></article>
        <article class="public-card"><h2>Your project stays yours</h2><p>Connecting a repository does not transfer its ownership or copyright to TCC. The repository remains in the GitHub account that owns it. TCC reads the selected source to verify and deploy it to the project’s assigned hosting folder.</p></article>
        <article class="public-card"><h2>Access is separated by account</h2><p>Developer accounts can use the projects and databases assigned to them. They do not receive the shared cPanel login or access to another developer’s workspace through TCC.</p></article>
    </section>

    <section class="public-section public-callout">
        <strong>About hosting access:</strong> TCC runs on shared hosting managed by TechwareTz. Authorized platform administrators can access hosting files and service records when required to provision, maintain, secure or support the service. That operational access is not a transfer of ownership and is not permission to reuse a developer’s project for unrelated work.
    </section>

    <section class="public-section">
        <h2>Our service</h2>
        <p>TCC is operated by TechwareTz. It currently provides project registration, administrator-reviewed GitHub deployment, subdomain hosting, project database requests, and a workspace for approved database access. Availability and limits depend on the selected plan and the resources available on the hosting service.</p>
        <p style="margin-top:10px">For a clear explanation of each step, see <a href="{{ route('how-it-works') }}">How it works</a>. For questions about your account or project, visit <a href="{{ route('contact') }}">Contact</a>.</p>
    </section>
</article>
@endsection
