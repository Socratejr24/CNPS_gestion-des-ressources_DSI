<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiels des demandes (SFD §4 et §5) : applications et ressources.
 *
 * Pas de suppression : désactivation (is_active), pour préserver l'historique
 * des demandes qui y font référence.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SFD §4.3 : nom, description, référence, statut, date de création.
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });

        // SFD §5.3 : désignation, description, catégorie, référence, statut, informations complémentaires.
        Schema::create('ressources', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('designation');
            $table->string('categorie', 100)->index();
            $table->text('description')->nullable();
            $table->text('informations_complementaires')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ressources');
        Schema::dropIfExists('applications');
    }
};
