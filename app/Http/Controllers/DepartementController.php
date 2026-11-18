<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartementRequest;
use App\Http\Resources\DepartementResource;
use App\Models\Departement;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function __construct(
        private IOrganisationService $organisation
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'structure_id' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return DepartementResource::collection(
            $this->organisation->listerDepartements(
                $validated['structure_id'] ?? null,
                isset($validated['is_active']) ? (bool) $validated['is_active'] : null,
            )
        );
    }

    public function store(DepartementRequest $request)
    {
        return (new DepartementResource($this->organisation->creerDepartement($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(Departement $departement)
    {
        return new DepartementResource($departement->load(['structure', 'responsable'])->loadCount('services'));
    }

    public function update(DepartementRequest $request, Departement $departement)
    {
        return new DepartementResource($this->organisation->modifierDepartement($departement, $request->validated()));
    }

    public function toggleStatus(Request $request, Departement $departement)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        return new DepartementResource(
            $this->organisation->changerStatutDepartement($departement, (bool) $validated['is_active'])
        );
    }

    /**
     * Désigne le responsable (N+2). `utilisateur_id: null` retire la désignation.
     */
    public function designerResponsable(Request $request, Departement $departement)
    {
        $validated = $request->validate([
            'utilisateur_id' => ['present', 'nullable', 'integer', 'exists:utilisateurs,id'],
        ]);

        $utilisateur = $validated['utilisateur_id'] ? User::findOrFail($validated['utilisateur_id']) : null;

        return new DepartementResource($this->organisation->designerResponsable($departement, $utilisateur));
    }
}
