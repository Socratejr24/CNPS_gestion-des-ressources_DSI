<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StructureRequest;
use App\Models\Structure;
use App\Services\Interfaces\IOrganisationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StructureController extends Controller
{
    public function __construct(private IOrganisationService $organisation) {}

    public function create(): View
    {
        return view('admin.organisation.structure-form', ['structure' => null]);
    }

    public function store(StructureRequest $request): RedirectResponse
    {
        $structure = $this->organisation->creerStructure($request->validated());

        return redirect()->route('admin.organisation.index')
            ->with('success', "Structure « {$structure->libelle} » créée.");
    }

    public function edit(Structure $structure): View
    {
        return view('admin.organisation.structure-form', [
            'structure' => $structure->loadCount('departements'),
        ]);
    }

    public function update(StructureRequest $request, Structure $structure): RedirectResponse
    {
        $this->organisation->modifierStructure($structure, $request->validated());

        return redirect()->route('admin.organisation.index')->with('success', 'Structure mise à jour.');
    }

    public function changerStatut(Request $request, Structure $structure): RedirectResponse
    {
        $actif = $request->boolean('is_active');
        $this->organisation->changerStatutStructure($structure, $actif);

        return back()->with('success', $actif ? 'Structure activée.' : 'Structure désactivée.');
    }
}
