@extends('layouts.app')

@section('title', 'Payments and plans · TechwareTz Control Center (TCC)')

@section('content')
<style>
    .payment-tabs{display:flex;gap:8px;overflow-x:auto;padding:4px 2px 12px;margin:0 0 8px;scrollbar-width:thin}
    .payment-tab{flex:0 0 auto;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--muted);padding:10px 14px;font:inherit;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap}
    .payment-tab[aria-selected="true"]{border-color:var(--blue);background:#eff8ff;color:#175cd3;box-shadow:0 0 0 1px #b2ddff}
    .payments-section{margin-top:16px}.payments-section .section-head{align-items:flex-start}
    .receipt-list,.account-settings{display:grid;gap:9px}.receipt-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid var(--line);border-radius:10px}
    .payment-form-grid,.settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 12px}.payment-form-grid .field,.settings-grid .field{margin-bottom:10px}
    .payment-actions{display:flex;gap:8px;align-items:end;flex-wrap:wrap}.plan-access-form{display:flex;gap:7px;align-items:center}.plan-access-form select{width:auto;min-width:105px}
    .account-setting{display:grid;grid-template-columns:1.1fr 1fr auto;gap:12px;align-items:end;padding:12px;border:1px solid var(--line);border-radius:10px}.account-setting .field{margin:0}
    .check-inline{display:flex;align-items:center;gap:8px;margin:0 0 14px}.check-inline input{width:auto}
    @media(max-width:760px){.payment-form-grid,.settings-grid{grid-template-columns:1fr}.receipt-row{align-items:flex-start;flex-direction:column}.plan-access-form{align-items:flex-start;flex-direction:column}.account-setting{grid-template-columns:1fr}}
</style>

<div class="page-heading">
    <div><p class="eyebrow">Admin</p><h1>Payments &amp; plans</h1><p class="muted" style="margin-top:7px">Manage prices, payment accounts and developer requests.</p></div>
    <span class="badge">{{ $payments->total() }} awaiting review</span>
</div>

@if (session('success'))<div class="notice">{{ session('success') }}</div>@endif
@if (session('warning'))<div class="notice warning">{{ session('warning') }}</div>@endif
@if ($errors->any())<div class="notice warning">{{ $errors->first() }}</div>@endif

<div class="payment-tabs" role="tablist" aria-label="Payment administration sections">
    <button class="payment-tab" id="tab-pricing" type="button" role="tab" aria-controls="panel-pricing" data-tab="pricing">Pricing</button>
    <button class="payment-tab" id="tab-offers" type="button" role="tab" aria-controls="panel-offers" data-tab="offers">Offers</button>
    <button class="payment-tab" id="tab-receipts" type="button" role="tab" aria-controls="panel-receipts" data-tab="receipts">Log a verified transaction</button>
    <button class="payment-tab" id="tab-accounts" type="button" role="tab" aria-controls="panel-accounts" data-tab="accounts">Payment accounts</button>
    <button class="payment-tab" id="tab-requests" type="button" role="tab" aria-controls="panel-requests" data-tab="requests">Developer payment requests</button>
</div>

