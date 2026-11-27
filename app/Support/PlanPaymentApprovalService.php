<?php

namespace App\Support;

use App\Models\AuditEvent;
use App\Models\PaymentReceipt;
use App\Models\PlanPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanPaymentApprovalService
{
    public function approve(PlanPayment $payment, int $adminId, string $ipAddress, bool $automatic = false): PlanPayment
    {
        return DB::transaction(function () use ($payment, $adminId, $ipAddress, $automatic) {
            $lockedPayment = PlanPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->status !== PlanPayment::STATUS_PENDING) {
                throw ValidationException::withMessages(['payment' => 'This payment request has already been reviewed.']);
            }

            $receipt = PaymentReceipt::query()
                ->where('transaction_reference', $lockedPayment->provider_transaction_id)
                ->lockForUpdate()
                ->first();

            if ($automatic) {
                if (! $receipt || $receipt->plan_payment_id !== null
                    || (int) $receipt->amount !== (int) $lockedPayment->amount
                    || $receipt->channel !== $lockedPayment->payment_method) {
                    throw ValidationException::withMessages(['payment' => 'The received transaction does not match this request.']);
                }
            } elseif ($receipt && $receipt->plan_payment_id !== null) {
                throw ValidationException::withMessages(['payment' => 'This transaction has already been used for another payment.']);
            }

            $user = User::query()->whereKey($lockedPayment->user_id)->lockForUpdate()->firstOrFail();
            $start = $user->plus_expires_at && $user->plus_expires_at->isFuture()
                ? $user->plus_expires_at->copy()
                : now();
            $periodEndsAt = $lockedPayment->billing_cycle === 'yearly' ? $start->addYear() : $start->addMonth();

            if (! $receipt) {
                $receipt = PaymentReceipt::create([
                    'transaction_reference' => $lockedPayment->provider_transaction_id,
                    'amount' => $lockedPayment->amount,
                    'channel' => $lockedPayment->payment_method,
                    'recorded_by' => $adminId,
                    'notes' => 'Transaction verified during manual payment review.',
                    'recorded_at' => now(),
                ]);
            }

            $receipt->forceFill(['plan_payment_id' => $lockedPayment->id])->save();
            $lockedPayment->forceFill([
                'status' => PlanPayment::STATUS_PAID,
                'paid_at' => now(),
                'period_ends_at' => $periodEndsAt,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
            ])->save();
            $user->forceFill(['plan' => User::PLAN_PLUS, 'plus_expires_at' => $periodEndsAt])->save();

            AuditEvent::create([
                'actor_id' => $adminId,
                'action' => $automatic ? 'plan.payment_auto_approved' : 'plan.payment_approved',
                'subject_type' => PlanPayment::class,
                'subject_id' => (string) $lockedPayment->id,
                'details' => [
                    'user_id' => $user->id,
                    'amount' => $lockedPayment->amount,
                    'billing_cycle' => $lockedPayment->billing_cycle,
                    'transaction_reference' => $lockedPayment->provider_transaction_id,
                    'period_ends_at' => $periodEndsAt->toIso8601String(),
                ],
                'ip_address' => $ipAddress,
            ]);

            return $lockedPayment->refresh();
        });
    }
}
