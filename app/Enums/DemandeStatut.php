<?php

namespace App\Enums;

/**
 * Statuts d'une demande (SFD §11, tableau 2).
 *
 * TRAITEMENT_IMPOSSIBLE est un 11e statut, ajouté pour trancher le point laissé
 * ouvert par la SFD (§8.3 / §11.2 « signaler une impossibilité ») : état final.
 */
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
    case TRAITEMENT_IMPOSSIBLE        = 'TRAITEMENT_IMPOSSIBLE';

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
            self::TRAITEMENT_IMPOSSIBLE        => 'Traitement impossible',
        };
    }

    /**
     * Libellé complet (SFD tableau 2), pour les pages de détail.
     */
    public function libelleComplet(): string
    {
        return match ($this) {
            self::INFO_COMPLEMENTAIRE_DEMANDEE => 'Informations complémentaires demandées',
            self::EN_ATTENTE_CONFIRMATION      => 'En attente de confirmation de réception',
            self::EN_COURS_REGULARISATION      => 'En cours de régularisation',
            default                            => $this->label(),
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
            self::TRAITEMENT_IMPOSSIBLE        => 'dash-status--impossible',     // Rouge foncé
        };
    }

    /**
     * États finaux (SFD §11.3, RG-32) + l'impossibilité de traitement.
     */
    public function estFinal(): bool
    {
        return in_array($this, [self::CLOTUREE, self::REJETEE, self::TRAITEMENT_IMPOSSIBLE], true);
    }

    /** En cours de validation hiérarchique. */
    public function estEnValidation(): bool
    {
        return $this === self::EN_ATTENTE_N1 || $this === self::EN_ATTENTE_N2;
    }
}
