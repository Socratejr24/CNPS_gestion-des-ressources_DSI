<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTypeUtilisateurRequest;
use App\Http\Requests\UpdateTypeUtilisateurRequest;
use App\Services\Interfaces\ITypeUtilisateurService;
use Illuminate\Http\Request;

class TypeUtilisateurController extends Controller
{
    public function __construct(
        private ITypeUtilisateurService $typeUtilisateurService
    ) {}

    public function index()
    {
        return response()->json($this->typeUtilisateurService->getAllTypes());
    }

    public function store(StoreTypeUtilisateurRequest $request)
    {
        $type = $this->typeUtilisateurService->createType($request->validated());
        return response()->json($type, 201);
    }

    public function show(int $id)
    {
        $type = $this->typeUtilisateurService->getType($id);
        if (!$type) return response()->json(['message' => 'Type non trouvé'], 404);
        return response()->json($type);
    }

    public function update(UpdateTypeUtilisateurRequest $request, int $id)
    {
        $updated = $this->typeUtilisateurService->updateType($id, $request->validated());
        if (!$updated) return response()->json(['message' => 'Type non trouvé'], 404);
        return response()->json($this->typeUtilisateurService->getType($id));
    }

    public function destroy(int $id)
    {
        $deleted = $this->typeUtilisateurService->deleteType($id);
        if (!$deleted) return response()->json(['message' => 'Type non trouvé'], 404);
        return response()->json(null, 204);
    }

    public function toggleStatus(Request $request, int $id)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $updated = $this->typeUtilisateurService->toggleTypeStatus($id, $validated['is_active']);
        if (!$updated) return response()->json(['message' => 'Type non trouvé'], 404);
        
        return response()->json([
            'message' => 'Statut mis à jour',
            'data' => $this->typeUtilisateurService->getType($id)
        ]);
    }
}