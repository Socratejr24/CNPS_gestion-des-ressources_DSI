<?php

namespace App\Enums;

/**
 * Catégories d'utilisateurs (SFD §3.1).
 */
enum CategorieUtilisateur: string
{
    /** Agent CNPS authentifié via LDAP (RG-07). */
    case AGENT = 'AGENT';

    /** Prestataire avec compte local créé par l'administrateur (RG-08). */
    case PRESTATAIRE = 'PRESTATAIRE';

    public function label(): string
    {
        return match ($this) {
            self::AGENT => 'Agent CNPS',
            self::PRESTATAIRE => 'Prestataire',
        };
    }
}
