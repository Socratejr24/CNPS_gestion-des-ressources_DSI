<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->text('description')->nullable();
            
            // Traçabilité vers users à faire plus
            
            // Dates (Laravel utilise timestamps() pour cree_le et modifie_le)
            $table->timestamps(); 
        $table->timestamp('supprime_le')->nullable(); // On crée la vraie colonne de ton MCD
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};