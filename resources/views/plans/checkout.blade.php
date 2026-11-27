@extends('layouts.app')

@section('title', 'Upgrade to Plus · TechwareTz Control Center')

@section('content')
<style>
    .checkout-wrap{max-width:760px;margin:0 auto}.checkout-panel{padding:26px}.payment-accounts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:12px 0 20px}.payment-account{display:flex;gap:11px;align-items:flex-start;padding:14px;border:1px solid var(--line);border-radius:11px;cursor:pointer}.payment-account:has(input:checked){border-color:var(--blue);background:#eff8ff;box-shadow:0 0 0 2px #b2ddff}.payment-account input{margin:3px 0 0}.payment-account strong,.payment-account small{display:block}.payment-account small{margin-top:4px;color:var(--muted)}.payee-box{padding:14px 16px;border-radius:11px;background:#f6f9fc;border:1px solid var(--line);margin:16px 0}.payment-help{font-size:13px;color:var(--muted);line-height:1.6}.checkout-actions{display:flex;gap:10px;margin-top:20px}.checkout-actions .button{flex:1}@media(max-width:600px){.payment-accounts{grid-template-columns:1fr}.checkout-panel{padding:20px}}
</style>

<div class="checkout-wrap">
    <div class="page-heading">
        <div><p class="eyebrow">Plus subscription</p><h1>Pay for Plus</h1><p class="muted" style="margin-top:8px">Pay to one of the accounts below, then submit the transaction reference for review.</p></div>
        <a href="{{ route('plans.index') }}">Back to plans</a>
    </div>

    <section class="panel checkout-panel">
        @if ($errors->any())
            <div class="notice warning" style="margin-bottom:18px">{{ $errors->first() }}</div>
        @endif

        <div class="payee-box"><span class="muted">Account name</span><br><strong>{{ $accountName }}</strong></div>

        <form method="post" action="{{ route('plans.checkout.store') }}">
            @csrf
            <div class="field">
                <label for="billing_cycle">Billing period</label>
                @if ($pricing['offer_active'])<p class="badge active" style="display:inline-flex;margin:0 0 8px">{{ $pricing['offer_title'] }}</p>@endif
                <select id="billing_cycle" name="billing_cycle" required>
                    <option value="monthly" @selected(old('billing_cycle', 'monthly') === 'monthly')>Monthly · TZS {{ number_format($monthlyPrice) }}{{ $monthlyPrice < $pricing['base_monthly'] ? ' (was TZS '.number_format($pricing['base_monthly']).')' : '' }}</option>
                    <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly · TZS {{ number_format($yearlyPrice) }}{{ $yearlyPrice < $pricing['base_yearly'] ? ' (was TZS '.number_format($pricing['base_yearly']).')' : '' }}</option>
                </select>
            </div>

            <fieldset style="border:0;padding:0;margin:18px 0 0">
                <legend style="font-weight:700;margin-bottom:4px">Choose the account you paid</legend>
                <p class="payment-help" style="margin:0">You can pay from any bank or mobile network to one of these accounts.</p>
                <div class="payment-accounts">
                    @foreach ($paymentAccounts as $key => $account)
                        <label class="payment-account">
                            <input type="radio" name="payment_method" value="{{ $key }}" @checked(old('payment_method', 'crdb') === $key) required>
                            <span><strong>{{ $account['label'] }}</strong><small>{{ $account['type'] }} · {{ $account['number'] }}</small></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="field">
                <label for="transaction_reference">Transaction ID / reference number</label>
                <input id="transaction_reference" name="transaction_reference" value="{{ old('transaction_reference') }}" maxlength="100" autocomplete="off" required>
                <small class="muted">Enter the reference shown in the successful payment message.</small>
            </div>

            <label class="check-option" style="margin:14px 0">
                <input type="checkbox" name="confirm_payment" value="1" @checked(old('confirm_payment')) required>
                <span><strong>I have completed the payment</strong><small>I understand the admin will verify this transaction before Plus is activated.</small></span>
            </label>

            <div class="notice" style="margin-top:16px">
                <strong>What happens next?</strong>
                <p class="payment-help" style="margin:6px 0 0">We review payment references manually, usually within one hour. If your Plus plan is not active after one hour, call <a href="tel:{{ $supportPhone }}">{{ $supportPhone }}</a> or email <a href="mailto:{{ $supportEmails[0] }}">{{ $supportEmails[0] }}</a> / <a href="mailto:{{ $supportEmails[1] }}">{{ $supportEmails[1] }}</a>.</p>
            </div>

            <div class="checkout-actions">
                <a class="button secondary" href="{{ route('plans.index') }}">Cancel</a>
                <button class="button" type="submit">Submit payment reference</button>
            </div>
            <p class="payment-help" style="margin-top:14px">Never share your mobile money PIN, bank password, or one-time code.</p>
        </form>
    </section>
</div>
@endsection
