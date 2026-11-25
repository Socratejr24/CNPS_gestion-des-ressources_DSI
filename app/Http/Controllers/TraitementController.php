<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Services\Interfaces\ITraitementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Traitement des demandes par le receveur (SFD §8, §9.3).
 * Chaque action est contrôlée par DemandePolicy (prendreEnCharge, traiter, regulariser).
 */
class TraitementController extends Controller
{
    public function __construct(private ITraitementService $traitements) {}

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'onglet' => ['nullable', Rule::in(['a-prendre', 'en-cours', 'terminees'])],
        ]);
        $onglet = $filtres['onglet'] ?? 'a-prendre';

        return view('traitement.index', [
            'onglet' => $onglet,
            'demandes' => $this->traitements->lister($request->user(), $onglet, $filtres),
            'compteurs' => $this->traitements->compteurs($request->user()),
            'filtres' => $filtres,
        ]);
    }

    public function prendreEnCharge(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('prendreEnCharge', $demande);
        $demande = $this->traitements->prendreEnCharge($demande, $request->user());

        return redirect()->route('demandes.show', $demande)
            ->with('success', "Vous avez pris en charge la demande {$demande->reference}.");
    }

    public function demanderInformations(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('traiter', $demande);
        $donnees = $request->validate(['question' => ['required', 'string', 'max:2000']], [
            'question.required' => 'Précisez les informations attendues du demandeur.',
        ]);

        $demande = $this->traitements->demanderInformations($demande, $request->user(), $donnees['question']);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Informations demandées : la demande vous reviendra après la réponse du demandeur.');
    }

    public function mettreADisposition(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('traiter', $demande);
        $donnees = $this->validerQuantites($request, [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);

        $demande = $this->traitements->mettreADisposition(
            $demande, $request->user(), $donnees['lignes'], $donnees['date'], $donnees['observations'] ?? null
        );

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Mise à disposition déclarée : le demandeur doit maintenant confirmer la réception.');
    }

    public function signalerImpossibilite(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('traiter', $demande);
        $donnees = $request->validate(['justification' => ['required', 'string', 'max:2000']], [
            'justification.required' => 'La justification de l\'impossibilité est obligatoire.',
        ]);

        $demande = $this->traitements->signalerImpossibilite($demande, $request->user(), $donnees['justification']);

        return redirect()->route('traitement.index')
            ->with('success', "Impossibilité de traitement signalée pour {$demande->reference}.");
    }

    public function regulariser(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('regulariser', $demande);
        $donnees = $this->validerQuantites($request, [
            'explication' => ['required', 'string', 'max:2000'],
        ], ['explication.required' => 'Expliquez la régularisation effectuée.']);

        $demande = $this->traitements->regulariser($demande, $request->user(), $donnees['lignes'], $donnees['explication']);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Régularisation déclarée : le demandeur doit de nouveau confirmer la réception.');
    }

    private function validerQuantites(Request $request, array $regles, array $messages = []): array
    {
        return $request->validate($regles + [
            'lignes' => ['required', 'array'],
            'lignes.*.quantite_fournie' => ['required', 'integer', 'min:0', 'max:9999'],
            'lignes.*.observation' => ['nullable', 'string', 'max:500'],
        ], $messages + [
            'lignes.*.quantite_fournie.required' => 'Indiquez la quantité fournie pour chaque ressource.',
            'date.before_or_equal' => 'La date de mise à disposition ne peut pas être dans le futur.',
        ], [
            'lignes.*.quantite_fournie' => 'quantité fournie',
            'date' => 'date de mise à disposition',
        ]);
    }
}
