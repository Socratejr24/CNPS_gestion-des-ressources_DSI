<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Rôles fonctionnels définis par la SFD (§3.3).
        foreach ([
            'Administrateur' => 'Administration générale de l\'application',
            'Demandeur' => 'Création et suivi des demandes de ressources',
            'Receveur' => 'Traitement des demandes reçues',
        ] as $libelle => $description) {
            // withTrashed : un rôle supprimé (soft delete) bloque l'unicité du libellé,
            // on le restaure au lieu d'essayer de le recréer.
            $role = Role::withTrashed()->firstOrCreate(
                ['libelle' => $libelle],
                ['description' => $description, 'is_active' => true],
            );

            if ($role->trashed()) {
                $role->restore();
            }
        }

        User::firstOrCreate(
            ['email' => 'admin@cnps.ci'],
            User::factory()->raw([
                'email' => 'admin@cnps.ci',
                'nom' => 'Admin',
                'prenom' => 'DSI',
                'identifiant' => 'admin',
                'matricule' => null,
            ]),
        );
    }
}
