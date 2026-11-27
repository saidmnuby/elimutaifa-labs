<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\PlanPayment;
use App\Models\User;
use App\Support\SystemMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanPaymentController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE, 403);

        $pendingPayment = PlanPayment::query()
            ->where('user_id', $user->id)
            ->where('status', PlanPayment::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingPayment) {
            return redirect()->route('plans.payment.show', $pendingPayment);
        }

        return view('plans.checkout', [
            'user' => $user,
            'monthlyPrice' => config('platform.plus_pricing.monthly'),
            'yearlyPrice' => config('platform.plus_pricing.yearly'),
            'paymentAccounts' => config('platform.payment_accounts'),
            'accountName' => config('platform.payment_account_name'),
            'supportPhone' => config('platform.payment_support_phone'),
            'supportEmails' => config('platform.payment_support_emails'),
        ]);
    }

    public function store(Request $request, SystemMailer $mailer): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE, 403);

        $validated = $request->validate([
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'payment_method' => ['required', Rule::in(array_keys(config('platform.payment_accounts', [])))],
            'transaction_reference' => [
                'required', 'string', 'max:100',
                Rule::unique('plan_payments', 'provider_transaction_id'),
            ],
            'confirm_payment' => ['accepted'],
        ]);

        if (PlanPayment::query()->where('user_id', $user->id)->where('status', PlanPayment::STATUS_PENDING)->exists()) {
            return redirect()->route('plans.checkout')->withErrors([
                'payment' => 'You already have a payment waiting for review. Please wait for the current request to be reviewed.',
            ]);
        }

        $amount = (int) config('platform.plus_pricing.'.$validated['billing_cycle']);
        if ($amount < 1) {
            return back()->withInput()->withErrors(['payment' => 'The selected Plus price is not configured.']);
        }

        $transactionReference = trim($validated['transaction_reference']);
        $account = config('platform.payment_accounts.'.$validated['payment_method']);
        $payment = PlanPayment::create([
            'user_id' => $user->id,
            'order_id' => 'TCC'.$user->id.strtoupper(bin2hex(random_bytes(8))),
            'billing_cycle' => $validated['billing_cycle'],
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => $validated['payment_method'],
            'status' => PlanPayment::STATUS_PENDING,
            'provider_transaction_id' => $transactionReference,
            'provider_channel' => $account['label'],
            'provider_payload' => ['verification_type' => 'manual'],
        ]);

        $mailer->admins('New Plus payment reference · TechwareTz Control Center', [
            'eyebrow' => 'Payment review required',
            'title' => 'A developer submitted a payment reference',
            'intro' => 'Check the incoming payment in the receiving account before approving Plus access.',
            'details' => [
                'Developer' => $user->name,
                'Email' => $user->email,
                'Plan' => 'Plus · '.ucfirst($payment->billing_cycle),
                'Amount' => 'TZS '.number_format($payment->amount),
                'Paid to' => $account['label'].' · '.$account['number'],
                'Transaction reference' => $transactionReference,
            ],
            'button_label' => 'Review payment requests',
            'button_url' => route('admin.payment-requests'),
        ]);

        AuditEvent::create([
            'actor_id' => $user->id,
            'action' => 'plan.payment_submitted',
            'subject_type' => PlanPayment::class,
            'subject_id' => (string) $payment->id,
            'details' => [
                'amount' => $payment->amount,
                'billing_cycle' => $payment->billing_cycle,
                'channel' => $account['label'],
                'transaction_reference' => $transactionReference,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('plans.payment.show', $payment)
            ->with('success', 'Your payment reference was submitted for review.');
    }

    public function show(Request $request, PlanPayment $payment): View
    {
        abort_unless($request->user()->id === $payment->user_id, 404);

        return view('plans.payment-result', [
            'payment' => $payment,
            'accountName' => config('platform.payment_account_name'),
            'paymentAccount' => config('platform.payment_accounts.'.$payment->payment_method),
            'supportPhone' => config('platform.payment_support_phone'),
            'supportEmails' => config('platform.payment_support_emails'),
        ]);
    }
}
