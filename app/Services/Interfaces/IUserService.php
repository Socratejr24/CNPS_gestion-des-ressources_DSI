<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IUserService
{
    public function getAllUsers(): Collection;
    public function getUser(int $id): ?User;
    public function updateUser(int $id, array $data): bool;
    public function deleteUser(int $id): bool;
    public function toggleUserStatus(int $id, bool $status): bool;
    
    // Photo
    public function uploadUserPhoto(int $id, $file): ?User;
    public function deleteUserPhoto(int $id): bool;
    
    // Recherche & pagination
    public function searchUsers(string $term): Collection;
    public function paginateUsers(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    
    // Reset password (admin)
    public function resetUserPassword(int $id): array;
    
    // Rôles 
    public function getUserRoles(int $userId): Collection;
    public function assignRolesToUser(int $userId, array $roleIds): array;
    public function removeRoleFromUser(int $userId, int $roleId): array;
}