@extends('layouts.app')

@section('title', 'Payments and plans · TechwareTz Control Center (TCC)')

@section('content')
<style>
    .payments-section{margin-top:22px}.payments-section .section-head{align-items:flex-start}.receipt-list{display:grid;gap:9px}.receipt-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 14px;border:1px solid var(--line);border-radius:10px}.payment-form-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:0 12px}.payment-form-grid .field{margin-bottom:10px}.payment-actions{display:flex;gap:8px;align-items:end;flex-wrap:wrap}.plan-access-form{display:flex;gap:7px;align-items:center}.plan-access-form select{width:auto;min-width:105px}.settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.settings-grid .field{margin:0}.account-settings{display:grid;gap:10px}.account-setting{display:grid;grid-template-columns:1.1fr 1fr auto;gap:12px;align-items:end;padding:12px;border:1px solid var(--line);border-radius:10px}.offer-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 12px}.check-inline{display:flex;align-items:center;gap:8px;margin:0 0 14px}.check-inline input{width:auto}.@media(max-width:760px){.payment-form-grid,.settings-grid,.offer-fields{grid-template-columns:1fr}.receipt-row{align-items:flex-start;flex-direction:column}.plan-access-form{align-items:flex-start;flex-direction:column}.account-setting{grid-template-columns:1fr}}
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
    <div class="section-head"><div><h2>Pricing, offers &amp; payment accounts</h2><p class="muted" style="margin-top:5px">Changes apply to new payment requests. Pending requests keep the amount and account details they were submitted with.</p></div></div>
    <form method="post" action="{{ route('admin.payment-requests.settings.update') }}">
        @csrf @method('PUT')
        <div class="settings-grid">
            <div class="field"><label for="payment_account_name">Account holder name</label><input id="payment_account_name" name="account_name" value="{{ old('account_name', $paymentSettings['account_name']) }}" maxlength="120" required>@error('account_name')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="monthly_price">Plus monthly price (TZS)</label><input id="monthly_price" name="pricing[monthly]" type="number" min="1" step="1" value="{{ old('pricing.monthly', $paymentSettings['pricing']['monthly']) }}" required>@error('pricing.monthly')<small class="form-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="yearly_price">Plus yearly price (TZS)</label><input id="yearly_price" name="pricing[yearly]" type="number" min="1" step="1" value="{{ old('pricing.yearly', $paymentSettings['pricing']['yearly']) }}" required>@error('pricing.yearly')<small class="form-error">{{ $message }}</small>@enderror</div>
        </div>
        <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--line)">
            <div class="section-head"><div><h3>Limited-time offer</h3><p class="muted" style="margin-top:4px">Set a lower monthly or yearly price and optionally choose when it starts and ends.</p></div></div>
            <input type="hidden" name="offer[enabled]" value="0">
            <label class="check-inline"><input type="checkbox" name="offer[enabled]" value="1" @checked(old('offer.enabled', $paymentSettings['offer']['enabled']))> <strong>Offer is active</strong></label>
            <div class="offer-fields">
                <div class="field"><label for="offer_title">Offer name</label><input id="offer_title" name="offer[title]" value="{{ old('offer.title', $paymentSettings['offer']['title']) }}" maxlength="100" placeholder="Example: Launch offer"></div>
                <div class="field"><label for="offer_monthly">Offer monthly price (TZS)</label><input id="offer_monthly" name="offer[monthly]" type="number" min="1" step="1" value="{{ old('offer.monthly', $paymentSettings['offer']['monthly']) }}" placeholder="Leave blank for regular price">@error('offer.monthly')<small class="form-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="offer_yearly">Offer yearly price (TZS)</label><input id="offer_yearly" name="offer[yearly]" type="number" min="1" step="1" value="{{ old('offer.yearly', $paymentSettings['offer']['yearly']) }}" placeholder="Leave blank for regular price">@error('offer.yearly')<small class="form-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="offer_starts_at">Offer starts (optional)</label><input id="offer_starts_at" name="offer[starts_at]" type="datetime-local" value="{{ old('offer.starts_at', $paymentSettings['offer']['starts_at'] ? \Illuminate\Support\Carbon::parse($paymentSettings['offer']['starts_at'])->format('Y-m-d\\TH:i') : '') }}"></div>
                <div class="field"><label for="offer_ends_at">Offer ends (optional)</label><input id="offer_ends_at" name="offer[ends_at]" type="datetime-local" value="{{ old('offer.ends_at', $paymentSettings['offer']['ends_at'] ? \Illuminate\Support\Carbon::parse($paymentSettings['offer']['ends_at'])->format('Y-m-d\\TH:i') : '') }}">@error('offer.ends_at')<small class="form-error">{{ $message }}</small>@enderror</div>
            </div>
        </div>
        <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--line)">
            <div class="section-head"><div><h3>Payment accounts</h3><p class="muted" style="margin-top:4px">Change account names/numbers or turn an option on or off for new payment requests.</p></div></div>
            <div class="account-settings">
                @foreach ($paymentAccounts as $key => $account)
                    <div class="account-setting">
                        <div class="field"><label for="account_label_{{ $key }}">Account / service</label><input id="account_label_{{ $key }}" name="accounts[{{ $key }}][label]" value="{{ old("accounts.$key.label", $account['label']) }}" maxlength="60" required></div>
                        <div class="field"><label for="account_number_{{ $key }}">Account number</label><input id="account_number_{{ $key }}" name="accounts[{{ $key }}][number]" value="{{ old("accounts.$key.number", $account['number']) }}" maxlength="40" required></div>
                        <label class="check-inline"><input type="hidden" name="accounts[{{ $key }}][enabled]" value="0"><input type="checkbox" name="accounts[{{ $key }}][enabled]" value="1" @checked(old("accounts.$key.enabled", $account['enabled'] ?? true))> <span>Available</span></label>
                    </div>
                @endforeach
            </div>
        </div>
        <button class="button" type="submit" style="margin-top:18px">Save payment settings</button>
    </form>
</section>

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
                        <strong>Paid to:</strong> {{ $payment->provider_payload['payment_account_label'] ?? config('platform.payment_accounts.'.$payment->payment_method.'.label') }}<br>
                        <strong>Account number:</strong> {{ $payment->provider_payload['payment_account_number'] ?? config('platform.payment_accounts.'.$payment->payment_method.'.number') }}<br>
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
