<?php

namespace App\Services\Implementations;

use App\Models\Application;
use App\Models\Ressource;
use App\Services\Interfaces\IReferentielService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReferentielService implements IReferentielService
{
    // ==================================================================
    // Applications
    // ==================================================================

    public function listerApplications(array $filtres = [], int $parPage = 15): LengthAwarePaginator
    {
        return Application::query()
            ->when($filtres['q'] ?? null, fn (Builder $q, string $terme) => $this->rechercher($q, $terme, ['reference', 'nom', 'description']))
            ->when($filtres['statut'] ?? null, fn (Builder $q, string $statut) => $q->where('is_active', $statut === 'actif'))
            ->orderBy('nom')
            ->paginate($parPage)
            ->withQueryString();
    }

    public function creerApplication(array $data): Application
    {
        return Application::create($data + ['is_active' => true])->refresh();
    }

    public function modifierApplication(Application $application, array $data): Application
    {
        unset($data['is_active']); // passe par changerStatutApplication()
        $application->update($data);

        return $application->refresh();
    }

    public function changerStatutApplication(Application $application, bool $actif): Application
    {
        // Les demandes existantes gardent leur application : la désactivation
        // la retire seulement des choix proposés pour les nouvelles demandes.
        $application->update(['is_active' => $actif]);

        return $application->refresh();
    }

    public function applicationsActives(): Collection
    {
        return Application::where('is_active', true)->orderBy('nom')->get();
    }

    // ==================================================================
    // Ressources
    // ==================================================================

    public function listerRessources(array $filtres = [], int $parPage = 15): LengthAwarePaginator
    {
        return Ressource::query()
            ->when($filtres['q'] ?? null, fn (Builder $q, string $terme) => $this->rechercher($q, $terme, ['reference', 'designation', 'description']))
            ->when($filtres['categorie'] ?? null, fn (Builder $q, string $categorie) => $q->where('categorie', $categorie))
            ->when($filtres['statut'] ?? null, fn (Builder $q, string $statut) => $q->where('is_active', $statut === 'actif'))
            ->orderBy('categorie')
            ->orderBy('designation')
            ->paginate($parPage)
            ->withQueryString();
    }

    public function creerRessource(array $data): Ressource
    {
        return Ressource::create($data + ['is_active' => true])->refresh();
    }

    public function modifierRessource(Ressource $ressource, array $data): Ressource
    {
        unset($data['is_active']);
        $ressource->update($data);

        return $ressource->refresh();
    }

    public function changerStatutRessource(Ressource $ressource, bool $actif): Ressource
    {
        $ressource->update(['is_active' => $actif]);

        return $ressource->refresh();
    }

    public function ressourcesActives(): Collection
    {
        return Ressource::where('is_active', true)->orderBy('categorie')->orderBy('designation')->get();
    }

    public function categories(): Collection
    {
        return Ressource::query()->distinct()->orderBy('categorie')->pluck('categorie');
    }

    // ==================================================================

    private function rechercher(Builder $requete, string $terme, array $colonnes): Builder
    {
        $motif = '%'.$terme.'%';

        return $requete->where(function (Builder $q) use ($colonnes, $motif) {
            foreach ($colonnes as $colonne) {
                $q->orWhere($colonne, 'LIKE', $motif);
            }
        });
    }
}
