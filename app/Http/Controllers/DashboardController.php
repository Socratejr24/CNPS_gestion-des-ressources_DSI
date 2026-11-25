<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ITableauDeBordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private ITableauDeBordService $tableauDeBord) {}

    /**
     * Tableau de bord adapté aux droits de l'utilisateur (SFD §14).
     */
    public function index(Request $request): View
    {
        $donnees = $this->tableauDeBord->pour($request->user());

        return view('dashboard.index', [
            'user' => current_user(),
            'global' => $donnees['global'],
            'kpis' => $donnees['kpis'],
            'travail' => $donnees['travail'],
            'chartStatuts' => $donnees['statuts'],
            'chartEvolution' => $donnees['evolution'],
            'recentesDemandes' => $donnees['recentes'],
            'recentesNotifs' => $donnees['notifications'],
        ]);
    }
}
