<?php

namespace App\Http\Controllers;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\Interfaces\IRoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private IRoleService $roleService
    ) {}

    public function index(Request $request)
    {
        return RoleResource::collection($this->roleService->getAllRoles());
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());
        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        $role = $this->roleService->getRole($id);
        if (!$role) return response()->json(['message' => 'Role non trouvé'], 404);
        return new RoleResource($role);
    }

    public function getByName(Request $request, string $name)
    {
        $role = $this->roleService->getRoleByLibelle($name);
        if (!$role) return response()->json(['message' => 'Role non trouvé'], 404);
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, int $id)
    {
        $updated = $this->roleService->updateRole($id, $request->validated());
        if (!$updated) return response()->json(['message' => 'Role non trouvé'], 404);
        return new RoleResource($this->roleService->getRole($id));
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = $this->roleService->deleteRole($id);
        if (!$deleted) return response()->json(['message' => 'Role non trouvé'], 404);
        return response()->json(null, 204);
    }

    //  activer / désactiver
    public function toggleStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $updated = $this->roleService->toggleRoleStatus($id, $validated['is_active']);
        if (!$updated) return response()->json(['message' => 'Role non trouvé'], 404);

        return response()->json([
            'message' => 'Statut mis à jour',
            'data' => new RoleResource($this->roleService->getRole($id)),
        ]);
    }

    //   recherche
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1'],
        ]);

        return RoleResource::collection($this->roleService->searchRoles($validated['q']));
    }

    //  pagination + filtres
    public function paginate(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $filters = array_filter([
            'is_active' => $validated['is_active'] ?? null,
            'search'    => $validated['search']    ?? null,
        ], fn ($v) => $v !== null);

        return RoleResource::collection(
            $this->roleService->paginateRoles($perPage, $filters)
        );
    }
  

    // Gestiond es permissions

//  Attribuer des permissions à un rôle
public function assignPermissions(Request $request, int $id)
{
    $validated = $request->validate([
        'permission_ids'   => ['required', 'array', 'min:1'],
        'permission_ids.*' => ['integer', 'distinct'], // pas de validation exists ici → gérée côté repo
    ]);

    $result = $this->roleService->assignPermissionsToRole($id, $validated['permission_ids']);

    if (!$result['role']) {
        return response()->json(['message' => 'Role non trouvé'], 404);
    }

    return response()->json([
        'message'          => 'Permissions attribuées',
        'attached'         => $result['attached'],
        'already_attached' => $result['already_attached'],
        'invalid'          => $result['invalid'],
        'data'             => new RoleResource($result['role']),
    ], 200);
}

//  Retirer une permission d'un rôle
public function removePermission(Request $request, int $roleId, int $permissionId)
{
    $result = $this->roleService->removePermissionFromRole($roleId, $permissionId);

    if (!$result['role']) {
        return response()->json(['message' => 'Role non trouvé'], 404);
    }

    if (!$result['removed']) {
        return response()->json([
            'message' => 'Cette permission n\'est pas attachée à ce rôle',
            'data'    => new RoleResource($result['role']),
        ], 409); // 409 Conflict → sémantiquement correct
    }

    return response()->json([
        'message' => 'Permission retirée',
        'data'    => new RoleResource($result['role']),
    ], 200);
}

//  Récupérer les permissions d'un rôle
public function getPermissions(Request $request, int $id)
{
    $role = $this->roleService->getRole($id);
    if (!$role) {
        return response()->json(['message' => 'Role non trouvé'], 404);
    }

    $permissions = $this->roleService->getRolePermissions($id);

    return PermissionResource::collection($permissions);
}







}