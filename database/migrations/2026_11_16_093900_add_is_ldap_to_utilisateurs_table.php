<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Valeur par défaut à false (compte local/prestataire)
            // Les agents CNPS auront true lors de la création via LDAP
            $table->boolean('is_ldap')
                  ->default(false)
                  ->after('is_active'); // Place la colonne après is_active
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn('is_ldap');
        });
    }
};