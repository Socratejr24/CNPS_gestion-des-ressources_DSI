<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\TypeUtilisateur;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $typePrestataire = TypeUtilisateur::where('libelle', 'Prestataire')->first();
        
        if (!$typePrestataire) {
            $this->command->error('Type "Prestataire" introuvable. Lance TypeUtilisateurSeeder.');
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => 'socratejr20052@gmail.com'],
            [
                'nom' => 'Administrateur',
                'prenom' => 'DSI',
                'password' => 'Admin@123',
                'is_ldap' => false,
                'is_active' => true,
                'type_utilisateur_id' => $typePrestataire->id, 
            ]
        );

        $roleAdmin = Role::where('libelle', 'Administrateur')->first();
        
        if ($roleAdmin) {
            $admin->roles()->syncWithoutDetaching([$roleAdmin->id]);
            $this->command->info(' Admin créé : socratejr20052@gmail.com   / Admin@123');
        } else {
            $this->command->warn('Rôle "Administrateur" introuvable.');
        }
    }
}