@extends('layouts.app')

@section('title', 'Privacy Notice | TechwareTz Control Center')
@section('meta_description', 'Learn what personal and project information TechwareTz Control Center uses, why it is needed, how access is controlled, and how to exercise privacy rights.')
@section('robots', 'index, follow')
@section('canonical', route('privacy'))
@section('og_title', 'Privacy Notice | TechwareTz Control Center')

@section('content')
<article class="public-page privacy-page">
    <header class="public-hero">
        <p class="eyebrow">Privacy</p>
        <h1>Privacy Notice</h1>
        <p>This notice explains how TechwareTz Control Center (TCC), operated by TechwareTz, handles account and project information when you use the portal and its hosting or database services.</p>
        <p><strong>Last updated:</strong> 28 November 2026</p>
    </header>

    <section class="public-section">
        <h2>1. Information we handle</h2>
        <ul>
            <li><strong>Account details:</strong> your name, email address, password hash, account status and plan. Passwords are stored as password hashes, not as readable passwords.</li>
            <li><strong>GitHub and project details:</strong> verified GitHub username and account ID, selected repository, branch, project name, requested hostname, administrator-approved hosting destination, and deployment status.</li>
            <li><strong>Database service details:</strong> database name, username, host and encrypted database password needed to provide your assigned database service.</li>
            <li><strong>Payment records:</strong> plan, amount, selected payment channel, transaction reference and review status. Payments are submitted by transaction reference and reviewed by an administrator.</li>
            <li><strong>Security and activity records:</strong> actions taken in the portal, timestamps and IP addresses used for security, troubleshooting, service administration and audit.</li>
        </ul>
        <p>Registration details are held temporarily while email verification is completed. The registration draft expires after a limited period if registration is not completed.</p>
    </section>

    <section class="public-section">
        <h2>2. Why we use this information</h2>
        <p>We use information to create and secure accounts, verify selected GitHub repositories, review project and database requests, deploy approved source code to the assigned hosting folder, provide database access, process plan requests, send service notices, prevent misuse, and maintain service records.</p>
        <p style="margin-top:9px">A verified transaction reference can be matched automatically only after an administrator has recorded that the money arrived. Requests that do not match are reviewed manually.</p>
        <p style="margin-top:9px">TCC uses necessary session cookies to keep you signed in and protect forms. These are required for secure account features to work.</p>
    </section>

    <section class="public-section">
        <h2>3. GitHub repositories and project ownership</h2>
        <ul>
            <li>Your GitHub repository remains in the GitHub account that owns it. Connecting it to TCC does not transfer its ownership or copyright.</li>
            <li>The GitHub App requests read-only access to repository contents and can access only repositories allowed in that GitHub App installation. It cannot push commits to your repository through that read-only access.</li>
            <li>For an approved deployment, the selected source commit is read and copied to the hosting folder assigned to your project. This copy is needed to publish and operate your site.</li>
            <li>TCC does not sell or reuse your source code for unrelated projects. You remain responsible for what you put in your repository, including removing secrets and credentials before pushing.</li>
        </ul>
        <p>Developer accounts are restricted to their own projects and assigned databases in TCC. Because hosting is managed on a shared cPanel account, authorized TechwareTz administrators can technically access hosting files and service records when needed to provision, maintain, secure or support the platform. The service does not promise that hosting administrators are technically unable to access those files.</p>
    </section>

    <section class="public-section">
        <h2>4. Who may receive or process information</h2>
        <p>Information may be shared only as needed with:</p>
        <ul>
            <li><strong>GitHub:</strong> to confirm account/repository access and support deployment workflows.</li>
            <li><strong>Hosting and email providers:</strong> to host TCC, serve project sites, store databases, and send account or service emails.</li>
            <li><strong>Authorized TechwareTz administrators:</strong> to review requests and operate, secure and support TCC and its hosting services.</li>
            <li><strong>Competent authorities:</strong> where disclosure is required by applicable law or a lawful request.</li>
        </ul>
        <p>Some providers may process information outside Tanzania. Any such processing or transfer is subject to applicable Tanzanian data-protection requirements and the arrangements available for that provider.</p>
    </section>

    <section class="public-section">
        <h2>5. Security and retention</h2>
        <p>Controls in the service include password hashing, authenticated project ownership checks, read-only repository access for GitHub verification, administrator-controlled deployment destinations, encryption of stored database passwords, and activity records for important actions. No online service can guarantee absolute security; keep your GitHub account, email and device secure, and do not put secrets in source code.</p>
        <p style="margin-top:9px">We keep information while it is needed to provide the service, administer an account or project, maintain security and audit records, resolve disputes, or meet legal and accounting obligations. Retention can differ by record type. When information is no longer needed and no lawful reason requires it to be retained, it should be deleted or de-identified.</p>
    </section>

    <section class="public-section">
        <h2>6. Your privacy choices and rights</h2>
        <p>Subject to the conditions and exceptions in applicable law, you may ask to access or correct your personal data, request deletion or restriction, object to certain processing, request a copy or transfer of data, withdraw consent where processing relies on consent, and raise a privacy complaint. Some information must be retained to operate the service, protect security, resolve disputes or comply with law.</p>
        <p style="margin-top:9px">To make a request, contact <a href="mailto:info@elimutaifa.co.tz">info@elimutaifa.co.tz</a> or <a href="mailto:elimutaifa@gmail.com">elimutaifa@gmail.com</a>. We may need to verify your identity before acting. You may also contact the <a href="https://pdpc.go.tz/data-subject-rights/" target="_blank" rel="noopener noreferrer">Personal Data Protection Commission (PDPC)</a> or read the <a href="https://oagmis.oag.go.tz/portal/acts/237" target="_blank" rel="noopener noreferrer">Personal Data Protection Act, 2022</a>.</p>
    </section>

    <section class="public-section">
        <h2>7. Information in your own project</h2>
        <p>You decide what personal data your application collects from its visitors or customers. You are responsible for providing your own privacy information, using that data lawfully, securing it, and handling requests from people who use your project. TCC provides hosting and database tools; you should not upload personal or sensitive data unless your project and plan are ready to protect it.</p>
    </section>

    <section class="public-section">
        <h2>8. Tanzania data-protection framework</h2>
        <p>This notice is intended to explain TCC’s current practices in light of the Personal Data Protection Act, 2022 and applicable regulations. It does not replace the law or a case-specific legal review. You can read the <a href="https://oagmis.oag.go.tz/portal/legislation/1350" target="_blank" rel="noopener noreferrer">Personal Data Protection (Personal Data Collection and Processing) Regulations, 2023</a> and the <a href="https://oagmis.oag.go.tz/portal/legislation/3833" target="_blank" rel="noopener noreferrer">2024 amendment</a>.</p>
        <p style="margin-top:9px">For questions about this notice, contact TechwareTz using the details on our <a href="{{ route('contact') }}">Contact page</a>.</p>
    </section>
</article>
@endsection
