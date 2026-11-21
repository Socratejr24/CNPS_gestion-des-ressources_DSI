<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\IRoleService;
use App\Repositories\Interfaces\IRoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Role;

class RoleService implements IRoleService
{
    public function __construct(
        private IRoleRepository $roleRepository
    ) {}

    public function getAllRoles(): Collection
    {
        return $this->roleRepository->getAll();
    }

    public function getRole(int $id): ?Role
    {
        return $this->roleRepository->getById($id);
    }

    public function getRoleByLibelle(string $libelle): ?Role
    {
        return $this->roleRepository->getByLibelle($libelle);
    }

    public function createRole(array $data): Role
    {
        return $this->roleRepository->create($data);
    }

    public function updateRole(int $id, array $data): bool
    {
        return $this->roleRepository->update($id, $data);
    }

    public function deleteRole(int $id): bool
    {
        return $this->roleRepository->delete($id);
    }

    public function toggleRoleStatus(int $id, bool $status): bool
    {
        return $this->roleRepository->toggleStatus($id, $status);
    }

    public function searchRoles(string $search): Collection
    {
        return $this->roleRepository->search($search);
    }

    public function paginateRoles(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($perPage, $filters);
    }

  // Gestion des permissions

        public function assignPermissionsToRole(int $roleId, array $permissionIds): array
    {
        // Nettoyage : entiers uniques uniquement
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        return $this->roleRepository->assignPermissions($roleId, $permissionIds);
    }


   public function removePermissionFromRole(int $roleId, int $permissionId): array
    {
        return $this->roleRepository->removePermission($roleId, $permissionId);
    }

    public function getRolePermissions(int $roleId): Collection
    {
        return $this->roleRepository->getPermissions($roleId);
    }

    public function syncRolePermissions(int $roleId, array $permissionIds): ?Role
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        return $this->roleRepository->syncPermissions($roleId, $permissionIds);
    }


}