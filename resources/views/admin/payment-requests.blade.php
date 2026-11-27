@extends('layouts.app')

@section('title', 'Payments and plans · TechwareTz Control Center (TCC)')

@section('content')
<style>
    .payments-section{margin-top:22px}.payments-section .section-head{align-items:flex-start}.receipt-list{display:grid;gap:9px}.receipt-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid var(--line);border-radius:10px}.payment-form-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:0 12px}.payment-form-grid .field{margin-bottom:10px}.payment-actions{display:flex;gap:8px;align-items:end;flex-wrap:wrap}.plan-access-form{display:flex;gap:7px;align-items:center}.plan-access-form select{width:auto;min-width:105px}.@media(max-width:760px){.payment-form-grid{grid-template-columns:1fr}.receipt-row{align-items:flex-start;flex-direction:column}.plan-access-form{align-items:flex-start;flex-direction:column}}
</style>

<div class="page-heading">
    <div>
        <p class="eyebrow">Admin</p>
        <h1>Payments &amp; plans</h1>
        <p class="muted" style="margin-top:7px">Log received transactions, review Plus requests, and manage Free/Plus access from here.</p>
    </div>
    <span class="badge">{{ $payments->total() }} awaiting review</span>
</div>

@if (session('success'))<div class="notice">{{ session('success') }}</div>@endif
@if (session('warning'))<div class="notice warning">{{ session('warning') }}</div>@endif
@if ($errors->any())<div class="notice warning">{{ $errors->first() }}</div>@endif

<section class="panel">
    <div class="section-head">
        <div><h2>Log a verified transaction</h2><p class="muted" style="margin-top:5px">First confirm the money arrived in the selected account. A matching developer request will be approved automatically.</p></div>
    </div>
    <form method="post" action="{{ route('admin.payment-requests.receipts.store') }}">
        @csrf
        <div class="payment-form-grid">
            <div class="field">
                <label for="receipt_reference">Transaction ID / reference</label>
                <input id="receipt_reference" name="transaction_reference" value="{{ old('transaction_reference') }}" maxlength="100" required>
                @error('transaction_reference')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="receipt_amount">Amount received (TZS)</label>
                <input id="receipt_amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>
                @error('amount')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="field">
                <label for="receipt_channel">Account that received it</label>
                <select id="receipt_channel" name="channel" required>
                    <option value="">Choose account</option>
                    @foreach ($paymentAccounts as $key => $account)
                        <option value="{{ $key }}" @selected(old('channel') === $key)>{{ $account['label'] }} · {{ $account['number'] }}</option>
                    @endforeach
                </select>
                @error('channel')<small class="form-error">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="field">
            <label for="receipt_notes">Admin note (optional)</label>
            <input id="receipt_notes" name="notes" value="{{ old('notes') }}" maxlength="500" placeholder="Optional note for your records">
        </div>
        <button class="button" type="submit">Save verified transaction</button>
    </form>

    @if ($receipts->isNotEmpty())
        <div style="margin-top:22px">
            <div class="section-head"><div><h3>Verified references not matched yet</h3><p class="muted" style="margin-top:4px">If a developer later submits an exact match, the plan will activate automatically.</p></div></div>
            <div class="receipt-list">
                @foreach ($receipts as $receipt)
                    <div class="receipt-row">
                        <div><strong>{{ $receipt->transaction_reference }}</strong><br><span class="muted">{{ $receipt->channel }} · TZS {{ number_format($receipt->amount) }} · Logged {{ $receipt->recorded_at->format('d M Y, H:i') }}</span></div>
                        <form method="post" action="{{ route('admin.payment-requests.receipts.destroy', $receipt) }}" onsubmit="return confirm('Remove this unmatched transaction record?')">
                            @csrf @method('DELETE')
                            <button class="button small danger" type="submit">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>

<section class="panel payments-section">
    <div class="section-head">
        <div><h2>Developer payment requests</h2><p class="muted" style="margin-top:5px">If the verified transaction has not been logged yet, check the account and approve manually.</p></div>
        <span class="badge">{{ $payments->total() }} pending</span>
    </div>
    @if ($payments->isEmpty())
        <div class="empty-state"><strong>No payment requests waiting</strong>New transaction references from developers will appear here.</div>
    @else
        <div class="project-list">
            @foreach ($payments as $payment)
                <article class="project-card">
                    <div class="project-title">
                        <div><h3>{{ $payment->user->name }}</h3><p class="muted">{{ $payment->user->email }} · Submitted {{ $payment->created_at->format('d M Y, H:i') }}</p></div>
                        <span class="badge pending">Pending review</span>
                    </div>
                    <div class="notice" style="margin:12px 0">
                        <strong>Amount:</strong> TZS {{ number_format($payment->amount) }}<br>
                        <strong>Plan:</strong> Plus · {{ ucfirst($payment->billing_cycle) }}<br>
                        <strong>Paid to:</strong> {{ config('platform.payment_accounts.'.$payment->payment_method.'.label') }}<br>
                        <strong>Transaction ID:</strong> <code>{{ $payment->provider_transaction_id }}</code>
                    </div>
                    <div class="payment-actions">
                        <form method="post" action="{{ route('admin.payment-requests.approve', $payment) }}" onsubmit="return confirm('Have you verified the amount and transaction in the receiving account?')">
                            @csrf <button class="button" type="submit">Verify &amp; activate Plus</button>
                        </form>
                        <form method="post" action="{{ route('admin.payment-requests.reject', $payment) }}" class="payment-actions" onsubmit="return confirm('Reject this payment reference?')">
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

<section class="panel payments-section">
    <div class="section-head">
        <div><h2>Free / Plus access</h2><p class="muted" style="margin-top:5px">Manual plan changes are managed here. Payment approval also activates Plus automatically.</p></div>
        <span class="badge">{{ $developers->total() }} developers</span>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Developer</th><th>Current plan</th><th>Plus ends</th><th>Change plan</th></tr></thead>
        <tbody>
        @forelse ($developers as $developer)
            <tr>
                <td><strong>{{ $developer->name }}</strong><br><span class="muted">{{ $developer->email }}</span></td>
                <td><span class="badge {{ $developer->isPlus() ? 'active' : 'pending' }}">{{ ucfirst($developer->effectivePlan()) }}</span></td>
                <td>{{ $developer->isPlus() && $developer->plus_expires_at ? $developer->plus_expires_at->format('d M Y') : '—' }}</td>
                <td>
                    <form method="post" action="{{ route('admin.payment-requests.developers.plan', $developer) }}" class="plan-access-form">
                        @csrf @method('PATCH')
                        <select name="plan" aria-label="Plan for {{ $developer->name }}">
                            <option value="free" @selected($developer->effectivePlan() === 'free')>Free</option>
                            <option value="plus" @selected($developer->effectivePlan() === 'plus')>Plus</option>
                        </select>
                        <button class="button small secondary" type="submit">Save</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No developer accounts yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $developers->links() }}</div>
</section>
@endsection
