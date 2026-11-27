@extends('layouts.app')

@section('title', 'Payment requests · TechwareTz Control Center (TCC)')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Admin</p>
        <h1>Plus payment requests</h1>
        <p class="muted" style="margin-top:7px">Verify each transaction in the receiving bank or mobile money account before activating Plus.</p>
    </div>
    <span class="badge">{{ $payments->total() }} pending</span>
</div>

@if ($errors->any())
    <div class="notice warning" style="margin-bottom:16px">{{ $errors->first() }}</div>
@endif

<section class="panel">
    @if ($payments->isEmpty())
        <div class="empty-state"><strong>No payment requests waiting</strong>Developer payment references will appear here for review.</div>
    @else
        <div class="project-list">
            @foreach ($payments as $payment)
                <article class="project-card">
                    <div class="project-title">
                        <div>
                            <h2>{{ $payment->user->name }}</h2>
                            <p class="muted">{{ $payment->user->email }} · Submitted {{ $payment->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="badge pending">Pending review</span>
                    </div>

                    <div class="notice" style="margin:14px 0">
                        <strong>Amount:</strong> TZS {{ number_format($payment->amount) }}<br>
                        <strong>Plan:</strong> Plus · {{ ucfirst($payment->billing_cycle) }}<br>
                        <strong>Paid to:</strong> {{ $payment->provider_channel }}<br>
                        <strong>Transaction ID:</strong> <code>{{ $payment->provider_transaction_id }}</code>
                    </div>

                    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end">
                        <form method="post" action="{{ route('admin.payment-requests.approve', $payment) }}" onsubmit="return confirm('Have you confirmed this transaction in the receiving account?')">
                            @csrf
                            <button class="button" type="submit">Verify &amp; activate Plus</button>
                        </form>
                        <form method="post" action="{{ route('admin.payment-requests.reject', $payment) }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap" onsubmit="return confirm('Reject this payment reference?')">
                            @csrf
                            <div class="field" style="margin:0;min-width:min(280px,70vw)">
                                <label for="review_note_{{ $payment->id }}">Note for developer (optional)</label>
                                <input id="review_note_{{ $payment->id }}" name="review_note" maxlength="500" placeholder="For example: transaction not found">
                            </div>
                            <button class="button small danger" type="submit">Reject</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $payments->links() }}</div>
    @endif
</section>
@endsection
