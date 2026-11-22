<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_databases', function (Blueprint $table) {
            $table->text('encrypted_password')->nullable()->after('secret_reference');
        });
    }

    public function down(): void
    {
        Schema::table('project_databases', function (Blueprint $table) {
            $table->dropColumn('encrypted_password');
        });
    }
};