<section class="panel" id="panel-pricing" role="tabpanel" aria-labelledby="tab-pricing" data-tab-panel="pricing" hidden>
    <div class="section-head"><div><h2>Plus plan prices</h2><p class="muted" style="margin-top:5px">These prices apply to new payment requests. Existing requests keep their submitted amount.</p></div></div>
    <form method="post" action="{{ route('admin.payment-requests.settings.update', ['tab' => 'pricing']) }}">
        @csrf @method('PUT')<input type="hidden" name="scope" value="pricing">
        <div class="settings-grid">
            <div class="field"><label for="monthly_price">Monthly price (TZS)</label><input id="monthly_price" name="pricing[monthly]" type="number" min="1" step="1" value="{{ old('pricing.monthly', $paymentSettings['pricing']['monthly']) }}" required>@error('pricing.monthly')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="yearly_price">Yearly price (TZS)</label><input id="yearly_price" name="pricing[yearly]" type="number" min="1" step="1" value="{{ old('pricing.yearly', $paymentSettings['pricing']['yearly']) }}" required>@error('pricing.yearly')<small class="form-error">{{ $message }}</small>@enderror</div>
        </div>
        <button class="button" type="submit">Save prices</button>
    </form>

    <div class="payments-section">
        <div class="section-head"><div><h2>Free / Plus access</h2><p class="muted" style="margin-top:5px">Manual plan changes are managed here. Payment approval also activates Plus automatically.</p></div><span class="badge">{{ $developers->total() }} developers</span></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Developer</th><th>Current plan</th><th>Plus ends</th><th>Change plan</th></tr></thead>
            <tbody>
            @forelse ($developers as $developer)
                <tr>
                    <td><strong>{{ $developer->name }}</strong><br><span class="muted">{{ $developer->email }}</span></td>
                    <td><span class="badge {{ $developer->isPlus() ? 'active' : 'pending' }}">{{ ucfirst($developer->effectivePlan()) }}</span></td>
                    <td>{{ $developer->isPlus() && $developer->plus_expires_at ? $developer->plus_expires_at->format('d M Y') : '—' }}</td>
                    <td><form method="post" action="{{ route('admin.payment-requests.developers.plan', $developer) }}" class="plan-access-form">@csrf @method('PATCH')<select name="plan" aria-label="Plan for {{ $developer->name }}"><option value="free" @selected($developer->effectivePlan() === 'free')>Free</option><option value="plus" @selected($developer->effectivePlan() === 'plus')>Plus</option></select><button class="button small secondary" type="submit">Save</button></form></td>
                </tr>
            @empty<tr><td colspan="4" class="muted">No developer accounts yet.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="pagination">{{ $developers->links() }}</div>
    </div>
</section>

<section class="panel" id="panel-offers" role="tabpanel" aria-labelledby="tab-offers" data-tab-panel="offers" hidden>
    <div class="section-head"><div><h2>Limited-time offer</h2><p class="muted" style="margin-top:5px">Set a lower monthly or yearly price. Dates are optional; without dates, the offer remains active until you turn it off.</p></div></div>
    <form method="post" action="{{ route('admin.payment-requests.settings.update', ['tab' => 'offers']) }}">
        @csrf @method('PUT')<input type="hidden" name="scope" value="offers">
        <input type="hidden" name="offer[enabled]" value="0">
        <label class="check-inline"><input type="checkbox" name="offer[enabled]" value="1" @checked(old('offer.enabled', $paymentSettings['offer']['enabled']))> <strong>Offer is active</strong></label>
        <div class="settings-grid">
            <div class="field"><label for="offer_title">Offer name</label><input id="offer_title" name="offer[title]" value="{{ old('offer.title', $paymentSettings['offer']['title']) }}" maxlength="100" placeholder="Example: Launch offer"></div>
            <div class="field"><label for="offer_monthly">Offer monthly price (TZS)</label><input id="offer_monthly" name="offer[monthly]" type="number" min="1" step="1" value="{{ old('offer.monthly', $paymentSettings['offer']['monthly']) }}" placeholder="Leave blank for regular price">@error('offer.monthly')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="offer_yearly">Offer yearly price (TZS)</label><input id="offer_yearly" name="offer[yearly]" type="number" min="1" step="1" value="{{ old('offer.yearly', $paymentSettings['offer']['yearly']) }}" placeholder="Leave blank for regular price">@error('offer.yearly')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="offer_starts_at">Offer starts (optional)</label><input id="offer_starts_at" name="offer[starts_at]" type="datetime-local" value="{{ old('offer.starts_at', $paymentSettings['offer']['starts_at'] ? \Illuminate\Support\Carbon::parse($paymentSettings['offer']['starts_at'])->format('Y-m-d\\TH:i') : '') }}"></div>
            <div class="field"><label for="offer_ends_at">Offer ends (optional)</label><input id="offer_ends_at" name="offer[ends_at]" type="datetime-local" value="{{ old('offer.ends_at', $paymentSettings['offer']['ends_at'] ? \Illuminate\Support\Carbon::parse($paymentSettings['offer']['ends_at'])->format('Y-m-d\\TH:i') : '') }}">@error('offer.ends_at')<small class="form-error">{{ $message }}</small>@enderror</div>
        </div>
        <button class="button" type="submit">Save offer</button>
    </form>
