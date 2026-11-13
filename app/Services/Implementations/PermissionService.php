<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\IPermissionService;
use App\Repositories\Interfaces\IPermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Permission;

class PermissionService implements IPermissionService
{
    public function __construct(
        private IPermissionRepository $permissionRepository
    ) {}

    public function getAllPermissions(): Collection
    {
        return $this->permissionRepository->getAll();
    }

    public function getPermission(int $id): ?Permission
    {
        return $this->permissionRepository->getById($id);
    }

    public function getPermissionBySlug(string $slug): ?Permission
    {
        return $this->permissionRepository->getBySlug($slug);
    }

    public function createPermission(array $data): Permission
    {
        return $this->permissionRepository->create($data);
    }

    public function updatePermission(int $id, array $data): bool
    {
        return $this->permissionRepository->update($id, $data);
    }

    public function deletePermission(int $id): bool
    {
        return $this->permissionRepository->delete($id);
    }

    public function togglePermissionStatus(int $id, bool $status): bool
    {
        return $this->permissionRepository->toggleStatus($id, $status);
    }

    public function searchPermissions(string $search): Collection
    {
        return $this->permissionRepository->search($search);
    }

    public function paginatePermissions(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->permissionRepository->paginate($perPage, $filters);
    }
}