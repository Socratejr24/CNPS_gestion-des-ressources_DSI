<?php

namespace App\Enums;

/**
 * Actions tracées dans l'historique d'une demande (SFD §13, RG-33).
 */
enum ActionDemande: string
{
    case CREATION = 'CREATION';
    case MODIFICATION = 'MODIFICATION';
    case SOUMISSION = 'SOUMISSION';
    case VALIDATION = 'VALIDATION';
    case REJET = 'REJET';
    case DEMANDE_INFORMATIONS = 'DEMANDE_INFORMATIONS';
    case REPONSE_INFORMATIONS = 'REPONSE_INFORMATIONS';
    case TRANSMISSION = 'TRANSMISSION';
    case PRISE_EN_CHARGE = 'PRISE_EN_CHARGE';
    case MISE_A_DISPOSITION = 'MISE_A_DISPOSITION';
    case CONFIRMATION_RECEPTION = 'CONFIRMATION_RECEPTION';
    case SIGNALEMENT_ANOMALIE = 'SIGNALEMENT_ANOMALIE';
    case REGULARISATION = 'REGULARISATION';
    case IMPOSSIBILITE = 'IMPOSSIBILITE';

    public function label(): string
    {
        return match ($this) {
            self::CREATION => 'Création',
            self::MODIFICATION => 'Modification du brouillon',
            self::SOUMISSION => 'Soumission',
            self::VALIDATION => 'Validation',
            self::REJET => 'Rejet',
            self::DEMANDE_INFORMATIONS => 'Demande d\'informations complémentaires',
            self::REPONSE_INFORMATIONS => 'Réponse du demandeur',
            self::TRANSMISSION => 'Transmission au destinataire',
            self::PRISE_EN_CHARGE => 'Prise en charge',
            self::MISE_A_DISPOSITION => 'Mise à disposition des ressources',
            self::CONFIRMATION_RECEPTION => 'Confirmation de réception',
            self::SIGNALEMENT_ANOMALIE => 'Signalement d\'une anomalie',
            self::REGULARISATION => 'Régularisation',
            self::IMPOSSIBILITE => 'Impossibilité de traitement',
        };
    }

    /** Icône (composant <x-icon>) pour la frise d'historique. */
    public function icone(): string
    {
        return match ($this) {
            self::CREATION, self::MODIFICATION => 'edit',
            self::SOUMISSION, self::TRANSMISSION => 'arrow-right',
            self::VALIDATION, self::CONFIRMATION_RECEPTION => 'check-circle',
            self::REJET, self::IMPOSSIBILITE => 'x-circle',
            self::DEMANDE_INFORMATIONS, self::REPONSE_INFORMATIONS => 'message',
            self::PRISE_EN_CHARGE => 'user',
            self::MISE_A_DISPOSITION => 'box',
            self::SIGNALEMENT_ANOMALIE => 'alert-triangle',
            self::REGULARISATION => 'refresh',
        };
    }
}
