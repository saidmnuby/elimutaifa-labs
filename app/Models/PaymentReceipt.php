<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'transaction_reference', 'amount', 'channel', 'recorded_by', 'plan_payment_id', 'notes', 'recorded_at',
    ];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function planPayment(): BelongsTo
    {
        return $this->belongsTo(PlanPayment::class);
    }
}
