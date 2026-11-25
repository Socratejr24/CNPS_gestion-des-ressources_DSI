<?php

namespace App\Enums;

/**
 * Nature d'une anomalie de réception (SFD §9.2).
 */
enum NatureAnomalie: string
{
    case RESSOURCE_MANQUANTE = 'RESSOURCE_MANQUANTE';
    case QUANTITE_INCORRECTE = 'QUANTITE_INCORRECTE';
    case NON_CONFORME = 'NON_CONFORME';
    case AUTRE = 'AUTRE';

    public function label(): string
    {
        return match ($this) {
            self::RESSOURCE_MANQUANTE => 'Ressource manquante',
            self::QUANTITE_INCORRECTE => 'Quantité incorrecte',
            self::NON_CONFORME => 'Ressource défectueuse ou non conforme',
            self::AUTRE => 'Autre',
        };
    }
}
