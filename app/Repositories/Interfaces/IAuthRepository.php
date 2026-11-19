<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface IAuthRepository
{
    public function findByEmail(string $email): ?User;
    public function findByIdentifiant(string $identifiant): ?User;
    public function findById(int $id): ?User;
    public function createUser(array $data): User;
    public function revokeTokens(User $user): void;
    public function updatePassword(User $user, string $password): bool;
    public function findByMatricule(string $matricule): ?User;
}