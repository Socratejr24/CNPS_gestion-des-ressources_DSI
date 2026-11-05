<?php

namespace App\Http\Controllers;

use App\Enums\DemandeStatut;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DemandeController extends Controller
{
    /**
     * Liste des demandes du demandeur (mes demandes).
     */
    public function index(Request $request)
    {
        // ============================================
        // 1. Données fictives (à remplacer par BDD plus tard)
        // ============================================
        $allDemandes = $this->getFakeDemandes();

        // ============================================
        // 2. Filtrage
        // ============================================
        $query = $request->input('q');
        $statutFiltre = $request->input('statut');
        $typeFiltre = $request->input('type');

        $demandes = $allDemandes;

        // Recherche (objet ou référence)
        if ($query) {
            $demandes = $demandes->filter(function ($d) use ($query) {
                return stripos($d->objet, $query) !== false
                    || stripos($d->reference, $query) !== false;
            });
        }

        // Filtre statut
        if ($statutFiltre) {
            $demandes = $demandes->filter(function ($d) use ($statutFiltre) {
                return $d->statut === $statutFiltre;
            });
        }

        // Filtre type
        if ($typeFiltre) {
            $demandes = $demandes->filter(function ($d) use ($typeFiltre) {
                return $d->type === $typeFiltre;
            });
        }

        // ============================================
        // 3. Pagination
        // ============================================
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $demandes->count();

        $items = $demandes
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $paginated = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        // ============================================
        // 4. Données pour le formulaire de création
        // ============================================
        $applications = ['CNPS-Web', 'GED', 'Portail Agent'];
        $ressources = ['Poste de travail', 'Licence logicielle', 'Accès VPN'];
        $destinations = ['Département DSI', 'Service Développement', 'M. Koffi'];

        // ============================================
        // 5. Vue
        // ============================================
        return view('demandes.index', [
            'demandes' => $paginated,
            'statuts' => DemandeStatut::cases(),
            'statutFiltre' => $statutFiltre,
            'typeFiltre' => $typeFiltre,
            'query' => $query,
            'applications' => $applications,
            'ressources' => $ressources,
            'destinations' => $destinations,
        ]);
    }


    /**
     * Génère des données fictives pour tester.
     * ⚠️ À REMPLACER par une vraie requête Eloquent plus tard.
     */
    private function getFakeDemandes(): Collection
    {
        $types = ['Générale', 'Application'];
        $destinations = [
            'Service IT',
            'Service Infrastructure',
            'Département DSI',
            'Service Développement',
            'Utilisateur : M. Koffi',
        ];
        $statuts = DemandeStatut::cases();

        $demandes = collect();

        for ($i = 142; $i >= 119; $i--) {
            $statut = $statuts[$i % count($statuts)];
            $date = now()->subDays(($i - 119) * 2);
            $type = $types[$i % 2];

            $demandes->push((object) [
                'reference'     => 'DEM-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'objet'         => $this->getObjetFake($i),
                'type'          => $type,
                'date'          => $date,
                'statut'        => $statut->value,
                'statut_obj'    => $statut,
                'destination'   => $destinations[$i % count($destinations)],
            ]);
        }

        return $demandes;
    }

    private function getObjetFake(int $i): string
    {
        $objets = [
            'Poste de travail',
            'Accès serveur applicatif',
            'Licence logicielle',
            'Nom de domaine',
            'Espace de stockage',
            'Souris sans fil',
            'Écran 27 pouces',
            'Clavier mécanique',
            'Accès VPN',
            'Compte administrateur',
            'Base de données PostgreSQL',
            'Certificat SSL',
            'Accès Git',
            'Serveur de test',
            'Outil de monitoring',
            'Environnement de staging',
            'Configuration réseau',
            'Firewall dédié',
            'Sauvegarde automatique',
            'Stockage cloud',
            'Téléphone IP',
            'Casque audio',
            'Webcam HD',
            'Disque dur SSD',
        ];

        return $objets[$i % count($objets)];
    }
}