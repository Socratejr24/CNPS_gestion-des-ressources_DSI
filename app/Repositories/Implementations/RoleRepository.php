<?php

namespace App\Repositories\Implementations;
use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Interfaces\IRoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleRepository implements IRoleRepository
{
    public function getAll(): Collection
    {
        return Role::all();
    }

    public function getById(int $id): ?Role
    {
        return Role::find($id);
    }

    public function getByLibelle(string $libelle): ?Role
    {
        return Role::where('libelle', $libelle)->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $role = Role::find($id);
        return $role ? $role->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $role = Role::find($id);
        return $role ? $role->delete() : false;
    }

    /**
     * Active ou désactive un rôle.
     */
    public function toggleStatus(int $id, bool $status): bool
    {
        $role = Role::find($id);
        if (!$role) {
            return false;
        }
        return $role->update(['is_active' => $status]);
    }

    /**
     * Recherche textuelle sur libelle + description.
     */
    public function search(string $search): Collection
    {
        $term = '%' . $search . '%';

        return Role::query()
            ->where(function ($q) use ($term) {
                $q->where('libelle', 'LIKE', $term)
                  ->orWhere('description', 'LIKE', $term);
            })
            ->get();
    }

    /**
     * Pagination avec filtres.
     *
     * Filtres supportés :
     *  - is_active (bool)
     *  - search (string)
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Role::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('libelle', 'LIKE', $term)
                  ->orWhere('description', 'LIKE', $term);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }


           // Gestion des permissions pour un rôle

 /**
     * Attache plusieurs permissions à un rôle (sans écraser les existantes).
     *
     * @param  int   $roleId
     * @param  int[] $permissionIds
     * @return array{attached: int[], already_attached: int[], invalid: int[], role: ?Role}
     */

    public function assignPermissions(int $roleId, array $permissionIds): array
    {
        $role = Role::find($roleId);
        if (!$role) {
            return ['attached' => [], 'already_attached' => [], 'invalid' => $permissionIds, 'role' => null];
        }

        // 1. Séparer valides / invalides (ids inexistants en BDD)
        $existing = Permission::whereIn('id', $permissionIds)->pluck('id')->all();
        $invalid  = array_values(array_diff($permissionIds, $existing));

        // 2. Attacher seulement les nouvelles (syncWithoutDetaching = sans doublon, sans supprimer)
        $before = $role->permissions()->pluck('permissions.id')->all();
        $role->permissions()->syncWithoutDetaching($existing);
        $after  = $role->permissions()->pluck('permissions.id')->all();

        $attached          = array_values(array_diff($after, $before));
        $alreadyAttached   = array_values(array_intersect($existing, $before));

        return [
            'attached'         => $attached,
            'already_attached' => $alreadyAttached,
            'invalid'          => $invalid,
            'role'             => $role->fresh('permissions'),
        ];
    }
 /**
     * Détache une permission d'un rôle.
     *
     * @return array{removed: bool, role: ?Role}
     */
    public function removePermission(int $roleId, int $permissionId): array
    {
        $role = Role::find($roleId);
        if (!$role) {
            return ['removed' => false, 'role' => null];
        }

        // Vérifie que la permission était bien attachée
        $hadPermission = $role->permissions()->where('permissions.id', $permissionId)->exists();
        if (!$hadPermission) {
            return ['removed' => false, 'role' => $role->fresh('permissions')];
        }

        $role->permissions()->detach($permissionId);

        return ['removed' => true, 'role' => $role->fresh('permissions')];
    }
/**
     * Récupère les permissions d'un rôle (vide si rôle inexistant).
     */
    public function getPermissions(int $roleId): Collection
    {
        $role = Role::find($roleId);
        return $role ? $role->permissions()->get() : new Collection();
    }

}