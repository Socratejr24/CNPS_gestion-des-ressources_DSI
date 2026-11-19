<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeUtilisateur;

class TypeUtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        TypeUtilisateur::firstOrCreate(
            ['libelle' => 'Agent CNPS'],
            ['description' => 'Agent interne se connectant via LDAP']
        );

        TypeUtilisateur::firstOrCreate(
            ['libelle' => 'Prestataire'],
            ['description' => 'Compte local créé par l\'administrateur']
        );
    }
}