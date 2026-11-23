<?php

namespace App\Services\Demandes;

use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\Departement;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Valideurs hiérarchiques d'une demande (RG-15, RG-16).
 *
 * Le circuit part du service du demandeur figé à la soumission ; les titulaires
 * sont lus au moment de l'action (remplacement d'un chef pris en compte).
 */
final class Valideurs
{
    /** Valideur attendu pour l'étape en cours, ou null (étape non hiérarchique ou poste vacant). */
    public static function attendu(Demande $demande): ?User
    {
        $demande->loadMissing(['serviceDemandeur.chef', 'serviceDemandeur.departement.responsable']);

        return match ($demande->statut) {
            DemandeStatut::EN_ATTENTE_N1 => $demande->serviceDemandeur?->chef,
            DemandeStatut::EN_ATTENTE_N2 => $demande->serviceDemandeur?->departement?->responsable,
            default => null,
        };
    }

    /** Niveau de l'étape de validation en cours : N1, N2 ou null. */
    public static function niveau(Demande $demande): ?string
    {
        return match ($demande->statut) {
            DemandeStatut::EN_ATTENTE_N1 => 'N1',
            DemandeStatut::EN_ATTENTE_N2 => 'N2',
            default => null,
        };
    }

    public static function estValideurAttendu(User $utilisateur, Demande $demande): bool
    {
        $valideur = self::attendu($demande);

        return $valideur !== null
            && $valideur->is($utilisateur)
            && $utilisateur->is_active
            && $demande->demandeur_id !== $utilisateur->id;
    }

    /** Demandes qui attendent la décision de cet utilisateur (écran « Demandes à valider »). */
    public static function aValiderPar(User $utilisateur): Builder
    {
        $servicesDiriges = Service::where('chef_id', $utilisateur->id)->select('id');
        $servicesDesDepartementsDiriges = Service::whereIn(
            'departement_id', Departement::where('responsable_id', $utilisateur->id)->select('id')
        )->select('id');

        return Demande::query()
            ->where('demandeur_id', '!=', $utilisateur->id)
            ->where(fn (Builder $q) => $q
                ->where(fn (Builder $q) => $q->where('statut', DemandeStatut::EN_ATTENTE_N1)->whereIn('service_demandeur_id', $servicesDiriges))
                ->orWhere(fn (Builder $q) => $q->where('statut', DemandeStatut::EN_ATTENTE_N2)->whereIn('service_demandeur_id', $servicesDesDepartementsDiriges))
            );
    }
}
