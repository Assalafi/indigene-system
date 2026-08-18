<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 35.4-35.8 / 36.2 - applications, reviews, duplicate flags, consent and documents.
     */
    public function up(): void
    {
        Schema::create('indigene_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('application_number', 50)->unique();
            $table->uuid('indigene_id');
            $table->uuid('profile_id')->unique();
            $table->uuid('lga_id');
            $table->string('application_type', 30)->default('onboarding');
            $table->string('status', 40)->default('draft')->index();
            $table->string('approval_route', 30)->default('chairman_or_admin');
            $table->uuid('created_by');
            $table->uuid('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('assigned_reviewer_id')->nullable();
            $table->uuid('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_reason_code', 60)->nullable();
            $table->text('decision_comment')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->timestamp('due_at')->nullable();
            $table->smallInteger('last_saved_step')->default(1);
            $table->string('declaration_version', 30)->nullable();
            $table->timestamp('declaration_accepted_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->foreign('indigene_id')->references('id')->on('indigenes')->restrictOnDelete();
            $table->foreign('profile_id')->references('id')->on('indigene_profiles')->restrictOnDelete();
            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_reviewer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['lga_id', 'status', 'submitted_at']);
            $table->index(['created_by', 'status']);
            $table->index(['assigned_reviewer_id', 'status']);
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->string('from_status', 40);
            $table->string('to_status', 40);
            $table->string('action', 50);
            $table->uuid('actor_id')->nullable();
            $table->string('actor_role', 60)->nullable();
            $table->uuid('actor_lga_id')->nullable();
            $table->text('public_comment')->nullable();
            $table->text('internal_comment')->nullable();
            $table->string('reason_code', 60)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('application_id')->references('id')->on('indigene_applications')->restrictOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actor_lga_id')->references('id')->on('lgas')->nullOnDelete();

            $table->index(['application_id', 'created_at']);
        });

        Schema::create('application_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->uuid('reviewer_id')->nullable();
            $table->string('review_type', 30);
            $table->string('outcome', 30);
            $table->string('checklist_version', 30)->nullable();
            $table->json('checklist')->nullable();
            $table->json('risk_flags')->nullable();
            $table->text('public_comment')->nullable();
            $table->text('internal_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('application_id')->references('id')->on('indigene_applications')->restrictOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('duplicate_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->uuid('candidate_indigene_id')->nullable();
            $table->string('match_type', 30);
            $table->decimal('score', 5, 2)->nullable();
            $table->json('evidence')->nullable();
            $table->string('status', 30)->default('open');
            $table->uuid('reviewed_by')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('application_id')->references('id')->on('indigene_applications')->restrictOnDelete();
            $table->foreign('candidate_indigene_id')->references('id')->on('indigenes')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['status', 'application_id']);
        });

        Schema::create('consent_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('indigene_id');
            $table->uuid('application_id');
            $table->string('data_subject_type', 30)->default('applicant');
            $table->uuid('relation_id')->nullable();
            $table->string('notice_version', 30);
            $table->string('lawful_basis', 60);
            $table->json('purpose_codes')->nullable();
            $table->boolean('consent_required')->default(false);
            $table->boolean('accepted')->nullable();
            $table->string('captured_method', 30)->default('portal');
            $table->uuid('captured_by')->nullable();
            $table->uuid('evidence_file_id')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('indigene_id')->references('id')->on('indigenes')->restrictOnDelete();
            $table->foreign('application_id')->references('id')->on('indigene_applications')->restrictOnDelete();
            $table->foreign('relation_id')->references('id')->on('indigene_relations')->nullOnDelete();
            $table->foreign('captured_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('evidence_file_id')->references('id')->on('file_assets')->nullOnDelete();
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->uuid('profile_id');
            $table->uuid('file_asset_id');
            $table->string('document_type', 60);
            $table->text('document_number_ciphertext')->nullable();
            $table->string('issuing_authority', 180)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('verification_status', 30)->default('pending');
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('indigene_applications')->restrictOnDelete();
            $table->foreign('profile_id')->references('id')->on('indigene_profiles')->restrictOnDelete();
            $table->foreign('file_asset_id')->references('id')->on('file_assets')->restrictOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['application_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('consent_records');
        Schema::dropIfExists('duplicate_flags');
        Schema::dropIfExists('application_reviews');
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('indigene_applications');
    }
};
