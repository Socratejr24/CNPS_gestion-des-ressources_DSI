<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Ressource;
use Illuminate\Database\Seeder;

/**
 * Applications et ressources de démonstration (environnement local uniquement).
 * Idempotent : peut être relancé sans doublon.
 */
class ReferentielDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['SIGRH', 'Système de gestion des ressources humaines', 'Paie, carrières et congés des agents.'],
            ['PORTAIL', 'Portail des assurés', 'Services en ligne pour les assurés et employeurs.'],
            ['GED', 'Gestion électronique des documents', 'Archivage et circulation des documents.'],
            ['MESSAGERIE', 'Messagerie professionnelle', 'Comptes et listes de diffusion.'],
        ] as [$reference, $nom, $description]) {
            Application::firstOrCreate(['reference' => $reference], ['nom' => $nom, 'description' => $description]);
        }

        foreach ([
            ['MAT-PC-PORT', 'Ordinateur portable', 'Matériel', 'Poste de travail portable standard.'],
            ['MAT-ECRAN-24', 'Écran 24 pouces', 'Matériel', null],
            ['MAT-CLAVIER', 'Clavier et souris', 'Matériel', null],
            ['LOG-OFFICE', 'Licence suite bureautique', 'Logiciel', null],
            ['ACC-COMPTE-APP', 'Compte applicatif', 'Accès', 'Création ou modification d\'un compte sur une application.'],
            ['ACC-VPN', 'Accès VPN', 'Accès', 'Accès distant au réseau de la CNPS.'],
        ] as [$reference, $designation, $categorie, $description]) {
            Ressource::firstOrCreate(['reference' => $reference], [
                'designation' => $designation,
                'categorie' => $categorie,
                'description' => $description,
            ]);
        }
    }
}
