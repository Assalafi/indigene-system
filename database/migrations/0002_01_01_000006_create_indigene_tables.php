<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 35 - indigene registry and profile versions.
     */
    public function up(): void
    {
        Schema::create('indigenes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('registry_number', 50)->unique();
            $table->uuid('origin_state_id');
            $table->uuid('origin_lga_id');
            $table->uuid('current_profile_id')->nullable();
            $table->text('nin_ciphertext')->nullable();
            $table->char('nin_hash', 64)->unique()->nullable();
            $table->char('nin_last4', 4)->nullable();
            $table->string('nin_verification_status', 30)->default('unverified');
            $table->timestamp('nin_verified_at')->nullable();
            $table->string('nin_provider_reference', 150)->nullable();
            $table->string('lifecycle_status', 30)->default('provisional');
            $table->uuid('created_by');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('origin_state_id')->references('id')->on('states')->restrictOnDelete();
            $table->foreign('origin_lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();

            $table->index(['origin_lga_id', 'lifecycle_status']);
        });

        Schema::create('indigene_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('indigene_id');
            $table->unsignedInteger('version_no');
            $table->string('title', 30)->nullable();
            $table->string('surname', 100)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('other_names', 150)->nullable();
            $table->string('sex', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('nationality', 80)->default('Nigerian');
            $table->string('occupation', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 190)->nullable();
            $table->uuid('origin_state_id');
            $table->uuid('origin_lga_id');
            $table->uuid('district_id')->nullable();
            $table->uuid('ward_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->string('hometown', 180)->nullable();
            $table->text('residential_address')->nullable();
            $table->uuid('residence_state_id')->nullable();
            $table->uuid('residence_lga_id')->nullable();
            $table->string('residence_town', 150)->nullable();
            $table->text('indigene_basis')->nullable();
            $table->uuid('photo_file_id')->nullable();
            $table->string('profile_status', 30)->default('draft');
            $table->boolean('is_current')->default(false);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('indigene_id')->references('id')->on('indigenes')->restrictOnDelete();
            $table->foreign('origin_state_id')->references('id')->on('states')->restrictOnDelete();
            $table->foreign('origin_lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            $table->foreign('ward_id')->references('id')->on('wards')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('residence_state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('residence_lga_id')->references('id')->on('lgas')->nullOnDelete();
            $table->foreign('photo_file_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['indigene_id', 'version_no']);
            $table->index(['origin_lga_id', 'profile_status', 'surname']);
        });

        Schema::create('indigene_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->string('relation_type', 30);
            $table->string('full_name', 180);
            $table->string('relationship', 80)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 190)->nullable();
            $table->text('address')->nullable();
            $table->string('occupation', 150)->nullable();
            $table->uuid('state_id')->nullable();
            $table->uuid('lga_id')->nullable();
            $table->uuid('ward_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->text('nin_ciphertext')->nullable();
            $table->char('nin_hash', 64)->nullable();
            $table->char('nin_last4', 4)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on('indigene_profiles')->cascadeOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('lga_id')->references('id')->on('lgas')->nullOnDelete();
            $table->foreign('ward_id')->references('id')->on('wards')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();

            $table->index(['profile_id', 'relation_type']);
        });

        Schema::table('indigenes', function (Blueprint $table) {
            $table->foreign('current_profile_id')->references('id')->on('indigene_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indigene_relations');
        Schema::dropIfExists('indigene_profiles');
        Schema::dropIfExists('indigenes');
    }
};



