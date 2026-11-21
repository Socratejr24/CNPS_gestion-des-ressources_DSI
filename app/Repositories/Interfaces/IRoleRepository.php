<?php

namespace App\Repositories\Interfaces;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Role;

interface IRoleRepository
{

       
    public function getAll(): Collection;
    public function getById(int $id): ?Role;
    public function create(array $data): Role;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByLibelle(string $libelle): ?Role;
    public function toggleStatus(int $id, bool $status): bool;
    public function search(string $search): Collection;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;

 //  Nouvelles méthodes : gestion des permissions
    public function assignPermissions(int $roleId, array $permissionIds): array;
    public function removePermission(int $roleId, int $permissionId): array;
    public function getPermissions(int $roleId): Collection;

    /** Remplace l'ensemble des permissions du rôle par celles données (ids inexistants ignorés). */
    public function syncPermissions(int $roleId, array $permissionIds): ?Role;

}

