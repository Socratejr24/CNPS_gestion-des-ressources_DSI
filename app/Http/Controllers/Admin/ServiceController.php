<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Departement;
use App\Models\Service;
use App\Models\User;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private IOrganisationService $organisation) {}

    public function create(Request $request): View
    {
        return view('admin.organisation.service-form', [
            'service' => null,
            'departements' => $this->departementsGroupes(),
            'departementParDefaut' => $request->integer('departement_id') ?: null,
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = $this->organisation->creerService($request->validated());

        return redirect()->route('admin.organisation.index')
            ->with('success', "Service « {$service->libelle} » créé.");
    }

    public function edit(Service $service): View
    {
        $service->load(['departement.structure', 'chef']);

        return view('admin.organisation.service-form', [
            'service' => $service,
            'departements' => $this->departementsGroupes($service->departement_id),
            'departementParDefaut' => null,
            'membres' => $service->utilisateurs()->orderBy('nom')->orderBy('prenom')->get(),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->organisation->modifierService($service, $request->validated());

        return back()->with('success', 'Service mis à jour.');
    }

    public function changerStatut(Request $request, Service $service): RedirectResponse
    {
        $actif = $request->boolean('is_active');
        $this->organisation->changerStatutService($service, $actif);

        return back()->with('success', $actif ? 'Service activé.' : 'Service désactivé.');
    }

    public function designerChef(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'utilisateur_id' => ['nullable', 'integer', 'exists:utilisateurs,id'],
        ]);

        $utilisateur = isset($validated['utilisateur_id']) ? User::findOrFail($validated['utilisateur_id']) : null;
        $this->organisation->designerChef($service, $utilisateur);

        return back()->with('success', $utilisateur
            ? "{$utilisateur->nomComplet()} est désormais chef du service (N+1)."
            : 'Chef de service retiré.');
    }

    /**
     * Départements actifs (plus l'actuel s'il est désactivé), regroupés par structure.
     *
     * @return array<string, array<int, string>>
     */
    private function departementsGroupes(?int $actuel = null): array
    {
        return Departement::with('structure')
            ->where(fn ($q) => $q->where('is_active', true)->when($actuel, fn ($q) => $q->orWhere('id', $actuel)))
            ->orderBy('libelle')
            ->get()
            ->groupBy(fn (Departement $d) => $d->structure?->libelle ?? '—')
            ->sortKeys()
            ->map(fn ($groupe) => $groupe->mapWithKeys(fn (Departement $d) => [
                $d->id => $d->libelle.($d->is_active ? '' : ' (inactif)'),
            ])->all())
            ->all();
    }
}
