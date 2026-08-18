<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 32.3 / 32.4 - LGA assignments and authentication infrastructure.
     */
    public function up(): void
    {
        Schema::create('user_lga_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('lga_id');
            $table->uuid('role_id');
            $table->string('assignment_type', 30)->default('primary');
            $table->string('appointment_title', 120)->nullable();
            $table->string('appointment_reference', 100)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('status', 20)->default('active');
            $table->uuid('created_by')->nullable();
            $table->uuid('ended_by')->nullable();
            $table->text('end_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ended_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['lga_id', 'role_id', 'status', 'ends_at']);
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->char('token_hash', 64)->unique();
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->uuid('revoked_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('revoked_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('email_hash', 64)->index();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('account_activation_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('login_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->char('identity_hash', 64)->nullable();
            $table->string('event_type', 40);
            $table->boolean('success')->default(false);
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('risk_flags')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_events');
        Schema::dropIfExists('account_activation_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('user_lga_assignments');
    }
};
