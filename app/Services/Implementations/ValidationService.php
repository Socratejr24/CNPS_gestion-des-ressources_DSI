<?php

namespace App\Services\Implementations;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Exceptions\RegleMetierException;
use App\Models\Demande;
use App\Models\User;
use App\Services\Demandes\Complements;
use App\Services\Demandes\Journal;
use App\Services\Demandes\Valideurs;
use App\Services\Interfaces\IValidationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ValidationService implements IValidationService
{
    private const RELATIONS = ['demandeur', 'serviceDemandeur', 'application'];

    // ==================================================================
    // Listes
    // ==================================================================

    public function listerAValider(User $valideur, array $filtres = [], int $parPage = 15): LengthAwarePaginator
    {
        return $this->filtrer(Valideurs::aValiderPar($valideur), $filtres)
            ->with(self::RELATIONS)
            ->orderBy('soumise_le')   // les plus anciennes d'abord
            ->paginate($parPage)
            ->withQueryString();
    }

    public function listerTraiteesPar(User $valideur, array $filtres = [], int $parPage = 15): LengthAwarePaginator
    {
        $actions = [ActionDemande::VALIDATION, ActionDemande::REJET, ActionDemande::DEMANDE_INFORMATIONS];

        return $this->filtrer(Demande::query(), $filtres)
            ->whereHas('evenements', fn (Builder $q) => $q
                ->where('utilisateur_id', $valideur->id)
                ->whereNotNull('niveau')
                ->whereIn('action', $actions))
            ->with(self::RELATIONS)
            ->latest('updated_at')
            ->paginate($parPage)
            ->withQueryString();
    }

    public function compterAValider(User $valideur): int
    {
        return Valideurs::aValiderPar($valideur)->count();
    }

    // ==================================================================
    // Décisions
    // ==================================================================

    public function valider(Demande $demande, User $valideur, ?string $commentaire = null): Demande
    {
        $niveau = $this->exigerValideur($demande, $valideur);
        $ancien = $demande->statut;

        // Étape suivante, déterminée avant toute écriture (un N+2 vacant bloque la validation N+1).
        $n2CouvertParN1 = false;
        if ($niveau === 'N1') {
            $responsable = $demande->serviceDemandeur?->departement?->responsable;

            if ($responsable?->is($valideur) || $responsable?->is($demande->demandeur)) {
                $n2CouvertParN1 = true;
                $suivant = DemandeStatut::EN_ATTENTE_TRAITEMENT;
            } elseif ($responsable === null || ! $responsable->is_active) {
                throw new RegleMetierException(
                    'Le département du demandeur n\'a pas de responsable (N+2) en fonction : la demande ne peut pas lui être transmise. Contactez l\'administrateur.'
                );
            } else {
                $suivant = DemandeStatut::EN_ATTENTE_N2;
            }
        } else {
            $suivant = DemandeStatut::EN_ATTENTE_TRAITEMENT;
        }

        return DB::transaction(function () use ($demande, $valideur, $commentaire, $niveau, $ancien, $suivant, $n2CouvertParN1) {
            $demande->statut = $suivant;
            $demande->save();

            Journal::ajouter($demande, $valideur, ActionDemande::VALIDATION, $ancien, $suivant, $this->texte($commentaire), $niveau);

            if ($n2CouvertParN1) {
                Journal::ajouter($demande, $valideur, ActionDemande::VALIDATION, $suivant, $suivant,
                    'Validation N+2 couverte par la validation N+1 (même personne, ou demandeur responsable du département).', 'N2');
            }

            // RG-22 : après la dernière validation, transmission à l'unique destinataire.
            if ($suivant === DemandeStatut::EN_ATTENTE_TRAITEMENT) {
                Journal::ajouter($demande, $valideur, ActionDemande::TRANSMISSION, $suivant, $suivant, $demande->libelleDestination());
            }

            return $demande->refresh();
        });
    }

    public function rejeter(Demande $demande, User $valideur, string $motif): Demande
    {
        $niveau = $this->exigerValideur($demande, $valideur);

        if (blank($motif)) {
            throw new RegleMetierException('Le motif du rejet est obligatoire (RG-21).');
        }

        return DB::transaction(function () use ($demande, $valideur, $motif, $niveau) {
            $ancien = $demande->statut;
            $demande->statut = DemandeStatut::REJETEE;
            $demande->save();

            Journal::ajouter($demande, $valideur, ActionDemande::REJET, $ancien, DemandeStatut::REJETEE, trim($motif), $niveau);

            return $demande->refresh();
        });
    }

    public function demanderInformations(Demande $demande, User $valideur, string $question): Demande
    {
        $niveau = $this->exigerValideur($demande, $valideur);

        return DB::transaction(function () use ($demande, $valideur, $question, $niveau) {
            Complements::ouvrir($demande, $valideur, $question, $niveau);

            return $demande->refresh();
        });
    }

    // ==================================================================

    /** @return string niveau de validation en cours (N1 ou N2) */
    private function exigerValideur(Demande $demande, User $valideur): string
    {
        $niveau = Valideurs::niveau($demande);

        if ($niveau === null) {
            throw new RegleMetierException('Cette demande n\'est pas en attente de validation hiérarchique.');
        }
        if (! Valideurs::estValideurAttendu($valideur, $demande)) {
            throw new RegleMetierException('Vous n\'êtes pas le valideur attendu pour cette étape.');
        }

        return $niveau;
    }

    private function filtrer(Builder $requete, array $filtres): Builder
    {
        return $requete->when($filtres['q'] ?? null, function (Builder $q, string $terme) {
            $motif = '%'.$terme.'%';
            $q->where(fn (Builder $q) => $q
                ->where('reference', 'LIKE', $motif)
                ->orWhere('objet', 'LIKE', $motif)
                ->orWhereHas('demandeur', fn (Builder $q) => $q->where('nom', 'LIKE', $motif)->orWhere('prenom', 'LIKE', $motif)));
        });
    }

    private function texte(?string $valeur): ?string
    {
        return filled($valeur) ? trim($valeur) : null;
    }
}