</section>

<section class="panel" id="panel-receipts" role="tabpanel" aria-labelledby="tab-receipts" data-tab-panel="receipts" hidden>
    <div class="section-head"><div><h2>Log a verified transaction</h2><p class="muted" style="margin-top:5px">First confirm the money arrived in the selected account. A matching developer request will be approved automatically.</p></div></div>
    <form method="post" action="{{ route('admin.payment-requests.receipts.store') }}">
        @csrf
        <div class="payment-form-grid">
            <div class="field"><label for="receipt_reference">Transaction ID / reference</label><input id="receipt_reference" name="transaction_reference" value="{{ old('transaction_reference') }}" maxlength="100" required>@error('transaction_reference')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="receipt_amount">Amount received (TZS)</label><input id="receipt_amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>@error('amount')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="receipt_channel">Account that received it</label><select id="receipt_channel" name="channel" required><option value="">Choose account</option>@foreach ($paymentAccounts as $key => $account)<option value="{{ $key }}" @selected(old('channel') === $key)>{{ $account['label'] }} · {{ $account['number'] }}</option>@endforeach</select>@error('channel')<small class="form-error">{{ $message }}</small>@enderror</div>
        </div>
        <div class="field"><label for="receipt_notes">Admin note (optional)</label><input id="receipt_notes" name="notes" value="{{ old('notes') }}" maxlength="500" placeholder="Optional note for your records"></div>
        <button class="button" type="submit">Save verified transaction</button>
    </form>
    @if ($receipts->isNotEmpty())
        <div class="payments-section"><div class="section-head"><div><h3>Verified references not matched yet</h3><p class="muted" style="margin-top:4px">If a developer later submits an exact match, the plan activates automatically.</p></div></div>
            <div class="receipt-list">@foreach ($receipts as $receipt)<div class="receipt-row"><div><strong>{{ $receipt->transaction_reference }}</strong><br><span class="muted">{{ $paymentAccounts[$receipt->channel]['label'] ?? $receipt->channel }} · TZS {{ number_format($receipt->amount) }} · Logged {{ $receipt->recorded_at->format('d M Y, H:i') }}</span></div><form method="post" action="{{ route('admin.payment-requests.receipts.destroy', $receipt) }}" onsubmit="return confirm('Remove this unmatched transaction record?')">@csrf @method('DELETE')<button class="button small danger" type="submit">Remove</button></form></div>@endforeach</div>
        </div>
    @endif
</section>

<section class="panel" id="panel-accounts" role="tabpanel" aria-labelledby="tab-accounts" data-tab-panel="accounts" hidden>
    <div class="section-head"><div><h2>Payment accounts</h2><p class="muted" style="margin-top:5px">Changes appear on the checkout page for new payment requests. Existing requests keep their original account details.</p></div></div>
    <form method="post" action="{{ route('admin.payment-requests.settings.update', ['tab' => 'accounts']) }}">
        @csrf @method('PUT')<input type="hidden" name="scope" value="accounts">
        <div class="field"><label for="payment_account_name">Account holder name</label><input id="payment_account_name" name="account_name" value="{{ old('account_name', $paymentSettings['account_name']) }}" maxlength="120" required>@error('account_name')<small class="form-error">{{ $message }}</small>@enderror</div>
        <div class="account-settings">
            @foreach ($paymentAccounts as $key => $account)
                <div class="account-setting">
                    <div class="field"><label for="account_label_{{ $key }}">Account / service</label><input id="account_label_{{ $key }}" name="accounts[{{ $key }}][label]" value="{{ old("accounts.$key.label", $account['label']) }}" maxlength="60" required></div>
                    <div class="field"><label for="account_number_{{ $key }}">Account number</label><input id="account_number_{{ $key }}" name="accounts[{{ $key }}][number]" value="{{ old("accounts.$key.number", $account['number']) }}" maxlength="40" required></div>
                    <label class="check-inline"><input type="hidden" name="accounts[{{ $key }}][enabled]" value="0"><input type="checkbox" name="accounts[{{ $key }}][enabled]" value="1" @checked(old("accounts.$key.enabled", $account['enabled'] ?? true))> <span>Available</span></label>
                </div>
            @endforeach
        </div>
        @error('accounts')<small class="form-error">{{ $message }}</small>@enderror
        <button class="button" type="submit" style="margin-top:18px">Save payment accounts</button>
    </form>
