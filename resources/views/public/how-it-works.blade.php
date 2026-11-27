@extends('layouts.app')

@section('title', 'How it works | TechwareTz Control Center')
@section('meta_description', 'Understand how TCC connects a selected GitHub repository, approves a project, deploys to its assigned subdomain and provisions a project database.')
@section('robots', 'index, follow')
@section('canonical', route('how-it-works'))
@section('og_title', 'How TechwareTz Control Center works')

@section('content')
<article class="public-page">
    <header class="public-hero">
        <p class="eyebrow">System guide</p>
        <h1>How the developer workspace works</h1>
        <p>TCC connects your own GitHub project to an assigned hosting folder and optional database. Each service has its own request and approval steps.</p>
    </header>

    <section class="public-section">
        <h2>Project hosting and deployment</h2>
        <div class="public-steps">
            <article class="public-step"><span class="public-step-number">1</span><div><h2>Create and verify your account</h2><p>Register with your name, email and password. Verify your email. Connecting a GitHub account confirms which GitHub identity you use for your project request.</p></div></article>
            <article class="public-step"><span class="public-step-number">2</span><div><h2>Choose which repository to connect</h2><p>Install the TCC GitHub App on the repository you choose. The App requests read-only repository contents access for verification and deployment. It does not push commits to your GitHub repository.</p></div></article>
            <article class="public-step"><span class="public-step-number">3</span><div><h2>Submit your project</h2><p>Provide the repository, branch, project name and requested hostname. The request waits for an administrator to review it and assign the hosting folder and services.</p></div></article>
            <article class="public-step"><span class="public-step-number">4</span><div><h2>Admin approves the project</h2><p>The administrator checks the request, chooses the project’s subdomain and hosting folder, then activates deployment. The destination is set by the platform administrator; a developer workflow cannot choose an arbitrary hosting path.</p></div></article>
            <article class="public-step"><span class="public-step-number">5</span><div><h2>Push changes to the approved branch</h2><p>A push to the configured branch starts a deployment for the approved repository and commit. TCC records the deployment status and shows whether it succeeded or failed.</p></div></article>
        </div>
    </section>

    <section class="public-section">
        <h2>Project databases</h2>
        <div class="public-steps">
            <article class="public-step"><span class="public-step-number">1</span><div><h2>Request a database for your project</h2><p>Choose one of your projects and request a database. This is separate from the GitHub deployment request.</p></div></article>
            <article class="public-step"><span class="public-step-number">2</span><div><h2>Admin creates and assigns access</h2><p>An administrator provisions a database and a database user for the project, then adds the connection details to TCC. Database passwords are stored encrypted in the portal.</p></div></article>
            <article class="public-step"><span class="public-step-number">3</span><div><h2>Manage only your assigned database</h2><p>When access is active, you can open the database workspace to work with tables and records, run allowed SQL commands, and import or export a SQL backup. Access is scoped to the database assigned to your project.</p></div></article>
        </div>
    </section>

    <section class="public-section public-grid">
        <article class="public-card"><h2>What TCC can read</h2><p>The GitHub App can read repositories selected for the installation. TCC stores project details, the selected repository and branch, deployment status, and database connection details needed to provide the service.</p></article>
        <article class="public-card"><h2>Who can access project files</h2><p>Your developer account does not expose another developer’s project. Authorized platform administrators do have hosting-level access needed to operate and support the shared hosting account. TCC does not claim that such administrators are technically unable to access hosted files.</p></article>
        <article class="public-card"><h2>What you remain responsible for</h2><p>You keep ownership of your source code and remain responsible for its content, licenses, dependencies, backups, and any personal data collected by your own application.</p></article>
        <article class="public-card"><h2>Service limits</h2><p>Free and Plus plans have different service limits. Requests can require administrator review and depend on available hosting capacity. Check the <a href="{{ route('plans.index') }}">plans page</a> for current plan details.</p></article>
    </section>

    <section class="public-section public-callout"><strong>No shared cPanel password is given to developers.</strong> Use the TCC workspace for your assigned deployment and database services. For data handling details, read the <a href="{{ route('privacy') }}">Privacy Notice</a>.</section>
</article>
@endsection
