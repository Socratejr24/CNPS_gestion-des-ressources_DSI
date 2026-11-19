<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Audit (selon tes conventions)
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_utilisateurs');
    }
};