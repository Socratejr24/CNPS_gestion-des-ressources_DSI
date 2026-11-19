<?php

namespace App\Repositories\Implementations;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class UserRepository implements IUserRepository
{
    public function getAll(): Collection
    {
        return User::with(['typeUtilisateur', 'roles'])->get();
    }

    public function getById(int $id): ?User
    {
        return User::with(['typeUtilisateur', 'roles'])->find($id);
    }

    public function findByEmail(string $email): ?User
    {        return User::where('email', $email)->first();
    }

    public function findByMatricule(string $matricule): ?User
    {
        return User::where('matricule', $matricule)->first();
    }

    public function update(int $id, array $data): bool
    {
        $user = User::find($id);
        return $user ? $user->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $user = User::find($id);
        return $user ? $user->delete() : false;
    }

    public function toggleStatus(int $id, bool $status): bool
    {
        $user = User::find($id);
        return $user ? $user->update(['is_active' => $status]) : false;
    }

    // ==========================================
    // PHOTO
    // ==========================================

    public function updatePhoto(int $id, string $path): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        // Supprimer l'ancienne photo si elle existe
        if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
            Storage::disk('public')->delete($user->photo_path);
        }

        return $user->update(['photo_path' => $path]);
    }

    public function deletePhoto(int $id): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
            Storage::disk('public')->delete($user->photo_path);
        }

        return $user->update(['photo_path' => null]);
    }

    // ==========================================
    // RECHERCHE & PAGINATION
    // ==========================================

    public function search(string $term): Collection
    {
        $like = '%' . $term . '%';

        return User::with(['typeUtilisateur', 'roles'])
            ->where(function ($q) use ($like) {
                $q->where('nom', 'LIKE', $like)
                  ->orWhere('prenom', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhere('identifiant', 'LIKE', $like)
                  ->orWhere('matricule', 'LIKE', $like);
            })
            ->get();
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['typeUtilisateur', 'roles']);

        // Filtre : recherche textuelle
        if (!empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($like) {
                $q->where('nom', 'LIKE', $like)
                  ->orWhere('prenom', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhere('identifiant', 'LIKE', $like)
                  ->orWhere('matricule', 'LIKE', $like);
            });
        }

        // Filtre : type d'utilisateur
        if (!empty($filters['type_utilisateur_id'])) {
            $query->where('type_utilisateur_id', $filters['type_utilisateur_id']);
        }

        // Filtre : statut actif/inactif
        if (isset($filters['is_active'])) {
            $val = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($val !== null) {
                $query->where('is_active', $val);
            }
        }

        // Filtre : rôle
        if (!empty($filters['role_id'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('roles.id', $filters['role_id']);
            });
        }

        // Tri
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSorts = ['created_at', 'nom', 'prenom', 'email'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage);
    }

    // ==========================================
    // RESET PASSWORD (ADMIN)
    // ==========================================

    public function updatePassword(int $id, string $newPassword): bool
    {
        $user = User::find($id);
        return $user ? $user->update(['password' => $newPassword]) : false;
    }

    public function forcePasswordChange(int $id, bool $mustChange): bool
    {
        $user = User::find($id);
        return $user ? $user->update(['must_change_password' => $mustChange]) : false;
    }

    // ==========================================
    // RÔLES (ÉTAPE 6)
    // ==========================================

    public function getRoles(int $userId): Collection
    {
        $user = User::find($userId);
        return $user ? $user->roles()->get() : new Collection();
    }

    public function assignRoles(int $userId, array $roleIds): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['attached' => [], 'already_attached' => [], 'invalid' => $roleIds, 'user' => null];
        }

        // Séparer valides / invalides
        $existing = Role::whereIn('id', $roleIds)->pluck('id')->all();
        $invalid  = array_values(array_diff($roleIds, $existing));

        // Avant / après
        $before = $user->roles()->pluck('roles.id')->all();
        $user->roles()->syncWithoutDetaching($existing);
        $after  = $user->roles()->pluck('roles.id')->all();

        $attached        = array_values(array_diff($after, $before));
        $alreadyAttached = array_values(array_intersect($existing, $before));

        return [
            'attached'         => $attached,
            'already_attached' => $alreadyAttached,
            'invalid'          => $invalid,
            'user'             => $user->fresh(['roles']),
        ];
    }

    public function removeRole(int $userId, int $roleId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['removed' => false, 'user' => null];
        }

        $hadRole = $user->roles()->where('roles.id', $roleId)->exists();
        if (!$hadRole) {
            return ['removed' => false, 'user' => $user->fresh(['roles'])];
        }

        $user->roles()->detach($roleId);

        return ['removed' => true, 'user' => $user->fresh(['roles'])];
    }
}