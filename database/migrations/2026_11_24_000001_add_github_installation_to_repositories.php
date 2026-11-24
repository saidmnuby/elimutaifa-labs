<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->unsignedBigInteger('github_installation_id')->nullable()->index()->after('full_name');
        });
    }

    public function down(): void
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->dropIndex(['github_installation_id']);
            $table->dropColumn('github_installation_id');
        });
    }
};
