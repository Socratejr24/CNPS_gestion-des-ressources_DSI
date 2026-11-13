<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- utilisateurs ----------
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Renommer les anciennes colonnes
            if (Schema::hasColumn('utilisateurs', 'supprime_le')) {
                $table->renameColumn('supprime_le', 'deleted_at');
            }
            if (Schema::hasColumn('utilisateurs', 'cree_par')) {
                $table->renameColumn('cree_par', 'created_by');
            }
            if (Schema::hasColumn('utilisateurs', 'modifie_par')) {
                $table->renameColumn('modifie_par', 'updated_by');
            }
            if (Schema::hasColumn('utilisateurs', 'supprime_par')) {
                $table->renameColumn('supprime_par', 'deleted_by');
            }
        });

        // ---------- roles ----------
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'supprime_le')) {
                $table->renameColumn('supprime_le', 'deleted_at');
            }
            if (!Schema::hasColumn('roles', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
            if (!Schema::hasColumn('roles', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
            if (!Schema::hasColumn('roles', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
        });

        // ---------- permissions ----------
        Schema::table('permissions', function (Blueprint $table) {
            //  Supprimer les doublons français
            if (Schema::hasColumn('permissions', 'cree_le')) {
                $table->dropColumn('cree_le');
            }
            if (Schema::hasColumn('permissions', 'modifie_le')) {
                $table->dropColumn('modifie_le');
            }
            if (Schema::hasColumn('permissions', 'supprime_le')) {
                $table->renameColumn('supprime_le', 'deleted_at');
            }
            if (!Schema::hasColumn('permissions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
            if (!Schema::hasColumn('permissions', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
            if (!Schema::hasColumn('permissions', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
        });

        // ---------- roles_permissions (pivot) ----------
        Schema::table('roles_permissions', function (Blueprint $table) {
            // Un pivot n'a pas besoin de soft delete → on supprime
            if (Schema::hasColumn('roles_permissions', 'supprime_le')) {
                $table->dropColumn('supprime_le');
            }
            if (!Schema::hasColumn('roles_permissions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Rollback (facultatif mais propre)
        Schema::table('utilisateurs', function (Blueprint $table) {
            if (Schema::hasColumn('utilisateurs', 'deleted_at')) $table->renameColumn('deleted_at', 'supprime_le');
            if (Schema::hasColumn('utilisateurs', 'created_by')) $table->renameColumn('created_by', 'cree_par');
            if (Schema::hasColumn('utilisateurs', 'updated_by')) $table->renameColumn('updated_by', 'modifie_par');
            if (Schema::hasColumn('utilisateurs', 'deleted_by')) $table->renameColumn('deleted_by', 'supprime_par');
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'deleted_at')) $table->renameColumn('deleted_at', 'supprime_le');
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'deleted_at')) $table->renameColumn('deleted_at', 'supprime_le');
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        Schema::table('roles_permissions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->timestamp('supprime_le')->nullable();
        });
    }
};