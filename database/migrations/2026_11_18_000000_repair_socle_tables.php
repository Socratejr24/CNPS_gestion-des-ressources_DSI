<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration de réparation du socle.
 *
 * Idempotente : chaque opération vérifie l'état actuel avant d'agir,
 * afin de fonctionner aussi bien sur une base neuve que sur une base
 * déjà migrée avec les anciennes versions des migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tables système Laravel supprimées par erreur de la migration initiale.
        //    SESSION_DRIVER=database exige la table `sessions`.
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Réinitialisation de mot de passe des comptes locaux (prestataires).
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // 2. utilisateurs : aligner la colonne mot de passe sur le modèle / Laravel Auth.
        if (Schema::hasColumn('utilisateurs', 'mot_de_passe') && ! Schema::hasColumn('utilisateurs', 'password')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->renameColumn('mot_de_passe', 'password');
            });
        }

        // Le mot de passe est facultatif : les agents CNPS s'authentifient via LDAP.
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        if (! Schema::hasColumn('utilisateurs', 'remember_token')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }

        // 3. utilisateurs : rétablir les index d'unicité perdus lors des renommages.
        foreach (['matricule', 'email', 'identifiant'] as $column) {
            if (! Schema::hasIndex('utilisateurs', [$column], 'unique')) {
                Schema::table('utilisateurs', function (Blueprint $table) use ($column) {
                    $table->unique($column);
                });
            }
        }

        // 4. permissions : supprimer les colonnes françaises redondantes recréées
        //    par 2026_11_12_105118 (doublons de created_at / updated_at / deleted_at).
        foreach (['cree_le', 'modifie_le', 'supprime_le'] as $column) {
            if (Schema::hasColumn('permissions', $column)) {
                Schema::table('permissions', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        // Réparation non réversible volontairement : revenir en arrière
        // réintroduirait les anomalies corrigées.
    }
};
