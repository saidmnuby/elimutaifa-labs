<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_database_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('requested_name_suffix', 55);
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_database_id')->nullable()->unique()->constrained('project_databases')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        $databaseModuleId = DB::table('service_modules')->where('key', 'database')->value('id');
        if (! $databaseModuleId) {
            return;
        }

        $legacyRequests = DB::table('project_modules')
            ->where('service_module_id', $databaseModuleId)
            ->whereNotNull('settings')
            ->get();

        foreach ($legacyRequests as $legacyRequest) {
            $settings = is_array($legacyRequest->settings)
                ? $legacyRequest->settings
                : json_decode((string) $legacyRequest->settings, true);
            $suffix = $settings['requested_name_suffix'] ?? null;
            if (! is_string($suffix) || $suffix === '') {
                continue;
            }

            $hasAssignedDatabase = DB::table('project_databases')
                ->where('project_id', $legacyRequest->project_id)
                ->exists();
            if ($hasAssignedDatabase) {
                continue;
            }

            DB::table('project_database_requests')->insert([
                'project_id' => $legacyRequest->project_id,
                'requested_name_suffix' => $suffix,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('project_modules')
                ->where('project_id', $legacyRequest->project_id)
                ->where('service_module_id', $databaseModuleId)
                ->update(['status' => 'pending', 'settings' => null, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_database_requests');
    }
};
