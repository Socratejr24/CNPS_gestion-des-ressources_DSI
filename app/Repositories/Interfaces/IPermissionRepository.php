<?php

namespace App\Repositories\Interfaces;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Permission;

interface IPermissionRepository
{
    public function getAll(): Collection;
    public function getById(int $id): ?Permission;
    public function getBySlug(string $slug): ?Permission;
    public function create(array $data): Permission;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
     public function toggleStatus(int $id, bool $status): bool;
    public function search(string $search): Collection;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
}