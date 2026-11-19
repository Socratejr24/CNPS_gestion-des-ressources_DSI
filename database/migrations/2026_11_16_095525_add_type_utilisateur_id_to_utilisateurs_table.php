<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // On ajoute la colonne nullable pour ne pas bloquer les utilisateurs existants
            $table->foreignId('type_utilisateur_id')
                  ->nullable()
                  ->after('id') // Place la colonne juste après l'id
                  ->constrained('types_utilisateurs')
                  ->nullOnDelete(); // Si le type est supprimé, on met NULL
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // On doit d'abord supprimer la contrainte de clé étrangère
            $table->dropForeign(['type_utilisateur_id']);
            $table->dropColumn('type_utilisateur_id');
        });
    }
};