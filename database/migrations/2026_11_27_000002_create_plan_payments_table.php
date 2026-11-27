<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('plus_expires_at')->nullable()->after('plan');
        });

        Schema::create('plan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_id', 64)->unique();
            $table->string('billing_cycle', 12);
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('TZS');
            $table->string('payment_method', 20);
            $table->string('status', 20)->default('pending')->index();
            $table->string('provider_reference', 100)->nullable()->index();
            $table->string('provider_transaction_id', 100)->nullable()->unique();
            $table->string('provider_channel', 60)->nullable();
            $table->string('buyer_phone', 20);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('period_ends_at')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_payments');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plus_expires_at');
        });
    }
};
