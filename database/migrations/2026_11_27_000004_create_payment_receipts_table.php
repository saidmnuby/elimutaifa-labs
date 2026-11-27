<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference', 100)->unique();
            $table->unsignedInteger('amount');
            $table->string('channel', 24);
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('plan_payment_id')->nullable()->unique()->constrained('plan_payments')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
