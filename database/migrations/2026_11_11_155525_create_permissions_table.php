<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->string('slug')->unique(); // Ex: 'creer_demande', 'valider_n1'
            
 
            
           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};