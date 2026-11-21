<?php

namespace App\Services\Demandes;

use App\Enums\TypeDestination;
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
}
