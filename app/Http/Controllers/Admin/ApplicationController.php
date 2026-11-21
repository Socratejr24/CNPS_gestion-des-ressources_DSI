<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApplicationRequest;
use App\Models\Application;
use App\Services\Interfaces\IReferentielService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Référentiel des applications (SFD §4). Pas de suppression : désactivation.
 */
class ApplicationController extends Controller
{
    public function __construct(private IReferentielService $referentiels) {}

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', Rule::in(['actif', 'inactif'])],
        ]);

        return view('admin.applications.index', [
            'applications' => $this->referentiels->listerApplications($filtres),
            'filtres' => $filtres,
            'compteurs' => [
                'total' => Application::count(),
                'actives' => Application::where('is_active', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.applications.form', ['application' => null]);
    }

    public function store(ApplicationRequest $request): RedirectResponse
    {
        $application = $this->referentiels->creerApplication($request->validated());

        return redirect()->route('admin.applications.index')
            ->with('success', "Application « {$application->nom} » créée.");
    }

    public function edit(Application $application): View
    {
        return view('admin.applications.form', ['application' => $application]);
    }

    public function update(ApplicationRequest $request, Application $application): RedirectResponse
    {
        $this->referentiels->modifierApplication($application, $request->validated());

        return redirect()->route('admin.applications.index')->with('success', 'Application mise à jour.');
    }

    public function changerStatut(Request $request, Application $application): RedirectResponse
    {
        $actif = $request->boolean('is_active');
        $this->referentiels->changerStatutApplication($application, $actif);

        return back()->with('success', $actif ? 'Application activée.' : 'Application désactivée.');
    }
}