</section>

<section class="panel" id="panel-requests" role="tabpanel" aria-labelledby="tab-requests" data-tab-panel="requests" hidden>
    <div class="section-head"><div><h2>Developer payment requests</h2><p class="muted" style="margin-top:5px">If a transaction has not been logged yet, verify it in the receiving account and approve manually.</p></div><span class="badge">{{ $payments->total() }} pending</span></div>
    @if ($payments->isEmpty())
        <div class="empty-state"><strong>No payment requests waiting</strong>New transaction references from developers will appear here.</div>
    @else
        <div class="project-list">@foreach ($payments as $payment)
            <article class="project-card">
                <div class="project-title"><div><h3>{{ $payment->user->name }}</h3><p class="muted">{{ $payment->user->email }} · Submitted {{ $payment->created_at->format('d M Y, H:i') }}</p></div><span class="badge pending">Pending review</span></div>
                <div class="notice" style="margin:12px 0"><strong>Amount:</strong> TZS {{ number_format($payment->amount) }}<br><strong>Plan:</strong> Plus · {{ ucfirst($payment->billing_cycle) }}<br><strong>Paid to:</strong> {{ $payment->provider_payload['payment_account_label'] ?? $paymentAccounts[$payment->payment_method]['label'] ?? $payment->provider_channel }}<br><strong>Account number:</strong> {{ $payment->provider_payload['payment_account_number'] ?? $paymentAccounts[$payment->payment_method]['number'] ?? '' }}<br><strong>Transaction ID:</strong> <code>{{ $payment->provider_transaction_id }}</code></div>
                <div class="payment-actions">
                    <form method="post" action="{{ route('admin.payment-requests.approve', $payment) }}" onsubmit="return confirm('Have you verified the amount and transaction in the receiving account?')">@csrf <button class="button" type="submit">Verify &amp; activate Plus</button></form>
                    <form method="post" action="{{ route('admin.payment-requests.reject', $payment) }}" class="payment-actions" onsubmit="return confirm('Reject this payment reference?')">@csrf<div class="field" style="margin:0;min-width:min(280px,70vw)"><label for="review_note_{{ $payment->id }}">Note for developer (optional)</label><input id="review_note_{{ $payment->id }}" name="review_note" maxlength="500" placeholder="For example: transaction not found"></div><button class="button small danger" type="submit">Reject</button></form>
                </div>
            </article>
        @endforeach</div>
        <div class="pagination">{{ $payments->links() }}</div>
    @endif
</section>

<script>
(() => {
    const tabs = [...document.querySelectorAll('[role="tab"][data-tab]')];
    const panels = [...document.querySelectorAll('[data-tab-panel]')];
    const activate = (name, updateUrl = false) => {
        if (!tabs.some(tab => tab.dataset.tab === name)) name = 'pricing';
        tabs.forEach(tab => {
            const selected = tab.dataset.tab === name;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
        });
        panels.forEach(panel => { panel.hidden = panel.dataset.tabPanel !== name; });
        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', name);
            history.replaceState({}, '', url);
        }
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab.dataset.tab, true));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length;
            tabs[next].focus();
            activate(tabs[next].dataset.tab, true);
        });
    });
    activate(new URLSearchParams(window.location.search).get('tab') || 'pricing');
})();
</script>
@endsection
