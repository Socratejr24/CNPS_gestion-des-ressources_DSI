<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->nullable()->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('fonction')->nullable();
            $table->string('identifiant')->nullable()->unique();
            $table->string('mot_de_passe');
            $table->boolean('is_active')->default(true);
            
            // Traçabilité (sans clés étrangères pour l'instant pour éviter les conflits)
            $table->unsignedBigInteger('cree_par')->nullable();
            $table->unsignedBigInteger('modifie_par')->nullable();
            $table->unsignedBigInteger('supprime_par')->nullable();
            
            $table->timestamps();
            $table->timestamp('supprime_le')->nullable(); // Soft delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};