<?php

namespace App\Http\Controllers;

use App\Http\Requests\StructureRequest;
use App\Http\Resources\StructureResource;
use App\Models\Structure;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function __construct(
        private IOrganisationService $organisation
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate(['is_active' => ['sometimes', 'boolean']]);

        return StructureResource::collection(
            $this->organisation->listerStructures(
                isset($validated['is_active']) ? (bool) $validated['is_active'] : null
            )
        );
    }

    public function store(StructureRequest $request)
    {
        return (new StructureResource($this->organisation->creerStructure($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(Structure $structure)
    {
        return new StructureResource($structure->loadCount('departements'));
    }

    public function update(StructureRequest $request, Structure $structure)
    {
        return new StructureResource($this->organisation->modifierStructure($structure, $request->validated()));
    }

    public function toggleStatus(Request $request, Structure $structure)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        return new StructureResource(
            $this->organisation->changerStatutStructure($structure, (bool) $validated['is_active'])
        );
    }
}
