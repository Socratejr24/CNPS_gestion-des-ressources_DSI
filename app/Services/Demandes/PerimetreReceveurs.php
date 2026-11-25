<?php

namespace App\Services\Demandes;

use App\Enums\TypeDestination;
use App\Models\Demande;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Receveurs autorisés à traiter une destination (arbitrage SFD §8.1 / RG-34) :
 *  - utilisateur  : cet utilisateur (qui doit avoir le rôle Receveur) ;
 *  - service      : les Receveurs affectés à ce service ;
 *  - département  : les Receveurs affectés à un service de ce département.
 */
final class PerimetreReceveurs
{
    /** Utilisateurs actifs ayant le rôle Receveur actif. */
    public static function receveurs(): Builder
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->where('libelle', Role::RECEVEUR)->where('is_active', true));
    }

    /** Receveurs autorisés pour une destination donnée. */
    public static function pour(TypeDestination $type, int $destinationId): Builder
    {
        return match ($type) {
            TypeDestination::UTILISATEUR => self::receveurs()->whereKey($destinationId),
            TypeDestination::SERVICE => self::receveurs()->where('service_id', $destinationId),
            TypeDestination::DEPARTEMENT => self::receveurs()->whereHas('service', fn (Builder $q) => $q->where('departement_id', $destinationId)),
        };
    }

    /** L'utilisateur est-il un receveur actif ? */
    public static function estReceveur(User $utilisateur): bool
    {
        return $utilisateur->is_active && $utilisateur->hasRole(Role::RECEVEUR);
    }

    /** L'utilisateur est-il un receveur autorisé pour la destination de cette demande ? */
    public static function couvre(User $utilisateur, Demande $demande): bool
    {
        if (! self::estReceveur($utilisateur) || $demande->destination_type === null) {
            return false;
        }

        $utilisateur->loadMissing('service');

        return match ($demande->destination_type) {
            TypeDestination::UTILISATEUR => $demande->destination_utilisateur_id === $utilisateur->id,
            TypeDestination::SERVICE => $utilisateur->service_id !== null && $demande->destination_service_id === $utilisateur->service_id,
            TypeDestination::DEPARTEMENT => $utilisateur->service?->departement_id !== null
                && $demande->destination_departement_id === $utilisateur->service->departement_id,
        };
    }

    /** Demandes dont la destination relève du périmètre de ce receveur (tous statuts). */
    public static function demandesPour(User $receveur): Builder
    {
        $receveur->loadMissing('service');
        $serviceId = $receveur->service_id;
        $departementId = $receveur->service?->departement_id;

        return Demande::query()->where(fn (Builder $q) => $q
            ->where(fn (Builder $q) => $q->where('destination_type', TypeDestination::UTILISATEUR)->where('destination_utilisateur_id', $receveur->id))
            ->when($serviceId, fn (Builder $q) => $q->orWhere(fn (Builder $q) => $q
                ->where('destination_type', TypeDestination::SERVICE)->where('destination_service_id', $serviceId)))
            ->when($departementId, fn (Builder $q) => $q->orWhere(fn (Builder $q) => $q
                ->where('destination_type', TypeDestination::DEPARTEMENT)->where('destination_departement_id', $departementId)))
        );
    }
}
