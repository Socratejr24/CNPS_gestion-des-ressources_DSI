<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes de ressources (SFD §6), leurs lignes et leur historique (SFD §13).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            // Référence unique générée par l'application (SFD §6.2), ex. DEM-2026-00001.
            $table->string('reference', 30)->unique();
            $table->foreignId('demandeur_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();

            $table->string('objet');
            $table->text('description')->nullable();               // description ou motif
            $table->text('informations_complementaires')->nullable();

            // Destination unique (RG-13, RG-14) : un seul des trois champs est renseigné.
            $table->string('destination_type', 20)->nullable();
            $table->foreignId('destination_departement_id')->nullable()->constrained('departements')->restrictOnDelete();
            $table->foreignId('destination_service_id')->nullable()->constrained('services')->restrictOnDelete();
            $table->foreignId('destination_utilisateur_id')->nullable()->constrained('utilisateurs')->restrictOnDelete();

            $table->string('statut', 40)->default('BROUILLON')->index();

            // Contexte organisationnel du demandeur figé à la soumission : le circuit
            // N+1 / N+2 se calcule à partir de ce service (RG-15, RG-16).
            $table->foreignId('service_demandeur_id')->nullable()->constrained('services')->restrictOnDelete();
            $table->timestamp('soumise_le')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();

            $table->index(['demandeur_id', 'statut']);
        });

        // Ressources souhaitées et quantités (SFD §6.2).
        Schema::create('demande_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignId('ressource_id')->constrained('ressources')->restrictOnDelete();
            $table->unsignedInteger('quantite_demandee')->default(1);
            $table->unsignedInteger('quantite_fournie')->nullable();   // renseignée à la mise à disposition (SFD §8.4)
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['demande_id', 'ressource_id']);
        });

        // Historique des actions (SFD §13.2, RG-33) : jamais modifié ni supprimé.
        Schema::create('demande_evenements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->string('action', 40);
            $table->string('ancien_statut', 40)->nullable();
            $table->string('nouveau_statut', 40)->nullable();
            $table->string('niveau', 10)->nullable();                // N1 / N2 pour les validations
            $table->text('commentaire')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['demande_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_evenements');
        Schema::dropIfExists('demande_lignes');
        Schema::dropIfExists('demandes');
    }
};
