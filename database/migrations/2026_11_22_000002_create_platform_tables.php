<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('service_modules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('name', 120);
            $table->string('version', 32)->default('1.0.0');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('project_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_module_id')->constrained()->restrictOnDelete();
            $table->string('status', 24)->default('pending')->index();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'service_module_id']);
        });

        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32)->default('github');
            $table->string('full_name', 190)->unique();
            $table->string('branch', 120)->default('main');
            $table->string('workflow_file', 190)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('hostname', 253)->unique();
            $table->string('document_root', 500)->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('host', 253)->default('localhost');
            $table->string('database_name', 128)->unique();
            $table->string('database_user', 128);
            $table->string('secret_reference', 190)->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamp('connection_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repository_id')->nullable()->constrained()->nullOnDelete();
            $table->string('commit_sha', 64)->nullable();
            $table->string('status', 24)->default('queued')->index();
            $table->string('trigger_source', 32)->default('github_actions');
            $table->string('external_run_id', 120)->nullable()->index();
            $table->string('deployment_url', 500)->nullable();
            $table->text('failure_summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 120)->index();
            $table->string('subject_type', 120)->nullable();
            $table->string('subject_id', 120)->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('deployments');
        Schema::dropIfExists('project_databases');
        Schema::dropIfExists('project_domains');
        Schema::dropIfExists('repositories');
        Schema::dropIfExists('project_modules');
        Schema::dropIfExists('service_modules');
        Schema::dropIfExists('projects');
    }
};
