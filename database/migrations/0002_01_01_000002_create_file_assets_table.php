<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 36.1 - private file assets. Files are stored outside MySQL.
     */
    public function up(): void
    {
        Schema::create('file_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('storage_disk', 50)->default('local');
            $table->string('object_key', 500)->unique();
            $table->string('original_name', 255);
            $table->string('mime_type', 150);
            $table->string('extension', 20);
            $table->bigInteger('size_bytes');
            $table->char('sha256', 64)->index();
            $table->string('encryption_key_version', 50)->nullable();
            $table->string('malware_scan_status', 30)->default('pending');
            $table->timestamp('malware_scanned_at')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->string('status', 30)->default('pending');
            $table->date('retention_until')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_assets');
    }
};
