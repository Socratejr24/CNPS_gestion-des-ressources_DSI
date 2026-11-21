<?php

namespace App\Http\Controllers;

use App\Enums\DemandeStatut;

class DashboardController extends Controller
{
    /**
     * Tableau de bord du demandeur (SFD §14).
     *
     * ⚠️ Les chiffres sont encore fictifs : ils seront calculés à partir des demandes
     * de l'utilisateur quand le module Demandes sera développé.
     */
    public function index()
    {
        $user = current_user();

        // Indicateurs regroupés par étape du cycle de vie (SFD §11)
        $kpis = [
            ['label' => 'En validation', 'valeur' => 3, 'aide' => 'N+1 ou N+2', 'couleur' => 'orange', 'icone' => 'clock'],
            ['label' => 'En traitement', 'valeur' => 5, 'aide' => 'Chez le receveur', 'couleur' => 'blue', 'icone' => 'refresh'],
            ['label' => 'À confirmer', 'valeur' => 2, 'aide' => 'Réception à confirmer', 'couleur' => 'violet', 'icone' => 'inbox'],
            ['label' => 'Clôturées', 'valeur' => 14, 'aide' => 'Depuis le début', 'couleur' => 'green', 'icone' => 'check-circle'],
        ];

        $chartStatuts = [
            'labels' => ['En validation', 'En traitement', 'À confirmer', 'Clôturées', 'Rejetées'],
            'series' => [3, 5, 2, 14, 1],
            'colors' => ['#DE7C0F', '#27357E', '#7C4DBC', '#088E39', '#C8102E'],
        ];

        $chartEvolution = [
            'labels' => ['Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.'],
            'series' => ['name' => 'Demandes créées', 'data' => [3, 5, 4, 7, 6, 9]],
        ];

        $recentesDemandes = collect([
            ['DEM-2026-0142', 'Poste de travail', 2, DemandeStatut::EN_ATTENTE_N1],
            ['DEM-2026-0141', 'Accès serveur applicatif', 4, DemandeStatut::EN_COURS_TRAITEMENT],
            ['DEM-2026-0140', 'Licence logicielle', 6, DemandeStatut::EN_ATTENTE_CONFIRMATION],
            ['DEM-2026-0139', 'Nom de domaine', 9, DemandeStatut::CLOTUREE],
            ['DEM-2026-0138', 'Espace de stockage', 12, DemandeStatut::REJETEE],
        ])->map(fn ($d) => (object) [
            'reference' => $d[0],
            'objet' => $d[1],
            'date' => now()->subDays($d[2]),
            'statut' => $d[3],
        ]);

        $recentesNotifs = collect([
            (object) ['type' => 'green', 'icone' => 'check-circle', 'titre' => 'DEM-2026-0140 : ressources mises à disposition', 'temps' => 'Il y a 10 min'],
            (object) ['type' => 'orange', 'icone' => 'alert-circle', 'titre' => 'Informations complémentaires demandées sur DEM-2026-0141', 'temps' => 'Il y a 25 min'],
            (object) ['type' => 'blue', 'icone' => 'info', 'titre' => 'DEM-2026-0139 clôturée', 'temps' => 'Il y a 1 h'],
        ]);

        return view('dashboard.index', compact(
            'user',
            'kpis',
            'chartStatuts',
            'chartEvolution',
            'recentesDemandes',
            'recentesNotifs',
        ));
    }
}
