<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organisation de la DSI (SFD §2) : Structure → Département → Service → Utilisateur.
 *
 * Pas de soft delete sur les entités organisationnelles : la SFD prévoit
 * leur désactivation (is_active), jamais leur suppression, afin de préserver
 * l'historique des demandes qui y font référence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            // N+2 : responsable du département (SFD §2.3, RG-16)
            $table->foreignId('responsable_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            // N+1 : chef de service (SFD §2.3, RG-15)
            $table->foreignId('chef_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('utilisateurs', function (Blueprint $table) {
            // SFD §3.1 : AGENT (LDAP) ou PRESTATAIRE (compte local)
            $table->string('categorie', 20)->default('AGENT')->after('fonction');
            // RG-03 : un seul service principal ; null = affectation en attente (RG-11)
            $table->foreignId('service_id')->nullable()->after('categorie')
                ->constrained('services')->nullOnDelete();
        });

        // SFD §3.3 : un utilisateur peut cumuler plusieurs rôles fonctionnels.
        Schema::create('utilisateurs_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
            $table->unique(['utilisateur_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs_roles');

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('categorie');
        });

        Schema::dropIfExists('services');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('structures');
    }
};
