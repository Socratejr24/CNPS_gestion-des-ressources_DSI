<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Services\Interfaces\IValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Validation hiérarchique N+1 / N+2 (SFD §7).
 * L'écran est réservé aux validateurs ; chaque décision est contrôlée par DemandePolicy::decider.
 */
class ValidationController extends Controller
{
    public function __construct(private IValidationService $validations) {}

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'onglet' => ['nullable', Rule::in(['a-valider', 'traitees'])],
        ]);
        $utilisateur = $request->user();
        $onglet = $filtres['onglet'] ?? 'a-valider';

        return view('validations.index', [
            'onglet' => $onglet,
            'demandes' => $onglet === 'traitees'
                ? $this->validations->listerTraiteesPar($utilisateur, $filtres)
                : $this->validations->listerAValider($utilisateur, $filtres),
            'nombreAValider' => $this->validations->compterAValider($utilisateur),
            'filtres' => $filtres,
        ]);
    }

    public function valider(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('decider', $demande);
        $donnees = $request->validate(['commentaire' => ['nullable', 'string', 'max:2000']]);

        $demande = $this->validations->valider($demande, $request->user(), $donnees['commentaire'] ?? null);

        return redirect()->route('validations.index')
            ->with('success', "Demande {$demande->reference} validée : {$demande->statut->label()}.");
    }

    public function rejeter(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('decider', $demande);
        $donnees = $request->validate(['motif' => ['required', 'string', 'max:2000']], [
            'motif.required' => 'Le motif du rejet est obligatoire.',
        ]);

        $demande = $this->validations->rejeter($demande, $request->user(), $donnees['motif']);

        return redirect()->route('validations.index')->with('success', "Demande {$demande->reference} rejetée.");
    }

    public function demanderInformations(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('decider', $demande);
        $donnees = $request->validate(['question' => ['required', 'string', 'max:2000']], [
            'question.required' => 'Précisez les informations attendues du demandeur.',
        ]);

        $demande = $this->validations->demanderInformations($demande, $request->user(), $donnees['question']);

        return redirect()->route('validations.index')
            ->with('success', "Informations demandées pour {$demande->reference} : la demande vous reviendra après la réponse.");
    }
}
