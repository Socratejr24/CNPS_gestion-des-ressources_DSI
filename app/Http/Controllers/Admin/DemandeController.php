<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DemandeStatut;
use App\Enums\TypeDestination;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Departement;
use App\Services\Interfaces\IDemandeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consultation de toutes les demandes par l'administrateur (SFD §3.3.1), avec export CSV.
 * Lecture seule : les actions restent réservées aux acteurs du circuit.
 */
class DemandeController extends Controller
{
    public function __construct(private IDemandeService $demandes) {}

    public function index(Request $request): View
    {
        $filtres = $this->filtres($request);
        $finaux = collect(DemandeStatut::cases())->filter(fn (DemandeStatut $s) => $s->estFinal())->map->value->values()->all();

        $soumises = Demande::where('statut', '!=', DemandeStatut::BROUILLON);

        return view('admin.demandes.index', [
            'demandes' => $this->demandes->rechercherPourAdministration($filtres)->paginate(20)->withQueryString(),
            'filtres' => $filtres,
            'statuts' => array_values(array_filter(DemandeStatut::cases(), fn ($s) => $s !== DemandeStatut::BROUILLON)),
            'types' => TypeDestination::cases(),
            'departements' => Departement::with('structure')->orderBy('libelle')->get(),
            'compteurs' => [
                'total' => (clone $soumises)->count(),
                'en_cours' => (clone $soumises)->whereNotIn('statut', $finaux)->count(),
                'cloturees' => (clone $soumises)->where('statut', DemandeStatut::CLOTUREE)->count(),
                'echecs' => (clone $soumises)->whereIn('statut', [DemandeStatut::REJETEE, DemandeStatut::TRAITEMENT_IMPOSSIBLE])->count(),
            ],
        ]);
    }

    /** Export CSV des demandes filtrées (séparateur « ; » et BOM UTF-8 pour Excel). */
    public function export(Request $request): StreamedResponse
    {
        $requete = $this->demandes->rechercherPourAdministration($this->filtres($request))->with('lignes.ressource');
        $fichier = 'demandes-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($requete) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF");
            fputcsv($sortie, [
                'Référence', 'Objet', 'Demandeur', 'Matricule', 'Service', 'Département', 'Application',
                'Destination', 'Statut', 'Soumise le', 'Receveur', 'Mise à disposition le', 'Clôturée le', 'Ressources',
            ], ';');

            $requete->chunk(200, function ($lot) use ($sortie) {
                foreach ($lot as $d) {
                    fputcsv($sortie, [
                        $d->reference,
                        $d->objet,
                        $d->demandeur?->nomComplet(),
                        $d->demandeur?->matricule,
                        $d->serviceDemandeur?->libelle,
                        $d->serviceDemandeur?->departement?->libelle,
                        $d->application?->nom,
                        $d->libelleDestination(),
                        $d->statut->libelleComplet(),
                        $d->soumise_le?->format('d/m/Y H:i'),
                        $d->receveur?->nomComplet(),
                        $d->mise_a_disposition_le?->format('d/m/Y'),
                        $d->cloturee_le?->format('d/m/Y H:i'),
                        $d->lignes->map(fn ($l) => $l->ressource->designation.' × '.$l->quantite_demandee
                            .($l->quantite_fournie !== null ? ' (fourni : '.$l->quantite_fournie.')' : ''))->implode(' | '),
                    ], ';');
                }
            });

            fclose($sortie);
        }, $fichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtres(Request $request): array
    {
        $etats = array_merge(['EN_COURS', 'TERMINEES'], array_map(fn ($s) => $s->value, DemandeStatut::cases()));

        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'etat' => ['nullable', Rule::in($etats)],
            'departement_id' => ['nullable', 'integer'],
            'destination_type' => ['nullable', Rule::enum(TypeDestination::class)],
            'du' => ['nullable', 'date'],
            'au' => ['nullable', 'date', 'after_or_equal:du'],
        ], ['au.after_or_equal' => 'La date de fin doit suivre la date de début.']);
    }
}
