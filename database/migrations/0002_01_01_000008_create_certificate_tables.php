<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 37 - certificates, versions, sequences, print/status/verification events and fraud reports.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('indigene_id');
            $table->uuid('approved_application_id');
            $table->uuid('lga_id');
            $table->string('certificate_number', 80)->unique()->nullable();
            $table->string('status', 30)->default('active');
            $table->uuid('current_version_id')->nullable();
            $table->char('public_token_hash', 64)->unique();
            $table->string('public_token_hint', 12)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->uuid('approved_by');
            $table->unsignedInteger('total_prints_cached')->default(0);
            $table->uuid('superseded_by_certificate_id')->nullable();
            $table->timestamps();

            $table->foreign('indigene_id')->references('id')->on('indigenes')->restrictOnDelete();
            $table->foreign('approved_application_id')->references('id')->on('indigene_applications')->restrictOnDelete();
            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('superseded_by_certificate_id')->references('id')->on('certificates')->nullOnDelete();

            $table->index(['lga_id', 'status']);
            $table->index(['indigene_id', 'status']);
        });

        Schema::create('certificate_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id');
            $table->unsignedInteger('version_no');
            $table->uuid('certificate_template_id')->nullable();
            $table->uuid('lga_profile_id')->nullable();
            $table->uuid('signatory_id')->nullable();
            $table->uuid('source_profile_id');
            $table->longText('snapshot_ciphertext');
            $table->uuid('pdf_file_id')->nullable();
            $table->char('pdf_sha256', 64)->nullable();
            $table->char('qr_payload_hash', 64)->nullable();
            $table->uuid('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('certificate_id')->references('id')->on('certificates')->restrictOnDelete();
            $table->foreign('certificate_template_id')->references('id')->on('certificate_templates')->nullOnDelete();
            $table->foreign('lga_profile_id')->references('id')->on('lga_profiles')->nullOnDelete();
            $table->foreign('signatory_id')->references('id')->on('official_signatories')->nullOnDelete();
            $table->foreign('source_profile_id')->references('id')->on('indigene_profiles')->restrictOnDelete();
            $table->foreign('pdf_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['certificate_id', 'version_no']);
        });

        Schema::create('certificate_number_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lga_id');
            $table->unsignedSmallInteger('year');
            $table->string('prefix', 30)->default('');
            $table->unsignedBigInteger('next_value')->default(1);
            $table->unsignedSmallInteger('padding')->default(6);
            $table->timestamp('updated_at')->nullable();

            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->unique(['lga_id', 'year', 'prefix']);
        });

        Schema::create('certificate_print_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id');
            $table->uuid('certificate_version_id');
            $table->unsignedInteger('print_number');
            $table->string('copy_type', 20)->default('original');
            $table->string('reason_code', 60)->nullable();
            $table->text('reason_note')->nullable();
            $table->uuid('requested_by');
            $table->string('requester_role', 60)->nullable();
            $table->uuid('requester_lga_id')->nullable();
            $table->char('idempotency_key_hash', 64)->nullable();
            $table->uuid('pdf_file_id')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('certificate_id')->references('id')->on('certificates')->restrictOnDelete();
            $table->foreign('certificate_version_id')->references('id')->on('certificate_versions')->restrictOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('requester_lga_id')->references('id')->on('lgas')->nullOnDelete();
            $table->foreign('pdf_file_id')->references('id')->on('file_assets')->nullOnDelete();

            $table->unique(['certificate_id', 'print_number'], 'cert_print_num_unique');
            $table->unique(['requested_by', 'idempotency_key_hash'], 'print_idem_unique');
        });

        Schema::create('certificate_status_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id');
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->string('reason_code', 60)->nullable();
            $table->text('reason_text')->nullable();
            $table->uuid('evidence_file_id')->nullable();
            $table->timestamp('effective_at')->nullable();
            $table->uuid('actor_id')->nullable();
            $table->string('actor_role', 60)->nullable();
            $table->uuid('actor_lga_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('certificate_id')->references('id')->on('certificates')->restrictOnDelete();
            $table->foreign('evidence_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actor_lga_id')->references('id')->on('lgas')->nullOnDelete();

            $table->index(['certificate_id', 'created_at']);
        });

        Schema::create('certificate_verification_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id')->nullable();
            $table->string('lookup_type', 30);
            $table->char('lookup_hash', 64)->nullable();
            $table->string('result_status', 30);
            $table->char('ip_prefix_hash', 64)->nullable();
            $table->string('user_agent_family', 60)->nullable();
            $table->string('country_code', 4)->nullable();
            $table->integer('risk_score')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('certificate_id')->references('id')->on('certificates')->nullOnDelete();
            $table->index(['lookup_type', 'created_at']);
        });

        Schema::create('fraud_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id')->nullable();
            $table->string('reference_number', 50)->unique();
            $table->text('reporter_name_ciphertext')->nullable();
            $table->text('reporter_contact_ciphertext')->nullable();
            $table->text('report_text');
            $table->uuid('evidence_file_id')->nullable();
            $table->string('status', 30)->default('open');
            $table->uuid('assigned_to')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('certificate_id')->references('id')->on('certificates')->nullOnDelete();
            $table->foreign('evidence_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_reports');
        Schema::dropIfExists('certificate_verification_events');
        Schema::dropIfExists('certificate_status_events');
        Schema::dropIfExists('certificate_print_events');
        Schema::dropIfExists('certificate_number_sequences');
        Schema::dropIfExists('certificate_versions');
        Schema::dropIfExists('certificates');
    }
};
