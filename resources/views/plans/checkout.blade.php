@extends('layouts.app')

@section('title', 'Upgrade to Plus · TechwareTz Control Center')

@section('content')
<style>
    .checkout-wrap{max-width:660px;margin:0 auto}.checkout-panel{padding:26px}.checkout-options{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0}.checkout-option{display:block;padding:15px;border:1px solid var(--line);border-radius:10px;cursor:pointer}.checkout-option:has(input:checked){border-color:var(--blue);background:#eff8ff;box-shadow:0 0 0 2px #b2ddff}.checkout-option input{margin:0 8px 0 0}.checkout-option strong,.checkout-option small{display:block}.checkout-option small{margin:6px 0 0 24px;color:var(--muted)}.checkout-price{font-size:13px;color:var(--muted);margin-top:4px}.billing-fields{padding:16px;border:1px solid var(--line);border-radius:10px;background:#fafbfc}.billing-fields[hidden]{display:none}.billing-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}.checkout-actions{display:flex;gap:10px;margin-top:20px}.checkout-actions .button{flex:1}.secure-note{font-size:12px;color:var(--muted);margin-top:14px;line-height:1.55}@media(max-width:600px){.checkout-options,.billing-grid{grid-template-columns:1fr}.checkout-panel{padding:20px}}
</style>

<div class="checkout-wrap">
    <div class="page-heading">
        <div><p class="eyebrow">Plus subscription</p><h1>Choose your payment</h1><p class="muted" style="margin-top:8px">Mobile money and card payments are securely handled by Selcom.</p></div>
        <a href="{{ route('plans.index') }}">Back to plans</a>
    </div>

    <section class="panel checkout-panel">
        @if (! $configured)
            <div class="notice warning"><strong>Payments are being set up.</strong><br>Checkout will open after the platform admin adds the Selcom merchant settings.</div>
        @endif

        <form method="post" action="{{ route('plans.checkout.store') }}" id="plus-checkout-form">
            @csrf
            <div class="field">
                <label for="billing_cycle">Billing period</label>
                <select id="billing_cycle" name="billing_cycle" required>
                    <option value="monthly" @selected(old('billing_cycle', 'monthly') === 'monthly')>Monthly — TZS {{ number_format($monthlyPrice) }}</option>
                    <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly — TZS {{ number_format($yearlyPrice) }}</option>
                </select>
            </div>
            <div class="field">
                <label for="phone">Mobile number</label>
                <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="07XX XXX XXX" value="{{ old('phone') }}" required>
                <small class="muted">Use a Tanzanian number. Selcom will send a payment prompt when you choose mobile money.</small>
            </div>

            <fieldset style="border:0;padding:0;margin:0">
                <legend style="font-weight:700;margin-bottom:8px">Payment method</legend>
                <div class="checkout-options">
                    <label class="checkout-option"><input type="radio" name="payment_method" value="mobile_money" @checked(old('payment_method', 'mobile_money') === 'mobile_money') required><strong>Mobile money</strong><small>Approve the payment prompt on your phone.</small></label>
                    <label class="checkout-option"><input type="radio" name="payment_method" value="card" @checked(old('payment_method') === 'card') required><strong>Debit or credit card</strong><small>Pay through Selcom's secure card page.</small></label>
                </div>
            </fieldset>

            <div class="billing-fields" id="card-billing-fields" @if(old('payment_method') !== 'card') hidden @endif>
                <h2 style="margin-bottom:14px">Card billing details</h2>
                <div class="billing-grid">
                    <div class="field"><label for="billing_firstname">First name</label><input id="billing_firstname" name="billing_firstname" value="{{ old('billing_firstname') }}" autocomplete="given-name"></div>
                    <div class="field"><label for="billing_lastname">Last name</label><input id="billing_lastname" name="billing_lastname" value="{{ old('billing_lastname') }}" autocomplete="family-name"></div>
                    <div class="field" style="grid-column:1/-1"><label for="billing_address">Billing address</label><input id="billing_address" name="billing_address" value="{{ old('billing_address') }}" autocomplete="street-address"></div>
                    <div class="field"><label for="billing_city">City</label><input id="billing_city" name="billing_city" value="{{ old('billing_city') }}" autocomplete="address-level2"></div>
                    <div class="field"><label for="billing_region">Region</label><input id="billing_region" name="billing_region" value="{{ old('billing_region') }}" autocomplete="address-level1"></div>
                    <div class="field"><label for="billing_postcode">Postcode or P.O. Box</label><input id="billing_postcode" name="billing_postcode" value="{{ old('billing_postcode') }}" autocomplete="postal-code"></div>
                </div>
            </div>

            <p class="checkout-price">Plus renews manually at the end of the selected period. No saved card or automatic charge.</p>
            <div class="checkout-actions">
                <a class="button secondary" href="{{ route('plans.index') }}">Cancel</a>
                <button class="button" type="submit" @disabled(! $configured)>Continue to Selcom</button>
            </div>
            <p class="secure-note">Your Plus access is enabled only after Selcom confirms the exact payment amount. Do not share your mobile money PIN or card details with anyone.</p>
        </form>
    </section>
</div>

<script>
    (() => {
        const wrap = document.getElementById('card-billing-fields');
        const inputs = [...wrap.querySelectorAll('input')];
        const update = () => {
            const isCard = document.querySelector('input[name="payment_method"]:checked')?.value === 'card';
            wrap.hidden = !isCard;
            inputs.forEach(input => {
                input.required = isCard;
                input.disabled = !isCard;
            });
        };
        document.querySelectorAll('input[name="payment_method"]').forEach(input => input.addEventListener('change', update));
        update();
    })();
</script>
@endsection
