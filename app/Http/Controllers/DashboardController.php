<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard principal (Demandeur pour l'instant).
     * ⚠️ Toutes les données sont en dur — à remplacer par des requêtes Eloquent.
     */
    public function index()
    {
        // ============================================
        // Utilisateur
        // ============================================
        $user = current_user();

        // ============================================
        // KPI (chiffres clés)
        // ============================================
        $kpis = [
            'en_cours'   => 12,
            'en_attente' => 3,
            'validees'   => 8,
            'cloturees'  => 1,
        ];

        // ============================================
        // Répartition par statut (donut)
        // ============================================
        $chartStatuts = [
            'labels' => ['En cours', 'En attente', 'Validées', 'Clôturées'],
            'series' => [12, 3, 8, 1],
            'colors' => ['#27357e', '#f6d01d', '#088e39', '#6b7190'],
        ];

        // ============================================
        // Évolution sur 6 mois (ligne)
        // ============================================
        $chartEvolution = [
            'labels' => ['Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.'],
            'series' => [
                'name' => 'Demandes créées',
                'data' => [3, 5, 4, 7, 6, 9],
            ],
        ];

        // ============================================
        // Top 5 ressources demandées (barres)
        // ============================================
        $chartTopRessources = [
            'labels' => [
                'Poste de travail',
                'Licence logicielle',
                'Nom de domaine',
                'Accès serveur',
                'Espace de stockage',
            ],
            'series' => [
                'name' => 'Demandes',
                'data' => [8, 6, 5, 4, 3],
            ],
        ];

        // ============================================
        // 5 dernières demandes
        // ============================================
        $recentesDemandes = collect([
            (object) [
                'reference' => 'DEM-0142',
                'objet'     => 'Poste de travail',
                'date'      => '2026-09-29',
                'statut'    => 'attente',
                'statut_label' => 'En attente N+1',
            ],
            (object) [
                'reference' => 'DEM-0141',
                'objet'     => 'Accès serveur applicatif',
                'date'      => '2026-09-28',
                'statut'    => 'cours',
                'statut_label' => 'En cours',
            ],
            (object) [
                'reference' => 'DEM-0140',
                'objet'     => 'Licence logicielle',
                'date'      => '2026-09-27',
                'statut'    => 'validee',
                'statut_label' => 'Validée',
            ],
            (object) [
                'reference' => 'DEM-0139',
                'objet'     => 'Nom de domaine',
                'date'      => '2026-09-25',
                'statut'    => 'cloturee',
                'statut_label' => 'Clôturée',
            ],
            (object) [
                'reference' => 'DEM-0138',
                'objet'     => 'Espace de stockage',
                'date'      => '2026-09-23',
                'statut'    => 'rejetee',
                'statut_label' => 'Rejetée',
            ],
        ]);

        // ============================================
        // 3 dernières notifications
        // ============================================
        $recentesNotifs = collect([
            (object) [
                'id'    => 1,
                'type'  => 'blue',
                'icone' => 'check-circle',
                'titre' => 'Demande DEM-0140 validée',
                'temps' => 'Il y a 10 min',
                'url'   => '/demandes/DEM-0140',
            ],
            (object) [
                'id'    => 2,
                'type'  => 'orange',
                'icone' => 'alert-circle',
                'titre' => 'Information demandée sur DEM-0141',
                'temps' => 'Il y a 25 min',
                'url'   => '/demandes/DEM-0141',
            ],
            (object) [
                'id'    => 3,
                'type'  => 'blue',
                'icone' => 'check-circle',
                'titre' => 'Demande DEM-0139 clôturée',
                'temps' => 'Il y a 1 h',
                'url'   => '/demandes/DEM-0139',
            ],
        ]);

        // ============================================
        // Actions rapides
        // ============================================
        $quickActions = [
            [
                'icon'  => 'file-text',
                'label' => 'Mes demandes',
                'desc'  => 'Consulter mes demandes',
                'url'   => '/demandes/mes-demandes',
                'color' => 'blue',
            ],
            [
                'icon'  => 'plus-circle',
                'label' => 'Nouvelle demande',
                'desc'  => 'Créer une demande',
                'url'   => '/demandes/nouvelle',
                'color' => 'orange',
            ],
            [
                'icon'  => 'monitor',
                'label' => 'Mes applications',
                'desc'  => 'Gérer mes applications',
                'url'   => '/applications/mes-applications',
                'color' => 'green',
            ],
            [
                'icon'  => 'box',
                'label' => 'Ressources',
                'desc'  => 'Voir le catalogue',
                'url'   => '/ressources',
                'color' => 'blue',
            ],
        ];

        return view('dashboard.index', compact(
            'user',
            'kpis',
            'chartStatuts',
            'chartEvolution',
            'chartTopRessources',
            'recentesDemandes',
            'recentesNotifs',
            'quickActions',
        ));
    }
}