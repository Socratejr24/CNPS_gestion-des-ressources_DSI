<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Role;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Organisation fictive pour le développement local (jamais en production).
 *
 * DSI
 *  └─ DEV  (responsable N+2 : Konan Yao)
 *      ├─ DEV-IT  (chef N+1 : Koffi Aya)  ← Kouassi Adou (agent), Bamba Issa (prestataire)
 *      └─ DEV-QA
 *  └─ INFRA (responsable N+2 : non désigné)
 *      └─ INFRA-RES (chef N+1 : non désigné)
 */
class OrganisationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $dsi = Structure::firstOrCreate(['code' => 'DSI'], ['libelle' => 'Direction des Systèmes d\'Information']);

        $dev = Departement::firstOrCreate(['code' => 'DEV'], ['structure_id' => $dsi->id, 'libelle' => 'Département Développement']);
        $infra = Departement::firstOrCreate(['code' => 'INFRA'], ['structure_id' => $dsi->id, 'libelle' => 'Département Infrastructure']);

        $devIt = Service::firstOrCreate(['code' => 'DEV-IT'], ['departement_id' => $dev->id, 'libelle' => 'Service Développement IT']);
        Service::firstOrCreate(['code' => 'DEV-QA'], ['departement_id' => $dev->id, 'libelle' => 'Service Qualité']);
        Service::firstOrCreate(['code' => 'INFRA-RES'], ['departement_id' => $infra->id, 'libelle' => 'Service Réseaux']);

        $roles = Role::pluck('id', 'libelle');

        // Les matricules des agents correspondent aux comptes de l'annuaire simulé (config/ldap.php).
        $personnes = [
            ['konan.yao@cnps.ci', 'm1003', 'Yao', 'Konan', 'AGENT', [Role::DEMANDEUR]],
            ['koffi.aya@cnps.ci', 'm1002', 'Aya', 'Koffi', 'AGENT', [Role::DEMANDEUR, Role::RECEVEUR]],
            ['kouassi.adou@cnps.ci', 'm1001', 'Adou', 'Kouassi', 'AGENT', [Role::DEMANDEUR, Role::RECEVEUR]],
            ['bamba.issa@prestataire.ci', null, 'Issa', 'Bamba', 'PRESTATAIRE', [Role::DEMANDEUR]],
        ];

        $utilisateurs = [];
        foreach ($personnes as [$email, $matricule, $nom, $prenom, $categorie, $libellesRoles]) {
            $user = User::firstOrCreate(['email' => $email], User::factory()->raw([
                'email' => $email,
                'matricule' => $matricule,
                'nom' => $nom,
                'prenom' => $prenom,
                'identifiant' => $matricule ?? strtolower($prenom.'.'.$nom),
                'categorie' => $categorie,
                // Les agents s'authentifieront via LDAP : pas de mot de passe local.
                'password' => $categorie === 'AGENT' ? null : 'password',
            ]));
            $user->forceFill(['service_id' => $devIt->id])->save();
            $user->roles()->syncWithoutDetaching(collect($libellesRoles)->map(fn ($l) => $roles[$l]));
            $utilisateurs[$email] = $user;
        }

        $devIt->forceFill(['chef_id' => $utilisateurs['koffi.aya@cnps.ci']->id])->save();
        $dev->forceFill(['responsable_id' => $utilisateurs['konan.yao@cnps.ci']->id])->save();
    }
}
