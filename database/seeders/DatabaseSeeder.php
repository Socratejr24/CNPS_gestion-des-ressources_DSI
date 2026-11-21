<?php

namespace Database\Seeders;

use App\Enums\CategorieUtilisateur;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Idempotent : peut être relancé sans doublons.
     */
    public function run(): void
    {
        // Rôles fonctionnels définis par la SFD (§3.3).
        foreach ([
            Role::ADMINISTRATEUR => 'Administration générale de l\'application',
            Role::DEMANDEUR => 'Création et suivi des demandes de ressources',
            Role::RECEVEUR => 'Traitement des demandes reçues',
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

        // Permissions d'affichage utilisées par la sidebar (user_can()).
        foreach ([
            'voir.traitement' => ['Voir la section Traitement', Role::RECEVEUR],
            'voir.administration' => ['Voir la section Administration', Role::ADMINISTRATEUR],
        ] as $slug => [$libelle, $roleLibelle]) {
            $permission = Permission::withTrashed()->firstOrCreate(
                ['slug' => $slug],
                ['libelle' => $libelle, 'is_active' => true],
            );
            if ($permission->trashed()) {
                $permission->restore();
            }
            Role::where('libelle', $roleLibelle)->first()
                ->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@cnps.ci'],
            User::factory()->raw([
                'email' => 'admin@cnps.ci',
                'nom' => 'Admin',
                'prenom' => 'DSI',
                'identifiant' => 'admin',
                'matricule' => null,
                'categorie' => CategorieUtilisateur::PRESTATAIRE->value, // compte local
            ]),
        );

        $admin->roles()->syncWithoutDetaching(
            Role::where('libelle', Role::ADMINISTRATEUR)->pluck('id')
        );

        if (app()->environment('local')) {
            $this->call([OrganisationDemoSeeder::class, ReferentielDemoSeeder::class]);
        }
    }
}
