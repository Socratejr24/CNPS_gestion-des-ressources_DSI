<?php

namespace App\Repositories\Implementations;

use App\Models\Permission;
use App\Repositories\Interfaces\IPermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionRepository implements IPermissionRepository
{
    public function getAll(): Collection
    {
        return Permission::all();
    }

    public function getById(int $id): ?Permission
    {
        return Permission::find($id);
    }

    public function getBySlug(string $slug): ?Permission
    {
        return Permission::where('slug', $slug)->first();
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $permission = Permission::find($id);
        return $permission ? $permission->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $permission = Permission::find($id);
        return $permission ? $permission->delete() : false;
    }

    public function toggleStatus(int $id, bool $status): bool
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return false;
        }
        return $permission->update(['is_active' => $status]);
    }

    public function search(string $search): Collection
    {
        $term = '%' . $search . '%';

        return Permission::query()
            ->where(function ($q) use ($term) {
                $q->where('libelle', 'LIKE', $term)
                  ->orWhere('slug', 'LIKE', $term)
                  ->orWhere('description', 'LIKE', $term);
            })
            ->get();
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Permission::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('libelle', 'LIKE', $term)
                  ->orWhere('slug', 'LIKE', $term)
                  ->orWhere('description', 'LIKE', $term);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}