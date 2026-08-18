<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SRD 32.1 - users table with UUIDv7 primary key.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name', 180);
            $table->string('email', 190)->unique();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password', 255);
            $table->string('status', 30)->default('invited')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('last_login_at')->nullable()->index();
            $table->char('last_login_ip_hash', 64)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('suspended_by')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('suspended_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
