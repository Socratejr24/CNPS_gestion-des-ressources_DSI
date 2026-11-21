<?php

namespace App\Enums;

/**
 * Destination opérationnelle d'une demande (SFD §6.3, RG-13) : une seule par demande.
 */
enum TypeDestination: string
{
    case DEPARTEMENT = 'DEPARTEMENT';
    case SERVICE = 'SERVICE';
    case UTILISATEUR = 'UTILISATEUR';

    public function label(): string
    {
        return match ($this) {
            self::DEPARTEMENT => 'Département',
            self::SERVICE => 'Service',
            self::UTILISATEUR => 'Utilisateur',
        };
    }
}
