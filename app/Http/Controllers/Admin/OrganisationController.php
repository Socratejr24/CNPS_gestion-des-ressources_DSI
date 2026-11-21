<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use Illuminate\View\View;

/**
 * Vue d'ensemble de l'organisation de la DSI (SFD §2) :
 * Structure → Département (N+2) → Service (N+1) → Utilisateurs.
 */
class OrganisationController extends Controller
{
    public function index(): View
    {
        $structures = Structure::query()
            ->with([
                'departements' => fn ($q) => $q->orderBy('libelle'),
                'departements.responsable',
                'departements.services' => fn ($q) => $q->withCount('utilisateurs')->orderBy('libelle'),
                'departements.services.chef',
            ])
            ->orderBy('libelle')
            ->get();

        return view('admin.organisation.index', [
            'structures' => $structures,
            'compteurs' => [
                'structures' => Structure::where('is_active', true)->count(),
                'departements' => Departement::where('is_active', true)->count(),
                'services' => Service::where('is_active', true)->count(),
                'sans_responsable' => Departement::where('is_active', true)->whereNull('responsable_id')->count(),
                'sans_chef' => Service::where('is_active', true)->whereNull('chef_id')->count(),
                'sans_affectation' => User::where('is_active', true)->whereNull('service_id')->count(),
            ],
        ]);
    }
}
