<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_payments', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('paid_at');
            $table->text('review_note')->nullable()->after('reviewed_at');
            $table->string('buyer_phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('plan_payments')->whereNull('buyer_phone')->update(['buyer_phone' => '']);

        Schema::table('plan_payments', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'review_note']);
            $table->string('buyer_phone', 20)->nullable(false)->change();
        });
    }
};
