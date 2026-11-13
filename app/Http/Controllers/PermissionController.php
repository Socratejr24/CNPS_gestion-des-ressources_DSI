<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private IPermissionService $permissionService
    ) {}

    public function index(Request $request)
    {
        $permissions = $this->permissionService->getAllPermissions();
        return PermissionResource::collection($permissions);
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = $this->permissionService->createPermission($request->validated());
        return (new PermissionResource($permission))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        $permission = $this->permissionService->getPermission($id);
        if (!$permission) return response()->json(['message' => 'Permission non trouvée'], 404);
        return new PermissionResource($permission);
    }

    public function getBySlug(Request $request, string $slug)
    {
        $permission = $this->permissionService->getPermissionBySlug($slug);
        if (!$permission) return response()->json(['message' => 'Permission non trouvée'], 404);
        return new PermissionResource($permission);
    }

    public function update(UpdatePermissionRequest $request, int $id)
    {
        $updated = $this->permissionService->updatePermission($id, $request->validated());
        if (!$updated) return response()->json(['message' => 'Permission non trouvée'], 404);
        return new PermissionResource($this->permissionService->getPermission($id));
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = $this->permissionService->deletePermission($id);
        if (!$deleted) return response()->json(['message' => 'Permission non trouvée'], 404);
        return response()->json(null, 204);
    }

    //  : activer / désactiver
    public function toggleStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $updated = $this->permissionService->togglePermissionStatus($id, $validated['is_active']);
        if (!$updated) return response()->json(['message' => 'Permission non trouvée'], 404);

        return response()->json([
            'message' => 'Statut mis à jour',
            'data' => new PermissionResource($this->permissionService->getPermission($id)),
        ]);
    }

    // : recherche
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1'],
        ]);

        $permissions = $this->permissionService->searchPermissions($validated['q']);
        return PermissionResource::collection($permissions);
    }

    // : pagination + filtres
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

        $paginated = $this->permissionService->paginatePermissions($perPage, $filters);

        return PermissionResource::collection($paginated);
    }
}