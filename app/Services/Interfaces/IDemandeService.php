<?php

namespace App\Services\Interfaces;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Cycle de vie des demandes côté demandeur (SFD §6 et §7.1).
 *
 * Données attendues par creer() / modifier() :
 *   objet, description, informations_complementaires, application_id,
 *   destination_type, destination_{departement|service|utilisateur}_id,
 *   lignes : [ ['ressource_id' => int, 'quantite' => int], ... ]
 */
interface IDemandeService
{
    /** Filtres : q (référence ou objet), statut. */
    public function listerPourDemandeur(User $demandeur, array $filtres = [], int $parPage = 15): LengthAwarePaginator;

    /** @return array{total:int, brouillons:int, validation:int, action:int, cloturees:int} */
    public function compteursDemandeur(User $demandeur): array;

    /** Crée la demande au statut BROUILLON (SFD §6.2). */
    public function creer(User $demandeur, array $data): Demande;

    /** Modifie un brouillon (SFD §6.4). */
    public function modifier(Demande $demande, array $data, User $auteur): Demande;

    /** Supprime un brouillon (SFD §6.4). */
    public function supprimer(Demande $demande): void;

    /** Soumet un brouillon et l'engage dans le circuit de validation (SFD §7, §11.2). */
    public function soumettre(Demande $demande, User $auteur): Demande;

    /** Réponse du demandeur à une demande d'informations : retour à l'étape concernée (RG-25, RG-26). */
    public function repondreAuComplement(Demande $demande, User $demandeur, string $reponse): Demande;
}
