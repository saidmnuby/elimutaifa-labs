@extends('layouts.app')

@section('title', 'Payment status · TechwareTz Control Center')

@section('content')
<div class="auth-wrap">
    <section class="panel auth-panel">
        @if ($payment->status === \App\Models\PlanPayment::STATUS_PAID)
            <p class="eyebrow">Payment confirmed</p>
            <h1>Plus is active</h1>
            <p class="auth-intro">Your payment was confirmed. Your Plus plan is active until {{ $payment->period_ends_at?->format('d M Y') }}.</p>
        @elseif ($payment->status === \App\Models\PlanPayment::STATUS_FAILED || $cancelled)
            <p class="eyebrow">Payment not completed</p>
            <h1>You can try again</h1>
            <p class="auth-intro">No Plus access was added. If money was deducted, wait for Selcom to confirm the transaction before paying again.</p>
        @else
            <p class="eyebrow">Waiting for confirmation</p>
            <h1>Payment is still processing</h1>
            <p class="auth-intro">Selcom has not confirmed this payment yet. Refresh this page in a moment; Plus will activate once confirmation arrives.</p>
        @endif

        <div class="notice">
            <strong>Order:</strong> {{ $payment->order_id }}<br>
            <strong>Amount:</strong> TZS {{ number_format($payment->amount) }}<br>
            <strong>Period:</strong> {{ ucfirst($payment->billing_cycle) }}
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="button" href="{{ route('plans.index') }}">View plans</a>
            @if ($payment->status !== \App\Models\PlanPayment::STATUS_PAID)
                <a class="button secondary" href="{{ request()->fullUrl() }}">Refresh status</a>
            @endif
        </div>
    </section>
</div>
@endsection
