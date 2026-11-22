<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_username', 39)->nullable()->unique()->after('email');
            $table->string('role', 24)->default('developer')->index()->after('password');
            $table->string('status', 24)->default('pending')->index()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['github_username']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['github_username', 'role', 'status']);
        });
    }
};
