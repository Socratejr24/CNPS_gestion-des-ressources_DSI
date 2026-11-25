<?php

namespace App\Services\Implementations;

use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\User;
use App\Services\Demandes\PerimetreReceveurs;
use App\Services\Demandes\Valideurs;
use App\Services\Interfaces\ITableauDeBordService;
use App\Services\Interfaces\ITraitementService;
use Illuminate\Database\Eloquent\Builder;

class TableauDeBordService implements ITableauDeBordService
{
    /** Couleurs du tableau 2 de la SFD, pour le graphique de répartition. */
    private const COULEURS = [
        'BROUILLON' => '#9AA0BB',
        'EN_ATTENTE_N1' => '#E8B500',
        'EN_ATTENTE_N2' => '#F6D01D',
        'EN_ATTENTE_TRAITEMENT' => '#5AA9E6',
        'EN_COURS_TRAITEMENT' => '#27357E',
        'INFO_COMPLEMENTAIRE_DEMANDEE' => '#DE7C0F',
        'EN_ATTENTE_CONFIRMATION' => '#7C4DBC',
        'CLOTUREE' => '#088E39',
        'REJETEE' => '#C8102E',
        'EN_COURS_REGULARISATION' => '#A34A00',
        'TRAITEMENT_IMPOSSIBLE' => '#6E0A1A',
    ];

    public function __construct(private ITraitementService $traitements) {}

    public function pour(User $utilisateur): array
    {
        // L'administrateur consulte toutes les demandes (SFD §3.3.1), les autres les leurs (RG-34).
        $global = $utilisateur->estAdministrateur();
        $perimetre = fn (): Builder => $global
            ? Demande::where('statut', '!=', DemandeStatut::BROUILLON)
            : Demande::where('demandeur_id', $utilisateur->id);

        $parStatut = $perimetre()->selectRaw('statut, COUNT(*) as nombre')->groupBy('statut')->pluck('nombre', 'statut')
            ->map(fn ($n) => (int) $n);
        $somme = fn (DemandeStatut ...$s) => collect($s)->sum(fn (DemandeStatut $x) => $parStatut[$x->value] ?? 0);

        return [
            'global' => $global,
            'kpis' => [
                ['label' => 'En validation', 'valeur' => $somme(DemandeStatut::EN_ATTENTE_N1, DemandeStatut::EN_ATTENTE_N2), 'aide' => 'Chez le N+1 ou le N+2', 'couleur' => 'orange', 'icone' => 'clock'],
                ['label' => 'En traitement', 'valeur' => $somme(DemandeStatut::EN_ATTENTE_TRAITEMENT, DemandeStatut::EN_COURS_TRAITEMENT, DemandeStatut::EN_COURS_REGULARISATION), 'aide' => 'Chez le receveur', 'couleur' => 'blue', 'icone' => 'refresh'],
                ['label' => $global ? 'Chez le demandeur' : 'Action requise', 'valeur' => $somme(DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE, DemandeStatut::EN_ATTENTE_CONFIRMATION), 'aide' => 'Infos à fournir ou réception à confirmer', 'couleur' => 'violet', 'icone' => 'alert-circle'],
                ['label' => 'Clôturées', 'valeur' => $somme(DemandeStatut::CLOTUREE), 'aide' => $somme(DemandeStatut::REJETEE, DemandeStatut::TRAITEMENT_IMPOSSIBLE).' rejetée(s) ou impossible(s)', 'couleur' => 'green', 'icone' => 'check-circle'],
            ],
            'travail' => $this->travail($utilisateur, $somme(DemandeStatut::BROUILLON)),
            'statuts' => $this->repartition($parStatut),
            'evolution' => $this->evolution($perimetre()),
            'recentes' => $perimetre()->latest('updated_at')->latest('id')->take(5)->get(),
            'notifications' => $utilisateur->notifications()->take(5)->get(),
        ];
    }

    /** Tuiles « à faire » selon les rôles et responsabilités de l'utilisateur. */
    private function travail(User $utilisateur, int $brouillons): array
    {
        $tuiles = [];

        if ($utilisateur->estValidateur()) {
            $tuiles[] = ['label' => 'Demandes à valider', 'valeur' => Valideurs::aValiderPar($utilisateur)->count(),
                'route' => route('validations.index'), 'icone' => 'check-circle', 'couleur' => 'orange'];
        }

        if (PerimetreReceveurs::estReceveur($utilisateur)) {
            $compteurs = $this->traitements->compteurs($utilisateur);
            $tuiles[] = ['label' => 'À prendre en charge', 'valeur' => $compteurs['a-prendre'],
                'route' => route('traitement.index'), 'icone' => 'inbox', 'couleur' => 'blue'];
            $tuiles[] = ['label' => 'Mes traitements en cours', 'valeur' => $compteurs['en-cours'],
                'route' => route('traitement.index', ['onglet' => 'en-cours']), 'icone' => 'refresh', 'couleur' => 'blue'];
        }

        if ($brouillons > 0) {
            $tuiles[] = ['label' => 'Brouillons à soumettre', 'valeur' => $brouillons,
                'route' => route('demandes.index', ['statut' => DemandeStatut::BROUILLON->value]), 'icone' => 'edit', 'couleur' => 'gray'];
        }

        return $tuiles;
    }

    private function repartition($parStatut): array
    {
        $statuts = collect(DemandeStatut::cases())->filter(fn (DemandeStatut $s) => ($parStatut[$s->value] ?? 0) > 0)->values();

        return [
            'labels' => $statuts->map(fn (DemandeStatut $s) => $s->label())->all(),
            'series' => $statuts->map(fn (DemandeStatut $s) => $parStatut[$s->value])->all(),
            'colors' => $statuts->map(fn (DemandeStatut $s) => self::COULEURS[$s->value])->all(),
        ];
    }

    /** Demandes créées sur les 6 derniers mois (calcul en PHP : indépendant du moteur SQL). */
    private function evolution(Builder $requete): array
    {
        $debut = now()->startOfMonth()->subMonths(5);
        $dates = $requete->where('created_at', '>=', $debut)->pluck('created_at');

        $mois = collect(range(0, 5))->map(fn (int $i) => $debut->copy()->addMonths($i));

        return [
            'labels' => $mois->map(fn ($m) => ucfirst($m->locale('fr')->isoFormat('MMM')))->all(),
            'series' => [
                'name' => 'Demandes créées',
                'data' => $mois->map(fn ($m) => $dates->filter(fn ($d) => $d->isSameMonth($m))->count())->all(),
            ],
        ];
    }
}
