<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\PlanPayment;
use App\Models\User;
use App\Support\SelcomGateway;
use App\Support\SystemMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PlanPaymentController extends Controller
{
    public function create(Request $request, SelcomGateway $selcom): View|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE, 403);

        return view('plans.checkout', [
            'user' => $user,
            'configured' => $selcom->configured(),
            'monthlyPrice' => config('services.selcom.monthly_price'),
            'yearlyPrice' => config('services.selcom.yearly_price'),
        ]);
    }

    public function store(Request $request, SelcomGateway $selcom): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE, 403);
        if (! $selcom->configured()) {
            return back()->withInput()->withErrors(['payment' => 'Online payments are being configured. Please try again later.']);
        }

        $validated = $request->validate([
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'payment_method' => ['required', Rule::in(['mobile_money', 'card'])],
            'phone' => ['required', 'string', 'regex:/\A(?:\+?255|0)?[67][0-9]{8}\z/'],
            'billing_firstname' => ['required_if:payment_method,card', 'nullable', 'string', 'max:60'],
            'billing_lastname' => ['required_if:payment_method,card', 'nullable', 'string', 'max:60'],
            'billing_address' => ['required_if:payment_method,card', 'nullable', 'string', 'max:120'],
            'billing_city' => ['required_if:payment_method,card', 'nullable', 'string', 'max:60'],
            'billing_region' => ['required_if:payment_method,card', 'nullable', 'string', 'max:60'],
            'billing_postcode' => ['required_if:payment_method,card', 'nullable', 'string', 'max:20'],
        ]);

        $amount = (int) config('services.selcom.'.($validated['billing_cycle'] === 'monthly' ? 'monthly_price' : 'yearly_price'));
        if ($amount < 1) {
            return back()->withInput()->withErrors(['payment' => 'The selected plan price is not configured.']);
        }

        $phone = preg_replace('/\D+/', '', $validated['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '255'.substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            $phone = '255'.$phone;
        }

        $payment = PlanPayment::create([
            'user_id' => $user->id,
            'order_id' => 'TCC'.$user->id.strtoupper(bin2hex(random_bytes(8))),
            'billing_cycle' => $validated['billing_cycle'],
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => $validated['payment_method'],
            'status' => PlanPayment::STATUS_PENDING,
            'buyer_phone' => $phone,
        ]);

        $successUrl = route('plans.payment.show', ['payment' => $payment->id]);
        $cancelUrl = route('plans.payment.show', ['payment' => $payment->id, 'state' => 'cancelled']);
        $payload = [
            'vendor' => (string) config('services.selcom.vendor_id'),
            'order_id' => $payment->order_id,
            'buyer_email' => $user->email,
            'buyer_name' => $user->name,
            'buyer_userid' => (string) $user->id,
            'buyer_phone' => $phone,
            'amount' => (string) $amount,
            'currency' => 'TZS',
            'payment_methods' => $validated['payment_method'] === 'card' ? 'CARD' : 'MOBILEMONEYPULL',
            'redirect_url' => base64_encode($successUrl),
            'cancel_url' => base64_encode($cancelUrl),
            'webhook' => base64_encode(route('payments.selcom.webhook')),
            'buyer_remarks' => 'TechwareTz Control Center Plus subscription',
            'merchant_remarks' => ucfirst($validated['billing_cycle']).' Plus plan for developer '.$user->id,
            'no_of_items' => '1',
            'expiry' => '60',
        ];

        if ($validated['payment_method'] === 'card') {
            $payload += [
                'billing.firstname' => $validated['billing_firstname'],
                'billing.lastname' => $validated['billing_lastname'],
                'billing.address_1' => $validated['billing_address'],
                'billing.city' => $validated['billing_city'],
                'billing.state_or_region' => $validated['billing_region'],
                'billing.postcode_or_pobox' => $validated['billing_postcode'],
                'billing.country' => 'TZ',
                'billing.phone' => $phone,
            ];
        }

        try {
            $checkout = $selcom->createOrder($payload);
            $payment->forceFill([
                'provider_reference' => data_get($checkout, 'response.reference'),
                'provider_payload' => ['order_created' => true],
            ])->save();

            return redirect()->away($checkout['url']);
        } catch (Throwable $exception) {
            report($exception);
            $payment->forceFill(['status' => PlanPayment::STATUS_FAILED])->save();

            return back()->withInput()->withErrors(['payment' => 'We could not start the payment. Check your details and try again.']);
        }
    }

    public function show(Request $request, PlanPayment $payment, SelcomGateway $selcom, SystemMailer $mailer): View|RedirectResponse
    {
        abort_unless($request->user()->id === $payment->user_id, 404);
        if ($payment->status === PlanPayment::STATUS_PENDING && $selcom->configured()) {
            try {
                $statusResponse = $selcom->orderStatus($payment->order_id);
                $providerOrder = data_get($statusResponse, 'data.0');
                if (is_array($providerOrder) && ($providerOrder['payment_status'] ?? null) === 'COMPLETED') {
                    $this->completePayment($payment, $providerOrder, $request, $mailer);
                    $payment->refresh();
                } elseif (is_array($providerOrder) && in_array($providerOrder['payment_status'] ?? '', ['CANCELLED', 'USERCANCELLED', 'REJECTED'], true)) {
                    $payment->forceFill(['status' => PlanPayment::STATUS_FAILED])->save();
                    $payment->refresh();
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return view('plans.payment-result', ['payment' => $payment, 'cancelled' => $request->query('state') === 'cancelled']);
    }

    public function webhook(Request $request, SelcomGateway $selcom, SystemMailer $mailer)
    {
        $payload = $request->json()->all();
        $headers = [
            'authorization' => $request->header('Authorization', ''),
            'digest-method' => $request->header('Digest-Method', ''),
            'digest' => $request->header('Digest', ''),
            'timestamp' => $request->header('Timestamp', ''),
            'signed-fields' => $request->header('Signed-Fields', ''),
        ];
        if (! $selcom->validWebhook($payload, $headers)) {
            return response()->json(['message' => 'Invalid payment callback signature.'], 401);
        }

        $payment = PlanPayment::query()->where('order_id', (string) ($payload['order_id'] ?? ''))->first();
        if (! $payment) {
            return response()->json(['message' => 'Payment order not found.'], 404);
        }

        if (($payload['result'] ?? null) === 'SUCCESS'
            && ($payload['resultcode'] ?? null) === '000'
            && ($payload['payment_status'] ?? null) === 'COMPLETED') {
            if (! isset($payload['amount']) || (float) $payload['amount'] !== (float) $payment->amount) {
                return response()->json(['message' => 'Payment amount does not match.'], 422);
            }
            $this->completePayment($payment, $payload, $request, $mailer);
        }

        return response()->json(['received' => true]);
    }

    private function completePayment(PlanPayment $payment, array $providerData, Request $request, SystemMailer $mailer): void
    {
        $completedNow = DB::transaction(function () use ($payment, $providerData, $request) {
            $lockedPayment = PlanPayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->status === PlanPayment::STATUS_PAID) {
                return false;
            }
            if (! isset($providerData['amount']) || (float) $providerData['amount'] !== (float) $lockedPayment->amount) {
                throw new \RuntimeException('Selcom payment amount did not match this plan order.');
            }

            $user = User::query()->whereKey($lockedPayment->user_id)->lockForUpdate()->firstOrFail();
            $start = $user->plus_expires_at && $user->plus_expires_at->isFuture()
                ? $user->plus_expires_at->copy()
                : now();
            $periodEndsAt = $lockedPayment->billing_cycle === 'yearly' ? $start->addYear() : $start->addMonth();

            $lockedPayment->forceFill([
                'status' => PlanPayment::STATUS_PAID,
                'provider_reference' => $providerData['reference'] ?? $lockedPayment->provider_reference,
                'provider_transaction_id' => $providerData['transid'] ?? null,
                'provider_channel' => $providerData['channel'] ?? null,
                'paid_at' => now(),
                'period_ends_at' => $periodEndsAt,
                'provider_payload' => collect($providerData)->only([
                    'result', 'resultcode', 'payment_status', 'amount', 'reference', 'transid', 'channel', 'phone', 'msisdn',
                ])->all(),
            ])->save();

            $user->forceFill(['plan' => User::PLAN_PLUS, 'plus_expires_at' => $periodEndsAt])->save();
            AuditEvent::create([
                'actor_id' => $user->id,
                'action' => 'plan.payment_succeeded',
                'subject_type' => PlanPayment::class,
                'subject_id' => (string) $lockedPayment->id,
                'details' => [
                    'order_id' => $lockedPayment->order_id,
                    'amount' => $lockedPayment->amount,
                    'cycle' => $lockedPayment->billing_cycle,
                    'period_ends_at' => $periodEndsAt->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
            ]);

            return true;
        });

        if ($completedNow) {
            $payment->refresh();
            $mailer->to($payment->user->email, 'Plus plan activated · TechwareTz Control Center', [
                'eyebrow' => 'Payment confirmed',
                'title' => 'Your Plus plan is active',
                'intro' => 'We received your payment and activated Plus on your account.',
                'details' => [
                    'Amount' => 'TZS '.number_format($payment->amount),
                    'Billing cycle' => ucfirst($payment->billing_cycle),
                    'Active until' => $payment->period_ends_at?->format('d M Y'),
                    'Payment reference' => $payment->provider_reference ?? $payment->order_id,
                ],
                'button_label' => 'View plans',
                'button_url' => route('plans.index'),
            ]);
        }
    }
}
