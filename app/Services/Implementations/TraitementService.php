<?php

namespace App\Services\Implementations;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Exceptions\RegleMetierException;
use App\Models\Demande;
use App\Models\User;
use App\Services\Demandes\Complements;
use App\Services\Demandes\Journal;
use App\Services\Demandes\PerimetreReceveurs;
use App\Services\Interfaces\ITraitementService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TraitementService implements ITraitementService
{
    private const RELATIONS = ['demandeur', 'serviceDemandeur', 'application', 'destinationDepartement', 'destinationService', 'destinationUtilisateur'];

    // ==================================================================
    // Listes
    // ==================================================================

    public function lister(User $receveur, string $onglet, array $filtres = [], int $parPage = 15): LengthAwarePaginator
    {
        $requete = match ($onglet) {
            'en-cours' => $this->requeteEnCours($receveur),
            'terminees' => Demande::where('receveur_id', $receveur->id)->whereIn('statut', $this->statutsFinaux()),
            default => $this->requeteAPrendre($receveur),
        };

        return $requete
            ->when($filtres['q'] ?? null, function (Builder $q, string $terme) {
                $motif = '%'.$terme.'%';
                $q->where(fn (Builder $q) => $q
                    ->where('reference', 'LIKE', $motif)
                    ->orWhere('objet', 'LIKE', $motif)
                    ->orWhereHas('demandeur', fn (Builder $q) => $q->where('nom', 'LIKE', $motif)->orWhere('prenom', 'LIKE', $motif)));
            })
            ->with(self::RELATIONS)
            ->orderBy($onglet === 'terminees' ? 'updated_at' : 'soumise_le', $onglet === 'terminees' ? 'desc' : 'asc')
            ->paginate($parPage)
            ->withQueryString();
    }

    public function compteurs(User $receveur): array
    {
        return [
            'a-prendre' => $this->requeteAPrendre($receveur)->count(),
            'en-cours' => $this->requeteEnCours($receveur)->count(),
        ];
    }

    private function requeteAPrendre(User $receveur): Builder
    {
        if (! PerimetreReceveurs::estReceveur($receveur)) {
            return Demande::query()->whereRaw('1 = 0');
        }

        return PerimetreReceveurs::demandesPour($receveur)
            ->where('statut', DemandeStatut::EN_ATTENTE_TRAITEMENT)
            ->where('demandeur_id', '!=', $receveur->id);
    }

    private function requeteEnCours(User $receveur): Builder
    {
        return Demande::where('receveur_id', $receveur->id)
            ->whereNotIn('statut', $this->statutsFinaux());
    }

    private function statutsFinaux(): array
    {
        return array_values(array_filter(DemandeStatut::cases(), fn (DemandeStatut $s) => $s->estFinal()));
    }

    // ==================================================================
    // Actions du receveur
    // ==================================================================

    public function prendreEnCharge(Demande $demande, User $receveur): Demande
    {
        $this->exigerStatut($demande, DemandeStatut::EN_ATTENTE_TRAITEMENT, 'n\'est pas en attente de prise en charge');

        if (! PerimetreReceveurs::couvre($receveur, $demande)) {
            throw new RegleMetierException('Cette demande n\'est pas adressée à votre périmètre : vous ne pouvez pas la prendre en charge.');
        }
        if ($demande->demandeur_id === $receveur->id) {
            throw new RegleMetierException('Vous ne pouvez pas traiter votre propre demande.');
        }

        return DB::transaction(function () use ($demande, $receveur) {
            // Verrou : deux receveurs du même service ne peuvent pas prendre la même demande.
            $verrouillee = Demande::whereKey($demande->id)->lockForUpdate()->first();
            if ($verrouillee->statut !== DemandeStatut::EN_ATTENTE_TRAITEMENT) {
                throw new RegleMetierException('Cette demande vient d\'être prise en charge par un autre receveur.');
            }

            $demande->receveur_id = $receveur->id;
            $demande->prise_en_charge_le = now();
            $demande->statut = DemandeStatut::EN_COURS_TRAITEMENT;
            $demande->save();

            Journal::ajouter($demande, $receveur, ActionDemande::PRISE_EN_CHARGE,
                DemandeStatut::EN_ATTENTE_TRAITEMENT, DemandeStatut::EN_COURS_TRAITEMENT);

            return $demande->refresh();
        });
    }

    public function demanderInformations(Demande $demande, User $receveur, string $question): Demande
    {
        $this->exigerReceveurAttitre($demande, $receveur);
        $this->exigerStatut($demande, DemandeStatut::EN_COURS_TRAITEMENT, 'n\'est pas en cours de traitement');

        return DB::transaction(function () use ($demande, $receveur, $question) {
            Complements::ouvrir($demande, $receveur, $question);

            return $demande->refresh();
        });
    }

    public function mettreADisposition(Demande $demande, User $receveur, array $lignes, string $date, ?string $observations): Demande
    {
        $this->exigerReceveurAttitre($demande, $receveur);
        $this->exigerStatut($demande, DemandeStatut::EN_COURS_TRAITEMENT, 'n\'est pas en cours de traitement');
        $dateMiseADisposition = $this->dateValide($date);

        return DB::transaction(function () use ($demande, $receveur, $lignes, $dateMiseADisposition, $observations) {
            $resume = $this->enregistrerQuantites($demande, $lignes);

            $demande->mise_a_disposition_le = $dateMiseADisposition;
            $demande->observations_mise_a_disposition = filled($observations) ? trim($observations) : null;
            $demande->statut = DemandeStatut::EN_ATTENTE_CONFIRMATION;
            $demande->save();

            Journal::ajouter($demande, $receveur, ActionDemande::MISE_A_DISPOSITION,
                DemandeStatut::EN_COURS_TRAITEMENT, DemandeStatut::EN_ATTENTE_CONFIRMATION,
                trim($resume.(filled($observations) ? "\n".trim($observations) : '')));

            return $demande->refresh();
        });
    }

    public function signalerImpossibilite(Demande $demande, User $receveur, string $justification): Demande
    {
        $this->exigerReceveurAttitre($demande, $receveur);
        $this->exigerStatut($demande, DemandeStatut::EN_COURS_TRAITEMENT, 'n\'est pas en cours de traitement');

        if (blank($justification)) {
            throw new RegleMetierException('La justification de l\'impossibilité est obligatoire.');
        }

        return DB::transaction(function () use ($demande, $receveur, $justification) {
            $demande->statut = DemandeStatut::TRAITEMENT_IMPOSSIBLE;
            $demande->save();

            Journal::ajouter($demande, $receveur, ActionDemande::IMPOSSIBILITE,
                DemandeStatut::EN_COURS_TRAITEMENT, DemandeStatut::TRAITEMENT_IMPOSSIBLE, trim($justification));

            return $demande->refresh();
        });
    }

    public function regulariser(Demande $demande, User $receveur, array $lignes, string $explication): Demande
    {
        $this->exigerReceveurAttitre($demande, $receveur);
        $this->exigerStatut($demande, DemandeStatut::EN_COURS_REGULARISATION, 'n\'est pas en cours de régularisation');

        if (blank($explication)) {
            throw new RegleMetierException('Expliquez la régularisation effectuée.');
        }

        return DB::transaction(function () use ($demande, $receveur, $lignes, $explication) {
            $resume = $this->enregistrerQuantites($demande, $lignes);

            $anomalie = $demande->anomalies()->whereNull('regularisee_le')->latest('id')->first();
            if ($anomalie) {
                $anomalie->regularisee_par_id = $receveur->id;
                $anomalie->regularisee_le = now();
                $anomalie->explication_regularisation = trim($explication);
                $anomalie->save();
            }

            // RG-30 / RG-31 : retour en attente de confirmation, sans reprise des validations.
            $demande->statut = DemandeStatut::EN_ATTENTE_CONFIRMATION;
            $demande->save();

            Journal::ajouter($demande, $receveur, ActionDemande::REGULARISATION,
                DemandeStatut::EN_COURS_REGULARISATION, DemandeStatut::EN_ATTENTE_CONFIRMATION,
                trim($explication)."\n".$resume);

            return $demande->refresh();
        });
    }

    // ==================================================================

    /**
     * Enregistre les quantités fournies (RG-27) et renvoie un résumé pour l'historique.
     * Au moins une ressource doit être fournie : sinon, c'est une impossibilité de traitement.
     */
    private function enregistrerQuantites(Demande $demande, array $saisies): string
    {
        $demande->loadMissing('lignes.ressource');

        if ($demande->lignes->isEmpty()) {
            throw new RegleMetierException('Cette demande ne comporte aucune ressource.');
        }

        $total = 0;
        $resume = [];
        foreach ($demande->lignes as $ligne) {
            $saisie = $saisies[$ligne->id] ?? null;
            if ($saisie === null || ! isset($saisie['quantite_fournie'])) {
                throw new RegleMetierException("Indiquez la quantité fournie pour « {$ligne->ressource->designation} ».");
            }

            $quantite = (int) $saisie['quantite_fournie'];
            if ($quantite < 0 || $quantite > 9999) {
                throw new RegleMetierException("Quantité invalide pour « {$ligne->ressource->designation} ».");
            }

            $ligne->quantite_fournie = $quantite;
            $ligne->observation = filled($saisie['observation'] ?? null) ? trim($saisie['observation']) : null;
            $ligne->save();

            $total += $quantite;
            $resume[] = "{$ligne->ressource->designation} : {$quantite} / {$ligne->quantite_demandee}";
        }

        if ($total === 0) {
            throw new RegleMetierException(
                'Aucune ressource n\'est fournie. Si la demande ne peut pas être satisfaite, utilisez « Signaler une impossibilité ».'
            );
        }

        return 'Quantités fournies — '.implode(' ; ', $resume);
    }

    private function exigerReceveurAttitre(Demande $demande, User $receveur): void
    {
        if ($demande->receveur_id !== $receveur->id || ! PerimetreReceveurs::estReceveur($receveur)) {
            throw new RegleMetierException('Seul le receveur qui a pris en charge cette demande peut la traiter.');
        }
    }

    private function exigerStatut(Demande $demande, DemandeStatut $attendu, string $sinon): void
    {
        if ($demande->statut !== $attendu) {
            throw new RegleMetierException("Action impossible : la demande {$sinon} (statut actuel : {$demande->statut->label()}).");
        }
    }

    private function dateValide(string $date): Carbon
    {
        try {
            $valeur = Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            throw new RegleMetierException('Date de mise à disposition invalide.');
        }

        if ($valeur->isFuture()) {
            throw new RegleMetierException('La date de mise à disposition ne peut pas être dans le futur.');
        }

        return $valeur;
    }
}
