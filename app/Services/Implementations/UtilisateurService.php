<?php

namespace App\Services\Implementations;

use App\Enums\CategorieUtilisateur;
use App\Exceptions\RegleMetierException;
use App\Models\Departement;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\Interfaces\IUtilisateurService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UtilisateurService implements IUtilisateurService
{
    private const RELATIONS = ['service.departement.structure', 'roles'];

    public function lister(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with(self::RELATIONS)
            ->when($filters['search'] ?? null, function ($q, string $search) {
                $term = '%'.$search.'%';
                $q->where(fn ($q) => $q->where('nom', 'LIKE', $term)
                    ->orWhere('prenom', 'LIKE', $term)
                    ->orWhere('email', 'LIKE', $term)
                    ->orWhere('matricule', 'LIKE', $term)
                    ->orWhere('identifiant', 'LIKE', $term));
            })
            ->when($filters['categorie'] ?? null, fn ($q, $c) => $q->where('categorie', $c))
            ->when($filters['service_id'] ?? null, fn ($q, $id) => $q->where('service_id', $id))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->when($filters['sans_affectation'] ?? false, fn ($q) => $q->whereNull('service_id'))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate($perPage);
    }

    public function charger(User $utilisateur): User
    {
        return $utilisateur->load(self::RELATIONS);
    }

    public function creerPrestataire(array $data): User
    {
        $data['categorie'] = CategorieUtilisateur::PRESTATAIRE;

        return User::create($data)->refresh()->load(self::RELATIONS);
    }

    public function modifier(User $utilisateur, array $data): User
    {
        // Statut, affectation et rôles passent par des opérations dédiées.
        unset($data['is_active'], $data['categorie']);

        // Les agents s'authentifient via LDAP : pas de mot de passe local.
        if ($utilisateur->categorie === CategorieUtilisateur::AGENT) {
            unset($data['password']);
        }

        if (array_key_exists('password', $data) && blank($data['password'])) {
            unset($data['password']);
        }

        $utilisateur->update($data);

        return $utilisateur->refresh()->load(self::RELATIONS);
    }

    public function changerStatut(User $utilisateur, bool $actif): User
    {
        if (! $actif) {
            $responsabilites = collect()
                ->merge(Service::where('chef_id', $utilisateur->id)->pluck('libelle')
                    ->map(fn ($l) => "chef du service « {$l} »"))
                ->merge(Departement::where('responsable_id', $utilisateur->id)->pluck('libelle')
                    ->map(fn ($l) => "responsable du département « {$l} »"));

            if ($responsabilites->isNotEmpty()) {
                throw new RegleMetierException(
                    'Impossible de désactiver ce compte : l\'utilisateur est '
                    .$responsabilites->implode(', ')
                    .'. Désignez d\'abord un remplaçant.'
                );
            }
        }

        $utilisateur->update(['is_active' => $actif]);

        return $utilisateur->refresh()->load(self::RELATIONS);
    }

    public function attribuerRoles(User $utilisateur, array $roleIds): User
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));

        $rolesActifs = Role::whereIn('id', $roleIds)->where('is_active', true)->pluck('id')->all();
        $invalides = array_diff($roleIds, $rolesActifs);

        if ($invalides !== []) {
            throw new RegleMetierException(
                'Rôles inexistants ou désactivés : '.implode(', ', $invalides).'.'
            );
        }

        $utilisateur->roles()->sync($rolesActifs);

        return $utilisateur->refresh()->load(self::RELATIONS);
    }
}
