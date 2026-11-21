<?php

namespace App\Services\Interfaces;

use App\Models\Application;
use App\Models\Ressource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Référentiels des demandes (SFD §4 et §5), gérés par l'administrateur.
 */
interface IReferentielService
{
    // Applications ------------------------------------------------------

    /** Filtres : q (recherche), statut (actif|inactif). */
    public function listerApplications(array $filtres = [], int $parPage = 15): LengthAwarePaginator;

    public function creerApplication(array $data): Application;

    public function modifierApplication(Application $application, array $data): Application;

    public function changerStatutApplication(Application $application, bool $actif): Application;

    /** Applications proposées dans le formulaire de demande. */
    public function applicationsActives(): Collection;

    // Ressources --------------------------------------------------------

    /** Filtres : q (recherche), categorie, statut (actif|inactif). */
    public function listerRessources(array $filtres = [], int $parPage = 15): LengthAwarePaginator;

    public function creerRessource(array $data): Ressource;

    public function modifierRessource(Ressource $ressource, array $data): Ressource;

    public function changerStatutRessource(Ressource $ressource, bool $actif): Ressource;

    /** Ressources proposées dans le formulaire de demande, triées par catégorie. */
    public function ressourcesActives(): Collection;

    /** Catégories déjà utilisées (pour les suggestions et les filtres). */
    public function categories(): Collection;
}
