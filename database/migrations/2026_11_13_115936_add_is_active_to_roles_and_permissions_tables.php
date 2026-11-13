<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // roles
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });

        // permissions
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};