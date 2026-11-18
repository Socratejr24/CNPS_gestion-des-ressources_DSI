<?php

namespace App\Http\Controllers;

use App\Enums\CategorieUtilisateur;
use App\Http\Requests\UtilisateurRequest;
use App\Http\Resources\UtilisateurResource;
use App\Models\Service;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use App\Services\Interfaces\IUtilisateurService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    public function __construct(
        private IUtilisateurService $utilisateurs,
        private IOrganisationService $organisation,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string'],
            'categorie' => ['sometimes', Rule::enum(CategorieUtilisateur::class)],
            'service_id' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'sans_affectation' => ['sometimes', 'boolean'],
        ]);

        return UtilisateurResource::collection(
            $this->utilisateurs->lister($validated['per_page'] ?? 15, $validated)
        );
    }

    /** Création d'un compte prestataire (les agents arrivent via LDAP). */
    public function store(UtilisateurRequest $request)
    {
        return (new UtilisateurResource($this->utilisateurs->creerPrestataire($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(User $utilisateur)
    {
        return new UtilisateurResource($this->utilisateurs->charger($utilisateur));
    }

    public function update(UtilisateurRequest $request, User $utilisateur)
    {
        return new UtilisateurResource($this->utilisateurs->modifier($utilisateur, $request->validated()));
    }

    public function toggleStatus(Request $request, User $utilisateur)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        return new UtilisateurResource($this->utilisateurs->changerStatut($utilisateur, (bool) $validated['is_active']));
    }

    /**
     * Affecte l'utilisateur à son service principal. `service_id: null` le désaffecte.
     */
    public function affecter(Request $request, User $utilisateur)
    {
        $validated = $request->validate([
            'service_id' => ['present', 'nullable', 'integer', 'exists:services,id'],
        ]);

        $service = $validated['service_id'] ? Service::findOrFail($validated['service_id']) : null;

        return new UtilisateurResource($this->organisation->affecterUtilisateur($utilisateur, $service));
    }

    /**
     * Remplace les rôles fonctionnels de l'utilisateur.
     */
    public function attribuerRoles(Request $request, User $utilisateur)
    {
        $validated = $request->validate([
            'role_ids' => ['present', 'array'],
            'role_ids.*' => ['integer', 'distinct'],
        ]);

        return new UtilisateurResource($this->utilisateurs->attribuerRoles($utilisateur, $validated['role_ids']));
    }
}
