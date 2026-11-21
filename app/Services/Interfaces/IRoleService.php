<?php

namespace App\Services\Interfaces;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Role;

interface IRoleService
{
    public function getAllRoles(): Collection;
    public function getRole(int $id): ?Role;
    public function createRole(array $data): Role;
    public function updateRole(int $id, array $data): bool;
    public function deleteRole(int $id): bool;
    public function getRoleByLibelle(string $libelle): ?Role;
    public function toggleRoleStatus(int $id, bool $status): bool;
    public function searchRoles(string $search): Collection;
    public function paginateRoles(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    // Gestion des permissions
    public function assignPermissionsToRole(int $roleId, array $permissionIds): array;
    public function removePermissionFromRole(int $roleId, int $permissionId): array;
    public function getRolePermissions(int $roleId): Collection;
    public function syncRolePermissions(int $roleId, array $permissionIds): ?Role;
}