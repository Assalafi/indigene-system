<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 33.3-33.7 - districts, wards, units, geography import batches and aliases.
     */
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lga_id');
            $table->string('code', 40);
            $table->string('name', 150);
            $table->string('status', 20)->default('active');
            $table->string('source_name', 255)->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['lga_id', 'code']);
            $table->index(['lga_id', 'status', 'name']);
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lga_id');
            $table->uuid('district_id')->nullable();
            $table->string('code', 40);
            $table->string('name', 150);
            $table->string('status', 30)->default('active');
            $table->string('source_name', 255)->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->uuid('import_batch_id')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('merged_into_ward_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            $table->foreign('merged_into_ward_id')->references('id')->on('wards')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['lga_id', 'code']);
            $table->index(['lga_id', 'status', 'name']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lga_id');
            $table->uuid('ward_id');
            $table->uuid('district_id')->nullable();
            $table->uuid('parent_unit_id')->nullable();
            $table->string('code', 50);
            $table->string('name', 180);
            $table->string('category', 40)->default('village');
            $table->string('status', 20)->default('active');
            $table->string('source_name', 255)->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->uuid('import_batch_id')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('merged_into_unit_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('lga_id')->references('id')->on('lgas')->restrictOnDelete();
            $table->foreign('ward_id')->references('id')->on('wards')->restrictOnDelete();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            $table->foreign('parent_unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('merged_into_unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['ward_id', 'category', 'code']);
            $table->index(['lga_id', 'ward_id', 'status', 'name']);
        });

        Schema::create('geography_import_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_name', 255);
            $table->string('source_reference', 255)->nullable();
            $table->string('dataset_type', 50);
            $table->string('dataset_version', 50)->nullable();
            $table->date('source_date')->nullable();
            $table->uuid('file_asset_id')->nullable();
            $table->char('checksum_sha256', 64)->nullable();
            $table->string('status', 30)->default('uploaded');
            $table->integer('row_count')->default(0);
            $table->integer('inserted_count')->default(0);
            $table->integer('updated_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('validation_report')->nullable();
            $table->uuid('imported_by')->nullable();
            $table->uuid('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('file_asset_id')->references('id')->on('file_assets')->nullOnDelete();
            $table->foreign('imported_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('geography_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type', 30);
            $table->uuid('entity_id');
            $table->string('alias', 180);
            $table->string('alias_type', 30)->default('legacy');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geography_aliases');
        Schema::dropIfExists('geography_import_batches');
        Schema::dropIfExists('units');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('districts');
    }
};
