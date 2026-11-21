<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RessourceRequest;
use App\Models\Ressource;
use App\Services\Interfaces\IReferentielService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Référentiel des ressources (SFD §5). Pas de suppression : désactivation.
 */
class RessourceController extends Controller
{
    public function __construct(private IReferentielService $referentiels) {}

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', Rule::in(['actif', 'inactif'])],
        ]);

        return view('admin.ressources.index', [
            'ressources' => $this->referentiels->listerRessources($filtres),
            'categories' => $this->referentiels->categories(),
            'filtres' => $filtres,
            'compteurs' => [
                'total' => Ressource::count(),
                'actives' => Ressource::where('is_active', true)->count(),
                'categories' => Ressource::distinct()->count('categorie'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.ressources.form', [
            'ressource' => null,
            'categories' => $this->referentiels->categories(),
        ]);
    }

    public function store(RessourceRequest $request): RedirectResponse
    {
        $ressource = $this->referentiels->creerRessource($request->validated());

        return redirect()->route('admin.ressources.index')
            ->with('success', "Ressource « {$ressource->designation} » créée.");
    }

    public function edit(Ressource $ressource): View
    {
        return view('admin.ressources.form', [
            'ressource' => $ressource,
            'categories' => $this->referentiels->categories(),
        ]);
    }

    public function update(RessourceRequest $request, Ressource $ressource): RedirectResponse
    {
        $this->referentiels->modifierRessource($ressource, $request->validated());

        return redirect()->route('admin.ressources.index')->with('success', 'Ressource mise à jour.');
    }

    public function changerStatut(Request $request, Ressource $ressource): RedirectResponse
    {
        $actif = $request->boolean('is_active');
        $this->referentiels->changerStatutRessource($ressource, $actif);

        return back()->with('success', $actif ? 'Ressource activée.' : 'Ressource désactivée.');
    }
}
