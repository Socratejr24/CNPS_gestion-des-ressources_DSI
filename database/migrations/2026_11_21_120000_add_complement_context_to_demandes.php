<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contexte d'une demande d'informations complémentaires (SFD §10.3, RG-25) :
 * l'étape à laquelle la demande revient après la réponse du demandeur, et l'auteur de la question.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->string('statut_retour_complement', 40)->nullable()->after('statut');
            $table->foreignId('complement_demande_par_id')->nullable()->after('statut_retour_complement')
                ->constrained('utilisateurs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('complement_demande_par_id');
            $table->dropColumn('statut_retour_complement');
        });
    }
};
