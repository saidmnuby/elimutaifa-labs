<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\PlanPayment;
use App\Models\User;
use App\Support\SystemMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPlanPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-requests', [
            'payments' => PlanPayment::with('user')->where('status', PlanPayment::STATUS_PENDING)->latest()->paginate(20),
        ]);
    }

    public function approve(Request $request, PlanPayment $payment, SystemMailer $mailer): RedirectResponse
    {
        $wasApproved = DB::transaction(function () use ($request, $payment) {
            $lockedPayment = PlanPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->status !== PlanPayment::STATUS_PENDING) {
                throw ValidationException::withMessages(['payment' => 'This payment request has already been reviewed.']);
            }

            if (! $lockedPayment->provider_transaction_id) {
                throw ValidationException::withMessages(['payment' => 'This request has no transaction reference.']);
            }

            $user = User::query()->whereKey($lockedPayment->user_id)->lockForUpdate()->firstOrFail();
            $start = $user->plus_expires_at && $user->plus_expires_at->isFuture()
                ? $user->plus_expires_at->copy()
                : now();
            $periodEndsAt = $lockedPayment->billing_cycle === 'yearly' ? $start->addYear() : $start->addMonth();

            $lockedPayment->forceFill([
                'status' => PlanPayment::STATUS_PAID,
                'paid_at' => now(),
                'period_ends_at' => $periodEndsAt,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ])->save();
            $user->forceFill(['plan' => User::PLAN_PLUS, 'plus_expires_at' => $periodEndsAt])->save();

            AuditEvent::create([
                'actor_id' => $request->user()->id,
                'action' => 'plan.payment_approved',
                'subject_type' => PlanPayment::class,
                'subject_id' => (string) $lockedPayment->id,
                'details' => [
                    'user_id' => $user->id,
                    'amount' => $lockedPayment->amount,
                    'billing_cycle' => $lockedPayment->billing_cycle,
                    'transaction_reference' => $lockedPayment->provider_transaction_id,
                    'period_ends_at' => $periodEndsAt->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
            ]);

            return true;
        });

        if ($wasApproved) {
            $payment->refresh();
            $mailer->to($payment->user->email, 'Your Plus plan is active · TechwareTz Control Center', [
                'eyebrow' => 'Payment confirmed',
                'title' => 'Your Plus plan is active',
                'intro' => 'We verified your payment and activated Plus on your account.',
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
}
