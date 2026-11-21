<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface IAuthService
{
   public function loginAgentLDAP(string $matricule, string $password): array;
    public function loginPrestataire(string $identifiant, string $password): array;
    public function createPrestataire(array $data): User;
    public function logout(User $user): void;
    public function forgotPassword(string $email): void;
    public function verifyOtp(string $email, string $otp): bool;
    public function resetPassword(string $email, string $otp, string $newPassword): bool;
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;
}