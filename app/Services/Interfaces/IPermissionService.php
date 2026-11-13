<?php

namespace App\Services\Interfaces;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Permission;

interface IPermissionService
{
    public function getAllPermissions(): Collection;
    public function getPermission(int $id): ?Permission;
    public function getPermissionBySlug(string $slug): ?Permission;
    public function createPermission(array $data): Permission;
    public function updatePermission(int $id, array $data): bool;
    public function deletePermission(int $id): bool;
    public function togglePermissionStatus(int $id, bool $status): bool;
    public function searchPermissions(string $search): Collection;
    public function paginatePermissions(int $perPage = 10, array $filters = []): LengthAwarePaginator;
}