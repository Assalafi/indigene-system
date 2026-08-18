<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 38 / 39 - notifications, exports, settings, audit and privacy tables.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 180);
            $table->string('notifiable_type', 125);
            $table->uuid('notifiable_id');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('notification_id');
            $table->string('channel', 30);
            $table->char('destination_hash', 64)->nullable();
            $table->string('provider_reference', 180)->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('last_error_code', 60)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('report_code', 60);
            $table->uuid('requested_by');
            $table->uuid('lga_scope_id')->nullable();
            $table->json('filters')->nullable();
            $table->string('format', 10)->default('csv');
            $table->text('purpose');
            $table->string('status', 30)->default('queued');
            $table->uuid('file_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->foreign('requested_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('lga_scope_id')->references('id')->on('lgas')->nullOnDelete();
            $table->foreign('file_id')->references('id')->on('file_assets')->nullOnDelete();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('scope_type', 30)->default('global');
            $table->uuid('scope_id')->nullable();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->integer('version')->default(1);
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['scope_type', 'scope_id', 'key']);
        });

        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id');
            $table->string('operation', 60);
            $table->char('key_hash', 64);
            $table->text('request_hash')->nullable();
            $table->string('response_reference', 180)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('actor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['actor_id', 'operation', 'key_hash']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable();
            $table->string('actor_type', 60)->nullable();
            $table->string('actor_role', 60)->nullable();
            $table->uuid('actor_lga_id')->nullable();
            $table->string('action', 60);
            $table->string('auditable_type', 125)->nullable();
            $table->uuid('auditable_id')->nullable();
            $table->uuid('request_id')->nullable();
            $table->string('route_name', 150)->nullable();
            $table->string('http_method', 10)->nullable();
            $table->string('result', 20)->default('success');
            $table->string('risk_level', 20)->default('low');
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->char('previous_hash', 64)->nullable();
            $table->char('event_hash', 64)->nullable();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actor_lga_id')->references('id')->on('lgas')->nullOnDelete();

            $table->index(['action', 'occurred_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['actor_id', 'occurred_at']);
        });

        Schema::create('sensitive_data_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable();
            $table->string('subject_type', 60);
            $table->uuid('subject_id');
            $table->string('data_category', 40);
            $table->string('action', 30);
            $table->text('purpose');
            $table->string('approval_reference', 100)->nullable();
            $table->string('result', 20)->default('success');
            $table->char('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['actor_id', 'created_at']);
        });

        Schema::create('privacy_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_number', 50)->unique();
            $table->uuid('indigene_id')->nullable();
            $table->text('requester_identity_ciphertext')->nullable();
            $table->string('request_type', 40);
            $table->string('channel', 30)->default('portal');
            $table->timestamp('received_at')->nullable();
            $table->string('verification_status', 30)->default('unverified');
            $table->string('status', 30)->default('open');
            $table->uuid('assigned_to')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->text('lawful_exception')->nullable();
            $table->text('decision')->nullable();
            $table->uuid('response_file_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('indigene_id')->references('id')->on('indigenes')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('response_file_id')->references('id')->on('file_assets')->nullOnDelete();
        });

        Schema::create('legal_holds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type', 60);
            $table->uuid('subject_id');
            $table->text('reason');
            $table->string('authority_reference', 180)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->uuid('created_by')->nullable();
            $table->uuid('released_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('released_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('retention_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('policy_code', 60);
            $table->string('subject_type', 60);
            $table->uuid('subject_id')->nullable();
            $table->string('action', 40);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->string('result', 20)->nullable();
            $table->string('actor_type', 30)->default('system');
            $table->json('evidence')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_actions');
        Schema::dropIfExists('legal_holds');
        Schema::dropIfExists('privacy_requests');
        Schema::dropIfExists('sensitive_data_access_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notifications');
    }
};
