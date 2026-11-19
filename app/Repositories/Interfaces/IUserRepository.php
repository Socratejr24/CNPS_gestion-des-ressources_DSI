<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IUserRepository
{
    public function getAll(): Collection;
    public function getById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByMatricule(string $matricule): ?User;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function toggleStatus(int $id, bool $status): bool;
    
    // Photo
    public function updatePhoto(int $id, string $path): bool;
    public function deletePhoto(int $id): bool;
    
    // Recherche & pagination
    public function search(string $term): Collection;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    
    // Reset password (admin)
    public function updatePassword(int $id, string $newPassword): bool;
    public function forcePasswordChange(int $id, bool $mustChange): bool;
    
    // Rôles (Étape 6)
    public function getRoles(int $userId): Collection;
    public function assignRoles(int $userId, array $roleIds): array;
    public function removeRole(int $userId, int $roleId): array;
}