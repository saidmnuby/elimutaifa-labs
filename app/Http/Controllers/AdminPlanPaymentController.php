<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\PaymentReceipt;
use App\Models\PlanPayment;
use App\Models\User;
use App\Support\PlanPaymentApprovalService;
use App\Support\SystemMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPlanPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-requests', [
            'payments' => PlanPayment::with('user')->where('status', PlanPayment::STATUS_PENDING)->latest()->paginate(20, ['*'], 'pending_page'),
            'developers' => User::where('role', User::ROLE_DEVELOPER)->latest()->paginate(20, ['*'], 'developers_page'),
            'receipts' => PaymentReceipt::with('recorder')->whereNull('plan_payment_id')->latest('recorded_at')->limit(20)->get(),
            'paymentAccounts' => config('platform.payment_accounts'),
        ]);
    }

    public function storeReceipt(Request $request, PlanPaymentApprovalService $approval, SystemMailer $mailer): RedirectResponse
    {
        $request->merge(['transaction_reference' => trim((string) $request->input('transaction_reference'))]);
        $validated = $request->validate([
            'transaction_reference' => ['required', 'string', 'max:100', Rule::unique('payment_receipts', 'transaction_reference')],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'channel' => ['required', Rule::in(array_keys(config('platform.payment_accounts', [])))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $reference = trim($validated['transaction_reference']);
        $receipt = PaymentReceipt::create([
            'transaction_reference' => $reference,
            'amount' => $validated['amount'],
            'channel' => $validated['channel'],
            'recorded_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
            'recorded_at' => now(),
        ]);
        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'action' => 'payment.receipt_recorded',
            'subject_type' => PaymentReceipt::class,
            'subject_id' => (string) $receipt->id,
            'details' => [
                'transaction_reference' => $receipt->transaction_reference,
                'amount' => $receipt->amount,
                'channel' => $receipt->channel,
            ],
            'ip_address' => $request->ip(),
        ]);

        $payment = PlanPayment::query()
            ->where('provider_transaction_id', $reference)
            ->where('status', PlanPayment::STATUS_PENDING)
            ->first();

        if (! $payment) {
            return redirect()->route('admin.payment-requests')
                ->with('success', 'Verified transaction saved. A matching payment request will be approved automatically when submitted.');
        }

        if ((int) $payment->amount !== (int) $receipt->amount || $payment->payment_method !== $receipt->channel) {
            return redirect()->route('admin.payment-requests')->with('warning',
                'Transaction saved, but the pending request has a different amount or destination. Review it manually before approval.');
        }

        $payment = $approval->approve($payment, $request->user()->id, (string) $request->ip(), automatic: true);
        $this->notifyActivatedDeveloper($payment, $mailer, true);

        return redirect()->route('admin.payment-requests')->with('success', 'Transaction matched. Plus was activated automatically.');
    }

    public function deleteReceipt(Request $request, PaymentReceipt $receipt): RedirectResponse
    {
        if ($receipt->plan_payment_id !== null) {
            return back()->withErrors(['receipt' => 'A transaction linked to a reviewed payment cannot be deleted.']);
        }

        $reference = $receipt->transaction_reference;
        $receipt->delete();
        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'action' => 'payment.receipt_removed',
            'subject_type' => PaymentReceipt::class,
            'subject_id' => (string) $receipt->id,
            'details' => ['transaction_reference' => $reference],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.payment-requests')->with('success', 'Unmatched transaction record removed.');
    }

    public function approve(Request $request, PlanPayment $payment, PlanPaymentApprovalService $approval, SystemMailer $mailer): RedirectResponse
    {
        $payment = $approval->approve($payment, $request->user()->id, (string) $request->ip());
        $this->notifyActivatedDeveloper($payment, $mailer);

        return redirect()->route('admin.payment-requests')->with('success', 'Payment verified and Plus activated.');
    }

    public function reject(Request $request, PlanPayment $payment, SystemMailer $mailer): RedirectResponse
    {
        $validated = $request->validate(['review_note' => ['nullable', 'string', 'max:500']]);

        DB::transaction(function () use ($request, $payment, $validated) {
            $lockedPayment = PlanPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->status !== PlanPayment::STATUS_PENDING) {
                throw ValidationException::withMessages(['payment' => 'This payment request has already been reviewed.']);
            }

            $lockedPayment->forceFill([
                'status' => PlanPayment::STATUS_FAILED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $validated['review_note'] ?? null,
            ])->save();

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'action' => 'plan.payment_rejected',
                'subject_type' => PlanPayment::class,
                'subject_id' => (string) $lockedPayment->id,
                'details' => [
                    'user_id' => $lockedPayment->user_id,
                    'transaction_reference' => $lockedPayment->provider_transaction_id,
                    'review_note' => $validated['review_note'] ?? null,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        $payment->refresh();
        $mailer->to($payment->user->email, 'Payment reference needs attention · TechwareTz Control Center', [
            'eyebrow' => 'Payment not verified',
            'title' => 'We could not verify your payment',
            'intro' => 'Please review the note below and contact support if you believe this is a mistake.',
            'details' => array_filter([
                'Transaction reference' => $payment->provider_transaction_id,
                'Admin note' => $payment->review_note,
                'Support phone' => config('platform.payment_support_phone'),
                'Support email' => implode(' or ', config('platform.payment_support_emails', [])),
            ]),
            'button_label' => 'View plans',
            'button_url' => route('plans.index'),
        ]);

        return redirect()->route('admin.payment-requests')->with('success', 'Payment request rejected. The developer was notified.');
    }

    public function updatePlan(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_DEVELOPER, 404);
        $validated = $request->validate(['plan' => ['required', Rule::in([User::PLAN_FREE, User::PLAN_PLUS])]]);
        $oldPlan = $user->effectivePlan();
        $expiresAt = $validated['plan'] === User::PLAN_PLUS && $user->isPlus()
            ? $user->plus_expires_at
            : null;
        $user->forceFill(['plan' => $validated['plan'], 'plus_expires_at' => $expiresAt])->save();

        AuditEvent::create([
            'actor_id' => $request->user()->id,
            'action' => 'developer.plan_changed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'details' => ['from' => $oldPlan, 'to' => $user->effectivePlan()],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.payment-requests')->with('success', "The plan for {$user->email} was changed to ".ucfirst($user->effectivePlan()).'.');
    }

    private function notifyActivatedDeveloper(PlanPayment $payment, SystemMailer $mailer, bool $automatic = false): void
    {
        $payment->refresh();
        $mailer->to($payment->user->email, 'Your Plus plan is active · TechwareTz Control Center', [
            'eyebrow' => 'Payment confirmed',
            'title' => 'Your Plus plan is active',
            'intro' => $automatic
                ? 'Your transaction matched a payment already verified by the admin.'
                : 'We verified your payment and activated Plus on your account.',
            'details' => [
                'Amount' => 'TZS '.number_format($payment->amount),
                'Billing cycle' => ucfirst($payment->billing_cycle),
                'Active until' => $payment->period_ends_at?->format('d M Y'),
                'Transaction reference' => $payment->provider_transaction_id,
            ],
            'button_label' => 'View plans',
            'button_url' => route('plans.index'),
        ]);
    }
}
