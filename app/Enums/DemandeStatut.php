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
     * Classe CSS pour le badge.
     * Utilise les classes `.dash-status--xxx`.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::BROUILLON                    => 'dash-status--brouillon',
            self::EN_ATTENTE_N1                => 'dash-status--attente',
            self::EN_ATTENTE_N2                => 'dash-status--attente',
            self::EN_ATTENTE_TRAITEMENT        => 'dash-status--cours',
            self::EN_COURS_TRAITEMENT          => 'dash-status--cours',
            self::INFO_COMPLEMENTAIRE_DEMANDEE => 'dash-status--attente',
            self::EN_ATTENTE_CONFIRMATION      => 'dash-status--attente',
            self::CLOTUREE                     => 'dash-status--validee',
            self::REJETEE                      => 'dash-status--rejetee',
            self::EN_COURS_REGULARISATION      => 'dash-status--attente',
        };
    }
}