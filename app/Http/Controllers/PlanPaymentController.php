<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\PlanPayment;
use App\Models\PaymentReceipt;
use App\Models\User;
use App\Support\SystemMailer;
use App\Support\PlanPaymentApprovalService;
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

    public function store(Request $request, SystemMailer $mailer, PlanPaymentApprovalService $approval): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE, 403);
        $request->merge(['transaction_reference' => trim((string) $request->input('transaction_reference'))]);

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

        $receipt = PaymentReceipt::query()
            ->where('transaction_reference', $transactionReference)
            ->whereNull('plan_payment_id')
            ->first();
        if ($receipt && (int) $receipt->amount === $amount && $receipt->channel === $validated['payment_method']) {
            $payment = $approval->approve($payment, $receipt->recorded_by, (string) $request->ip(), automatic: true);
            $mailer->to($user->email, 'Your Plus plan is active · TechwareTz Control Center', [
                'eyebrow' => 'Payment confirmed',
                'title' => 'Your Plus plan is active',
                'intro' => 'Your transaction reference matched a payment already verified by the admin.',
                'details' => [
                    'Amount' => 'TZS '.number_format($payment->amount),
                    'Billing cycle' => ucfirst($payment->billing_cycle),
                    'Active until' => $payment->period_ends_at?->format('d M Y'),
                    'Transaction reference' => $payment->provider_transaction_id,
                ],
                'button_label' => 'View plans',
                'button_url' => route('plans.index'),
            ]);

            return redirect()->route('plans.payment.show', $payment)
                ->with('success', 'Your verified transaction was matched and Plus is active.');
        }

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
                'Receipt match' => $receipt ? 'Reference exists but account or amount differs; manual review required.' : 'No verified receipt has been logged yet.',
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
