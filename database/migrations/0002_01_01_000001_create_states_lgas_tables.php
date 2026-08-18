<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 33.1 / 33.2 - states and lgas.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100)->unique();
            $table->string('type', 20)->default('state');
            $table->string('capital', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('source_name', 255)->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('lgas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('state_id');
            $table->string('code', 20);
            $table->string('name', 150);
            $table->string('type', 30)->default('lga');
            $table->string('headquarters', 150)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('source_name', 255)->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->uuid('merged_into_lga_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('state_id')->references('id')->on('states')->restrictOnDelete();
            $table->foreign('merged_into_lga_id')->references('id')->on('lgas')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['state_id', 'code']);
            $table->index(['state_id', 'status', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgas');
        Schema::dropIfExists('states');
    }
};
