<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // ⚠️ IMPORTANT : on doit d'abord supprimer l'index unique sur email
            // avant de pouvoir le rendre nullable
            $table->dropUnique(['email']);
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            
            // On remet l'index unique mais nullable-friendly
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->unique('email');
        });
    }
};