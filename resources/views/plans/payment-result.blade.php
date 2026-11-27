@extends('layouts.app')

@section('title', 'Payment status · TechwareTz Control Center')

@section('content')
<div class="auth-wrap">
    <section class="panel auth-panel">
        @if ($payment->status === \App\Models\PlanPayment::STATUS_PAID)
            <p class="eyebrow">Payment confirmed</p>
            <h1>Plus is active</h1>
            <p class="auth-intro">Your payment was verified and Plus is active until {{ $payment->period_ends_at?->format('d M Y') }}.</p>
        @elseif ($payment->status === \App\Models\PlanPayment::STATUS_FAILED)
            <p class="eyebrow">Payment needs attention</p>
            <h1>We could not verify this payment</h1>
            <p class="auth-intro">{{ $payment->review_note ?: 'Contact support with your transaction reference so we can help.' }}</p>
        @else
            <p class="eyebrow">Payment submitted</p>
            <h1>Waiting for review</h1>
            <p class="auth-intro">An admin will check your transaction reference and activate Plus after the payment is confirmed. Reviews usually take less than one hour.</p>
        @endif

        <div class="notice" style="margin:18px 0">
            <strong>Amount:</strong> TZS {{ number_format($payment->amount) }}<br>
            <strong>Period:</strong> {{ ucfirst($payment->billing_cycle) }}<br>
            <strong>Paid to:</strong> {{ $paymentAccount['label'] ?? $payment->provider_channel }} · {{ $paymentAccount['number'] ?? '' }}<br>
            <strong>Transaction reference:</strong> {{ $payment->provider_transaction_id }}
        </div>

        @if ($payment->status !== \App\Models\PlanPayment::STATUS_PAID)
            <p class="muted" style="font-size:13px;line-height:1.6">If Plus is not active after one hour, call <a href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a> or email <a href="mailto:{{ $supportEmails[0] }}">{{ $supportEmails[0] }}</a> / <a href="mailto:{{ $supportEmails[1] }}">{{ $supportEmails[1] }}</a>.</p>
        @endif

        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="button" href="{{ route('plans.index') }}">View plans</a>
            @if ($payment->status === \App\Models\PlanPayment::STATUS_FAILED)
                <a class="button secondary" href="{{ route('plans.checkout') }}">Submit another reference</a>
            @endif
        </div>
    </section>
</div>
@endsection
