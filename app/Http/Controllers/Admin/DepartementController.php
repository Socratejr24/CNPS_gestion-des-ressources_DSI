<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartementRequest;
use App\Models\Departement;
use App\Models\Structure;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartementController extends Controller
{
    public function __construct(private IOrganisationService $organisation) {}

    public function create(Request $request): View
    {
        return view('admin.organisation.departement-form', [
            'departement' => null,
            'structures' => $this->structures(),
            'structureParDefaut' => $request->integer('structure_id') ?: null,
        ]);
    }

    public function store(DepartementRequest $request): RedirectResponse
    {
        $departement = $this->organisation->creerDepartement($request->validated());

        return redirect()->route('admin.organisation.index')
            ->with('success', "Département « {$departement->libelle} » créé.");
    }

    public function edit(Departement $departement): View
    {
        $departement->load(['structure', 'responsable', 'services' => fn ($q) => $q->orderBy('libelle')]);

        return view('admin.organisation.departement-form', [
            'departement' => $departement,
            'structures' => $this->structures($departement->structure_id),
            'structureParDefaut' => null,
            // RG-16 : le N+2 doit être affecté à un service du département.
            'candidats' => User::where('is_active', true)
                ->whereHas('service', fn ($q) => $q->where('departement_id', $departement->id))
                ->with('service')
                ->orderBy('nom')->orderBy('prenom')
                ->get(),
        ]);
    }

    public function update(DepartementRequest $request, Departement $departement): RedirectResponse
    {
        $this->organisation->modifierDepartement($departement, $request->validated());

        return back()->with('success', 'Département mis à jour.');
    }

    public function changerStatut(Request $request, Departement $departement): RedirectResponse
    {
        $actif = $request->boolean('is_active');
        $this->organisation->changerStatutDepartement($departement, $actif);

        return back()->with('success', $actif ? 'Département activé.' : 'Département désactivé.');
    }

    public function designerResponsable(Request $request, Departement $departement): RedirectResponse
    {
        $validated = $request->validate([
            'utilisateur_id' => ['nullable', 'integer', 'exists:utilisateurs,id'],
        ]);

        $utilisateur = isset($validated['utilisateur_id']) ? User::findOrFail($validated['utilisateur_id']) : null;
        $this->organisation->designerResponsable($departement, $utilisateur);

        return back()->with('success', $utilisateur
            ? "{$utilisateur->nomComplet()} est désormais responsable du département (N+2)."
            : 'Responsable retiré.');
    }

    /** Structures actives, plus la structure actuelle si elle est désactivée. */
    private function structures(?int $actuelle = null)
    {
        return Structure::where('is_active', true)
            ->when($actuelle, fn ($q) => $q->orWhere('id', $actuelle))
            ->orderBy('libelle')
            ->get();
    }
}
