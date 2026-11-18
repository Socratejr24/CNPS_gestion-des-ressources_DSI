<?php

namespace App\Services\Implementations;

use App\Exceptions\RegleMetierException;
use App\Models\Departement;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrganisationService implements IOrganisationService
{
    // ==================================================================
    // Structures
    // ==================================================================

    public function listerStructures(?bool $actif = null): Collection
    {
        return Structure::query()
            ->when($actif !== null, fn ($q) => $q->where('is_active', $actif))
            ->withCount('departements')
            ->orderBy('libelle')
            ->get();
    }

    public function creerStructure(array $data): Structure
    {
        return Structure::create($data)->refresh();
    }

    public function modifierStructure(Structure $structure, array $data): Structure
    {
        unset($data['is_active']); // passe par changerStatutStructure()
        $structure->update($data);

        return $structure->refresh();
    }

    public function changerStatutStructure(Structure $structure, bool $actif): Structure
    {
        if (! $actif && $structure->departements()->where('is_active', true)->exists()) {
            throw new RegleMetierException(
                'Impossible de désactiver la structure : elle contient des départements actifs.'
            );
        }

        $structure->update(['is_active' => $actif]);

        return $structure->refresh();
    }

    // ==================================================================
    // Départements
    // ==================================================================

    public function listerDepartements(?int $structureId = null, ?bool $actif = null): Collection
    {
        return Departement::query()
            ->with(['structure', 'responsable'])
            ->withCount('services')
            ->when($structureId, fn ($q) => $q->where('structure_id', $structureId))
            ->when($actif !== null, fn ($q) => $q->where('is_active', $actif))
            ->orderBy('libelle')
            ->get();
    }

    public function creerDepartement(array $data): Departement
    {
        $this->exigerActif(Structure::findOrFail($data['structure_id']), 'La structure');

        return Departement::create($data)->refresh()->load(['structure', 'responsable']);
    }

    public function modifierDepartement(Departement $departement, array $data): Departement
    {
        unset($data['is_active']);

        if (isset($data['structure_id']) && (int) $data['structure_id'] !== $departement->structure_id) {
            $this->exigerActif(Structure::findOrFail($data['structure_id']), 'La structure');
        }

        $departement->update($data);

        return $departement->refresh()->load(['structure', 'responsable']);
    }

    public function changerStatutDepartement(Departement $departement, bool $actif): Departement
    {
        if ($actif) {
            $this->exigerActif($departement->structure, 'La structure parente');
        } elseif ($departement->services()->where('is_active', true)->exists()) {
            throw new RegleMetierException(
                'Impossible de désactiver le département : il contient des services actifs.'
            );
        }

        $departement->update(['is_active' => $actif]);

        return $departement->refresh()->load(['structure', 'responsable']);
    }

    /**
     * Désigne (ou retire avec null) le N+2 (RG-06, RG-16).
     * Le responsable doit appartenir à un service du département pour que
     * le circuit de validation reste cohérent.
     */
    public function designerResponsable(Departement $departement, ?User $utilisateur): Departement
    {
        if ($utilisateur !== null) {
            $this->exigerActif($departement, 'Le département');
            $this->exigerUtilisateurActif($utilisateur);

            if ($utilisateur->service?->departement_id !== $departement->id) {
                throw new RegleMetierException(
                    'Le responsable doit être affecté à un service de ce département.'
                );
            }
        }

        $departement->responsable_id = $utilisateur?->id;
        $departement->save();

        return $departement->refresh()->load(['structure', 'responsable']);
    }

    // ==================================================================
    // Services
    // ==================================================================

    public function listerServices(?int $departementId = null, ?bool $actif = null): Collection
    {
        return Service::query()
            ->with(['departement.structure', 'chef'])
            ->withCount('utilisateurs')
            ->when($departementId, fn ($q) => $q->where('departement_id', $departementId))
            ->when($actif !== null, fn ($q) => $q->where('is_active', $actif))
            ->orderBy('libelle')
            ->get();
    }

    public function creerService(array $data): Service
    {
        $this->exigerActif(Departement::findOrFail($data['departement_id']), 'Le département');

        return Service::create($data)->refresh()->load(['departement.structure', 'chef']);
    }

    public function modifierService(Service $service, array $data): Service
    {
        unset($data['is_active']);

        if (isset($data['departement_id']) && (int) $data['departement_id'] !== $service->departement_id) {
            $this->exigerActif(Departement::findOrFail($data['departement_id']), 'Le département');

            // Le responsable de l'ancien département sortirait de son département.
            $responsableId = $service->departement->responsable_id;
            if ($responsableId && $service->utilisateurs()->whereKey($responsableId)->exists()) {
                throw new RegleMetierException(
                    'Impossible de déplacer ce service : il contient le responsable de son département actuel.'
                );
            }
        }

        $service->update($data);

        return $service->refresh()->load(['departement.structure', 'chef']);
    }

    public function changerStatutService(Service $service, bool $actif): Service
    {
        if ($actif) {
            $this->exigerActif($service->departement, 'Le département parent');
        } elseif ($service->utilisateurs()->where('is_active', true)->exists()) {
            throw new RegleMetierException(
                'Impossible de désactiver le service : des utilisateurs actifs y sont affectés.'
            );
        }

        $service->update(['is_active' => $actif]);

        return $service->refresh()->load(['departement.structure', 'chef']);
    }

    /**
     * Désigne (ou retire avec null) le N+1 (RG-06, RG-15).
     * Le chef doit être affecté au service.
     */
    public function designerChef(Service $service, ?User $utilisateur): Service
    {
        if ($utilisateur !== null) {
            $this->exigerActif($service, 'Le service');
            $this->exigerUtilisateurActif($utilisateur);

            if ($utilisateur->service_id !== $service->id) {
                throw new RegleMetierException('Le chef de service doit être affecté à ce service.');
            }
        }

        $service->chef_id = $utilisateur?->id;
        $service->save();

        return $service->refresh()->load(['departement.structure', 'chef']);
    }

    // ==================================================================
    // Affectations
    // ==================================================================

    /**
     * Affecte (ou désaffecte avec null) un utilisateur à son service principal (RG-03, RG-06).
     *
     * Refuse tout changement qui rendrait incohérente une responsabilité N+1 / N+2
     * existante : l'administrateur doit d'abord retirer la responsabilité.
     */
    public function affecterUtilisateur(User $utilisateur, ?Service $service): User
    {
        if ($service !== null) {
            $this->exigerActif($service, 'Le service');
        }

        return DB::transaction(function () use ($utilisateur, $service) {
            $ancienService = $utilisateur->service;

            if ($ancienService
                && $ancienService->id !== $service?->id
                && $ancienService->chef_id === $utilisateur->id) {
                throw new RegleMetierException(
                    "L'utilisateur est chef du service « {$ancienService->libelle} » : retirez d'abord cette responsabilité."
                );
            }

            $departementDirige = Departement::where('responsable_id', $utilisateur->id)->first();
            if ($departementDirige && $service?->departement_id !== $departementDirige->id) {
                throw new RegleMetierException(
                    "L'utilisateur est responsable du département « {$departementDirige->libelle} » : retirez d'abord cette responsabilité."
                );
            }

            $utilisateur->service_id = $service?->id;
            $utilisateur->save();

            return $utilisateur->refresh()->load(['service.departement.structure', 'roles']);
        });
    }

    // ==================================================================
    // Helpers
    // ==================================================================

    private function exigerActif(Structure|Departement|Service $entite, string $libelle): void
    {
        if (! $entite->is_active) {
            throw new RegleMetierException("{$libelle} « {$entite->libelle} » est désactivé(e).");
        }
    }

    private function exigerUtilisateurActif(User $utilisateur): void
    {
        if (! $utilisateur->is_active) {
            throw new RegleMetierException("Le compte de {$utilisateur->nomComplet()} est désactivé.");
        }
    }
}
