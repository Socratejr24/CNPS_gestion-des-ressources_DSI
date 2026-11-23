<?php

namespace App\Http\Controllers;

use App\Enums\DemandeStatut;
use App\Enums\TypeDestination;
use App\Exceptions\RegleMetierException;
use App\Http\Requests\DemandeRequest;
use App\Models\Demande;
use App\Models\Departement;
use App\Models\Service;
use App\Services\Demandes\CircuitValidation;
use App\Services\Demandes\PerimetreReceveurs;
use App\Services\Demandes\SuiviCircuit;
use App\Services\Demandes\Valideurs;
use App\Services\Interfaces\IDemandeService;
use App\Services\Interfaces\IReferentielService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Demandes côté demandeur : liste, création, brouillons, soumission, détail (SFD §6, §7.1).
 */
class DemandeController extends Controller
{
    public function __construct(
        private IDemandeService $demandes,
        private IReferentielService $referentiels,
    ) {}

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', Rule::enum(DemandeStatut::class)],
        ]);
        $utilisateur = $request->user();

        return view('demandes.index', [
            'demandes' => $this->demandes->listerPourDemandeur($utilisateur, $filtres),
            'compteurs' => $this->demandes->compteursDemandeur($utilisateur),
            'statuts' => DemandeStatut::cases(),
            'filtres' => $filtres,
        ]);
    }

    public function create(Request $request): View
    {
        return view('demandes.form', $this->donneesFormulaire($request, null));
    }

    public function store(DemandeRequest $request): RedirectResponse
    {
        $demande = $this->demandes->creer($request->user(), $request->donnees());

        return $request->veutSoumettre()
            ? $this->tenterSoumission($request, $demande)
            : redirect()->route('demandes.show', $demande)->with('success', "Brouillon {$demande->reference} enregistré.");
    }

    public function show(Demande $demande): View
    {
        Gate::authorize('view', $demande);

        $demande->load([
            'demandeur.service.departement', 'application', 'lignes.ressource',
            'destinationDepartement', 'destinationService', 'destinationUtilisateur.service',
            'serviceDemandeur.chef', 'serviceDemandeur.departement.responsable',
            'evenements.utilisateur',
        ]);

        $utilisateur = request()->user();
        $estValidation = $demande->statut->estEnValidation();

        return view('demandes.show', [
            'demande' => $demande,
            'etapes' => SuiviCircuit::etapes($demande),
            'estDemandeur' => $demande->demandeur_id === $utilisateur->id,
            'peutDecider' => $utilisateur->can('decider', $demande),
            'peutRepondre' => $utilisateur->can('repondre', $demande),
            'niveau' => Valideurs::niveau($demande),
            // Poste N+1 / N+2 devenu vacant après la soumission : personne ne peut statuer.
            'posteVacant' => $estValidation && Valideurs::attendu($demande) === null,
            'question' => $demande->derniereQuestion(),
        ]);
    }

    public function edit(Request $request, Demande $demande): View
    {
        Gate::authorize('update', $demande);

        return view('demandes.form', $this->donneesFormulaire($request, $demande->load('lignes')));
    }

    public function update(DemandeRequest $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('update', $demande);

        $demande = $this->demandes->modifier($demande, $request->donnees(), $request->user());

        return $request->veutSoumettre()
            ? $this->tenterSoumission($request, $demande)
            : redirect()->route('demandes.show', $demande)->with('success', 'Brouillon mis à jour.');
    }

    public function destroy(Demande $demande): RedirectResponse
    {
        Gate::authorize('delete', $demande);

        $reference = $demande->reference;
        $this->demandes->supprimer($demande);

        return redirect()->route('demandes.index')->with('success', "Brouillon {$reference} supprimé.");
    }

    public function soumettre(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('soumettre', $demande);

        return $this->tenterSoumission($request, $demande);
    }

    /** Réponse à une demande d'informations complémentaires (SFD §10.3). */
    public function repondre(Request $request, Demande $demande): RedirectResponse
    {
        Gate::authorize('repondre', $demande);
        $donnees = $request->validate(['reponse' => ['required', 'string', 'max:5000']], [
            'reponse.required' => 'Saisissez votre réponse.',
        ]);

        $demande = $this->demandes->repondreAuComplement($demande, $request->user(), $donnees['reponse']);

        return redirect()->route('demandes.show', $demande)
            ->with('success', "Réponse envoyée : la demande est de nouveau « {$demande->statut->label()} ».");
    }

    // ------------------------------------------------------------------

    /**
     * Soumet ; en cas de refus (poste vacant, contenu incomplet…), le brouillon reste
     * enregistré et l'utilisateur est renvoyé vers sa modification avec le motif.
     */
    private function tenterSoumission(Request $request, Demande $demande): RedirectResponse
    {
        try {
            $demande = $this->demandes->soumettre($demande, $request->user());
        } catch (RegleMetierException $e) {
            return redirect()->route('demandes.edit', $demande)
                ->withErrors(['regle_metier' => $e->getMessage()])
                ->with('warning', 'Brouillon enregistré, mais non soumis.');
        }

        return redirect()->route('demandes.show', $demande)
            ->with('success', "Demande {$demande->reference} soumise : {$demande->statut->label()}.");
    }

    private function donneesFormulaire(Request $request, ?Demande $demande): array
    {
        $utilisateur = $request->user();

        return [
            'demande' => $demande,
            'applications' => $this->referentiels->applicationsActives(),
            'ressources' => $this->referentiels->ressourcesActives()->groupBy('categorie'),
            'departements' => Departement::with('structure')->where('is_active', true)->orderBy('libelle')->get()
                ->groupBy(fn ($d) => $d->structure?->libelle ?? '—')->sortKeys(),
            'services' => Service::with('departement')->where('is_active', true)->orderBy('libelle')->get()
                ->groupBy(fn ($s) => $s->departement?->libelle ?? '—')->sortKeys(),
            'receveurs' => PerimetreReceveurs::receveurs()->with('service')->whereNotNull('service_id')
                ->whereKeyNot($utilisateur->id)->orderBy('nom')->orderBy('prenom')->get(),
            'types' => TypeDestination::cases(),
            'circuit' => CircuitValidation::evaluer($utilisateur),
        ];
    }
}
