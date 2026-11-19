<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadPhotoRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\RoleResource;
use App\Services\Interfaces\IUserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private IUserService $userService
    ) {}

    /**
     * GET /test-api/users
     * Liste avec pagination + filtres
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page'            => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search'              => ['sometimes', 'string'],
            'type_utilisateur_id' => ['sometimes', 'integer'],
            'is_active'           => ['sometimes', 'boolean'],
            'role_id'             => ['sometimes', 'integer'],
            'sort_by'             => ['sometimes', 'string', 'in:created_at,nom,prenom,email'],
            'sort_order'          => ['sometimes', 'string', 'in:asc,desc'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $filters = array_filter([
            'search'              => $validated['search'] ?? null,
            'type_utilisateur_id' => $validated['type_utilisateur_id'] ?? null,
            'is_active'           => $validated['is_active'] ?? null,
            'role_id'             => $validated['role_id'] ?? null,
            'sort_by'             => $validated['sort_by'] ?? null,
            'sort_order'          => $validated['sort_order'] ?? null,
        ], fn ($v) => $v !== null);

        $paginated = $this->userService->paginateUsers($perPage, $filters);

        return UserResource::collection($paginated);
    }

    /**
     * GET /test-api/users/search?q=xxx
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1'],
        ]);

        return UserResource::collection(
            $this->userService->searchUsers($validated['q'])
        );
    }

    /**
     * GET /test-api/users/{id}
     */
    public function show(int $id)
    {
        $user = $this->userService->getUser($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        return new UserResource($user);
    }

    /**
     * PUT /test-api/users/{id}
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $updated = $this->userService->updateUser($id, $request->validated());
        if (!$updated) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        return new UserResource($this->userService->getUser($id));
    }

    /**
     * DELETE /test-api/users/{id}
     */
    public function destroy(int $id)
    {
        $deleted = $this->userService->deleteUser($id);
        if (!$deleted) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        return response()->json(null, 204);
    }

    /**
     * PATCH /test-api/users/{id}/toggle-status
     */
    public function toggleStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $updated = $this->userService->toggleUserStatus($id, $validated['is_active']);
        if (!$updated) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json([
            'message' => 'Statut mis à jour',
            'data'    => new UserResource($this->userService->getUser($id)),
        ]);
    }

    /**
     * POST /test-api/users/{id}/photo
     */
    public function uploadPhoto(UploadPhotoRequest $request, int $id)
    {
        $user = $this->userService->uploadUserPhoto($id, $request->file('photo'));

        return response()->json([
            'message' => 'Photo mise à jour',
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * DELETE /test-api/users/{id}/photo
     */
    public function deletePhoto(int $id)
    {
        $deleted = $this->userService->deleteUserPhoto($id);
        if (!$deleted) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        return response()->json(['message' => 'Photo supprimée']);
    }

    /**
     * POST /test-api/users/{id}/reset-password
     */
    public function resetPassword(int $id)
    {
        $result = $this->userService->resetUserPassword($id);

        return response()->json([
            'message'      => 'Mot de passe réinitialisé',
            'email_sent'   => $result['email_sent'],
            'password_temp'=> $result['password_temp'], // ⚠️ pour debug, à retirer en prod
            'data'         => new UserResource($result['user']),
        ]);
    }

    // ==========================================
    // RÔLES (Étape 6)
    // ==========================================

    /**
     * GET /test-api/users/{id}/roles
     */
    public function getRoles(int $id)
    {
        $user = $this->userService->getUser($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        return RoleResource::collection($this->userService->getUserRoles($id));
    }

    /**
     * POST /test-api/users/{id}/roles
     */
    public function assignRoles(Request $request, int $id)
    {
        $validated = $request->validate([
            'role_ids'   => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct'],
        ]);

        $result = $this->userService->assignRolesToUser($id, $validated['role_ids']);

        if (!$result['user']) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json([
            'message'          => 'Rôles attribués',
            'attached'         => $result['attached'],
            'already_attached' => $result['already_attached'],
            'invalid'          => $result['invalid'],
            'data'             => new UserResource($result['user']),
        ]);
    }

    /**
     * DELETE /test-api/users/{id}/roles/{roleId}
     */
    public function removeRole(int $id, int $roleId)
    {
        $result = $this->userService->removeRoleFromUser($id, $roleId);

        if (!$result['user']) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        if (!$result['removed']) {
            return response()->json([
                'message' => 'Ce rôle n\'est pas attribué à cet utilisateur',
                'data'    => new UserResource($result['user']),
            ], 409);
        }

        return response()->json([
            'message' => 'Rôle retiré',
            'data'    => new UserResource($result['user']),
        ]);
    }
}