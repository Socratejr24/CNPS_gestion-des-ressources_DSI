<?php

namespace App\Services\Interfaces;

use App\Models\Departement;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Gestion de l'organisation de la DSI (SFD §2) et des affectations.
 * Toutes les opérations sont réservées à l'administrateur (RG-05, RG-06).
 */
interface IOrganisationService
{
    // Structures
    public function listerStructures(?bool $actif = null): Collection;

    public function creerStructure(array $data): Structure;

    public function modifierStructure(Structure $structure, array $data): Structure;

    public function changerStatutStructure(Structure $structure, bool $actif): Structure;

    // Départements
    public function listerDepartements(?int $structureId = null, ?bool $actif = null): Collection;

    public function creerDepartement(array $data): Departement;

    public function modifierDepartement(Departement $departement, array $data): Departement;

    public function changerStatutDepartement(Departement $departement, bool $actif): Departement;

    public function designerResponsable(Departement $departement, ?User $utilisateur): Departement;

    // Services
    public function listerServices(?int $departementId = null, ?bool $actif = null): Collection;

    public function creerService(array $data): Service;

    public function modifierService(Service $service, array $data): Service;

    public function changerStatutService(Service $service, bool $actif): Service;

    public function designerChef(Service $service, ?User $utilisateur): Service;

    // Affectations
    public function affecterUtilisateur(User $utilisateur, ?Service $service): User;
}
