<?php

namespace App\Services\Implementations;

use App\Helpers\AuthHelper;
use App\Mail\PasswordResetByAdminMail;
use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IUserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService implements IUserService
{
    public function __construct(
        private IUserRepository $userRepository
    ) {}

    public function getAllUsers(): Collection
    {
        return $this->userRepository->getAll();
    }

    public function getUser(int $id): ?User
    {
        return $this->userRepository->getById($id);
    }

    public function updateUser(int $id, array $data): bool
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            return false;
        }

        // Sécurité : on ne modifie PAS le mot de passe via cet endpoint
        unset($data['password'], $data['must_change_password']);

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function toggleUserStatus(int $id, bool $status): bool
    {
        return $this->userRepository->toggleStatus($id, $status);
    }

    // ==========================================
    // PHOTO
    // ==========================================

    public function uploadUserPhoto(int $id, $file): ?User
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'Utilisateur introuvable.']);
        }

        // Validation MIME & taille (2 Mo max)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw ValidationException::withMessages([
                'photo' => 'Format non supporté. Utilisez JPG, PNG ou WebP.',
            ]);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'photo' => 'La photo ne doit pas dépasser 2 Mo.',
            ]);
        }

        // Nom unique basé sur l'ID et un timestamp
        $filename = $id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $filename, 'public');

        $this->userRepository->updatePhoto($id, $path);

        return $this->userRepository->getById($id);
    }

    public function deleteUserPhoto(int $id): bool
    {
        return $this->userRepository->deletePhoto($id);
    }

    // ==========================================
    // RECHERCHE & PAGINATION
    // ==========================================

    public function searchUsers(string $term): Collection
    {
        return $this->userRepository->search($term);
    }

    public function paginateUsers(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    // ==========================================
    // RESET PASSWORD (ADMIN)
    // ==========================================

    public function resetUserPassword(int $id): array
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'Utilisateur introuvable.']);
        }

        // Les agents LDAP n'ont pas de mot de passe local → interdit
        if ($user->is_ldap) {
            throw ValidationException::withMessages([
                'user' => 'Impossible de réinitialiser le mot de passe d\'un agent LDAP.',
            ]);
        }

        // Génération du mot de passe temporaire
        $passwordPlain = AuthHelper::generatePassword();

        // Mise à jour en BDD
        $this->userRepository->updatePassword($id, $passwordPlain);
        $this->userRepository->forcePasswordChange($id, true);

        // Envoi par email
        if (!empty($user->email)) {
            Mail::to($user->email)->send(
                new PasswordResetByAdminMail(
                    nom:          $user->nom,
                    prenom:       $user->prenom,
                    passwordPlain: $passwordPlain,
                )
            );
        }

        return [
            'user'              => $this->userRepository->getById($id),
            'password_temp'     => $passwordPlain,
            'email_sent'        => !empty($user->email),
        ];
    }

    // ==========================================
    // RÔLES (ÉTAPE 6)
    // ==========================================

    public function getUserRoles(int $userId): Collection
    {
        return $this->userRepository->getRoles($userId);
    }

    public function assignRolesToUser(int $userId, array $roleIds): array
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        return $this->userRepository->assignRoles($userId, $roleIds);
    }

    public function removeRoleFromUser(int $userId, int $roleId): array
    {
        return $this->userRepository->removeRole($userId, $roleId);
    }
}