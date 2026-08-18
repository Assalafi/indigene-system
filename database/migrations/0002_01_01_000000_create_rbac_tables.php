<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRD 32.2 - RBAC tables (Spatie, UUID primary keys including pivots).
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 125);
            $table->string('guard_name', 125);
            $table->string('description', 255)->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 125);
            $table->string('guard_name', 125);
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Facades\DB::raw('(UUID())'));
            $table->uuid('role_id');
            $table->uuid('permission_id');

            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Facades\DB::raw('(UUID())'));
            $table->uuid('role_id');
            $table->string('model_type', 125);
            $table->uuid('model_id');

            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();

            $table->index(['model_type', 'model_id']);
            $table->unique(['role_id', 'model_type', 'model_id']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Facades\DB::raw('(UUID())'));
            $table->uuid('permission_id');
            $table->string('model_type', 125);
            $table->uuid('model_id');

            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();

            $table->index(['model_type', 'model_id']);
            $table->unique(['permission_id', 'model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
