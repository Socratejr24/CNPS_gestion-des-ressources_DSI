<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private IOrganisationService $organisation
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'departement_id' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return ServiceResource::collection(
            $this->organisation->listerServices(
                $validated['departement_id'] ?? null,
                isset($validated['is_active']) ? (bool) $validated['is_active'] : null,
            )
        );
    }

    public function store(ServiceRequest $request)
    {
        return (new ServiceResource($this->organisation->creerService($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(Service $service)
    {
        return new ServiceResource($service->load(['departement.structure', 'chef'])->loadCount('utilisateurs'));
    }

    public function update(ServiceRequest $request, Service $service)
    {
        return new ServiceResource($this->organisation->modifierService($service, $request->validated()));
    }

    public function toggleStatus(Request $request, Service $service)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        return new ServiceResource(
            $this->organisation->changerStatutService($service, (bool) $validated['is_active'])
        );
    }

    /**
     * Désigne le chef de service (N+1). `utilisateur_id: null` retire la désignation.
     */
    public function designerChef(Request $request, Service $service)
    {
        $validated = $request->validate([
            'utilisateur_id' => ['present', 'nullable', 'integer', 'exists:utilisateurs,id'],
        ]);

        $utilisateur = $validated['utilisateur_id'] ? User::findOrFail($validated['utilisateur_id']) : null;

        return new ServiceResource($this->organisation->designerChef($service, $utilisateur));
    }
}
