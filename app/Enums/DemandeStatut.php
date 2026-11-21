<?php

namespace App\Enums;

enum DemandeStatut: string
{
    case BROUILLON                    = 'BROUILLON';
    case EN_ATTENTE_N1                = 'EN_ATTENTE_N1';
    case EN_ATTENTE_N2                = 'EN_ATTENTE_N2';
    case EN_ATTENTE_TRAITEMENT        = 'EN_ATTENTE_TRAITEMENT';
    case EN_COURS_TRAITEMENT          = 'EN_COURS_TRAITEMENT';
    case INFO_COMPLEMENTAIRE_DEMANDEE = 'INFO_COMPLEMENTAIRE_DEMANDEE';
    case EN_ATTENTE_CONFIRMATION      = 'EN_ATTENTE_CONFIRMATION';
    case CLOTUREE                     = 'CLOTUREE';
    case REJETEE                      = 'REJETEE';
    case EN_COURS_REGULARISATION      = 'EN_COURS_REGULARISATION';

    /**
     * Libellé court pour l'affichage.
     */
    public function label(): string
    {
        return match ($this) {
            self::BROUILLON                    => 'Brouillon',
            self::EN_ATTENTE_N1                => 'En attente N+1',
            self::EN_ATTENTE_N2                => 'En attente N+2',
            self::EN_ATTENTE_TRAITEMENT        => 'En attente de traitement',
            self::EN_COURS_TRAITEMENT          => 'En cours de traitement',
            self::INFO_COMPLEMENTAIRE_DEMANDEE => 'Infos demandées',
            self::EN_ATTENTE_CONFIRMATION      => 'En attente de confirmation',
            self::CLOTUREE                     => 'Clôturée',
            self::REJETEE                      => 'Rejetée',
            self::EN_COURS_REGULARISATION      => 'En régularisation',
        };
    }

    /**
     * Classe CSS du badge (`.dash-status--xxx`, cf. components/badges.css).
     * Couleurs du tableau 2 de la SFD.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::BROUILLON                    => 'dash-status--brouillon',      // Gris
            self::EN_ATTENTE_N1                => 'dash-status--validation',     // Jaune
            self::EN_ATTENTE_N2                => 'dash-status--validation',     // Jaune
            self::EN_ATTENTE_TRAITEMENT        => 'dash-status--a-traiter',      // Bleu clair
            self::EN_COURS_TRAITEMENT          => 'dash-status--traitement',     // Bleu
            self::INFO_COMPLEMENTAIRE_DEMANDEE => 'dash-status--info',           // Orange
            self::EN_ATTENTE_CONFIRMATION      => 'dash-status--confirmation',   // Violet
            self::CLOTUREE                     => 'dash-status--cloturee',       // Vert
            self::REJETEE                      => 'dash-status--rejetee',        // Rouge
            self::EN_COURS_REGULARISATION      => 'dash-status--regularisation', // Orange foncé
        };
    }

    /**
     * États finaux (SFD §11.3).
     */
    public function estFinal(): bool
    {
        return $this === self::CLOTUREE || $this === self::REJETEE;
    }
}