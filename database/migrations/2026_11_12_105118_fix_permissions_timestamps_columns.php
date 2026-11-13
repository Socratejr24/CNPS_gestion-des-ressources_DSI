<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // On ajoute UNIQUEMENT si absentes (sécurité)
            if (!Schema::hasColumn('permissions', 'cree_le')) {
                $table->timestamp('cree_le')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('permissions', 'modifie_le')) {
                $table->timestamp('modifie_le')->nullable()->after('cree_le');
            }
            if (!Schema::hasColumn('permissions', 'supprime_le')) {
                $table->timestamp('supprime_le')->nullable()->after('modifie_le');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['cree_le', 'modifie_le', 'supprime_le']);
        });
    }
};