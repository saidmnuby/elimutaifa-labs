@extends('layouts.app')

@section('title', 'Contact us | TechwareTz Control Center')
@section('meta_description', 'Contact TechwareTz Control Center about your account, project deployment, database access, payment or privacy request.')
@section('robots', 'index, follow')
@section('canonical', route('contact'))
@section('og_title', 'Contact TechwareTz Control Center')

@section('content')
<article class="public-page">
    <header class="public-hero">
        <p class="eyebrow">Contact</p>
        <h1>How can we help?</h1>
        <p>Contact TechwareTz about your TCC account, project request, deployment, database access, payment or privacy question. Include the project name or transaction reference when it helps us find the right record. Never send a password, database credential, verification code or private key.</p>
    </header>

    <section class="public-section public-grid">
        <article class="public-card">
            <h2>Email support</h2>
            <div class="contact-method"><div><strong>General support</strong><span>Accounts, projects, deployment and database help</span></div><a href="mailto:info@elimutaifa.co.tz">info@elimutaifa.co.tz</a></div>
            <div class="contact-method"><div><strong>Additional email</strong><span>Use if the main address is unavailable</span></div><a href="mailto:elimutaifa@gmail.com">elimutaifa@gmail.com</a></div>
            <div class="contact-actions"><a class="button" href="mailto:info@elimutaifa.co.tz?subject=TechwareTz%20Control%20Center%20support">Email support</a></div>
        </article>
        <article class="public-card">
            <h2>Phone support</h2>
            <div class="contact-method"><div><strong>Call or WhatsApp</strong><span>For payment follow-up and urgent account help</span></div><a href="tel:+255715874294">0715 874 294</a></div>
            <div class="contact-actions"><a class="button secondary" href="https://wa.me/255715874294" target="_blank" rel="noopener noreferrer">Open WhatsApp</a><a class="button" href="tel:+255715874294">Call</a></div>
        </article>
    </section>

    <section class="public-section public-callout"><strong>Payment support:</strong> include the transaction reference, amount and account you paid to. We normally ask you to allow up to one hour for manual review. Do not include your mobile-money PIN, bank password or one-time code.</section>
</article>
@endsection
