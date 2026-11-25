<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traitement des demandes (SFD §8), réception et anomalies (SFD §9).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            // Receveur ayant pris en charge la demande (RG-23) : seul habilité à la traiter ensuite.
            $table->foreignId('receveur_id')->nullable()->after('service_demandeur_id')
                ->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('prise_en_charge_le')->nullable()->after('soumise_le');
            $table->date('mise_a_disposition_le')->nullable()->after('prise_en_charge_le');
            $table->text('observations_mise_a_disposition')->nullable()->after('mise_a_disposition_le');
            $table->timestamp('cloturee_le')->nullable()->after('observations_mise_a_disposition');

            $table->index(['receveur_id', 'statut']);
        });

        // Anomalies de réception (SFD §9.2) et leur régularisation (SFD §9.3).
        Schema::create('demande_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignId('signalee_par_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->string('nature', 40);
            $table->text('description');
            // Ressources concernées : [{ "ligne_id": 1, "quantite": 2 }, ...]
            $table->json('lignes');
            $table->foreignId('regularisee_par_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('regularisee_le')->nullable();
            $table->text('explication_regularisation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_anomalies');

        Schema::table('demandes', function (Blueprint $table) {
            $table->dropIndex(['receveur_id', 'statut']);
            $table->dropConstrainedForeignId('receveur_id');
            $table->dropColumn(['prise_en_charge_le', 'mise_a_disposition_le', 'observations_mise_a_disposition', 'cloturee_le']);
        });
    }
};
