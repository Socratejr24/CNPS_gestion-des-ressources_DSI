<?php

namespace App\Repositories\Implementations;

use App\Models\User;
use App\Repositories\Interfaces\IAuthRepository;

class AuthRepository implements IAuthRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByIdentifiant(string $identifiant): ?User
    {
        return User::where('identifiant', $identifiant)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function updatePassword(User $user, string $password): bool
    {
        return $user->update(['password' => $password]);
    }
    
    // Ajoute cette méthode
public function findByMatricule(string $matricule): ?User
{
    return User::where('matricule', $matricule)->first();
} 
}