<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 34 - LGA branding and authority tables.
     */
    public function up(): void
    {
        Schema::create('lga_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lga_id');
            $table->string('display_name', 180)->nullable();
            $table->text('office_address')->nullable();
            $table->string('support_phone', 20)->nullable();
            $table->string('support_email', 190)->nullable();
            $table->string('primary_colour', 20)->nullable();
            $table->string('secondary_colour', 20)->nullable();
            $table->uuid('logo_file_id')->nullable();
            $table->uuid('coat_of_arms_file_id')->nullable();
            $table->text('certificate_heading')->nullable();
            $table->longText('certificate_body_template')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('status', 20)->default('draft');
            $table->integer('version_no')->default(1);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('logo_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('coat_of_arms_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['lga_id', 'version_no']);
        });

        Schema::create('official_signatories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lga_id');
            $table->string('full_name', 180);
            $table->string('office_title', 150);
            $table->uuid('signature_file_id')->nullable();
            $table->uuid('seal_file_id')->nullable();
            $table->string('appointment_reference', 100)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('is_primary')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('signature_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('seal_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['lga_id', 'status', 'effective_from']);
        });

        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 180);
            $table->string('code', 60);
            $table->string('scope_type', 30)->default('global');
            $table->uuid('lga_id')->nullable();
            $table->integer('version_no')->default(1);
            $table->string('blade_view', 180)->default('certificates.templates.standard');
            $table->string('page_size', 20)->default('A4');
            $table->string('orientation', 10)->default('portrait');
            $table->json('configuration')->nullable();
            $table->uuid('preview_file_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->foreign('lga_id')->references('id')->on('lgas')->nullOnDelete();
            $table->foreign('preview_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['code', 'version_no', 'lga_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('official_signatories');
        Schema::dropIfExists('lga_profiles');
    }
};
