<?php

namespace App\Policies;

use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\User;
use App\Services\Demandes\PerimetreReceveurs;
use App\Services\Demandes\Valideurs;

/**
 * Qui peut consulter ou agir sur une demande (RG-34, SFD §16.1 : impossible de
 * contourner les validations par un accès direct).
 *
 * Consultation : demandeur, administrateur, valideur attendu pour l'étape en cours,
 * et toute personne ayant déjà agi sur la demande (valideur ou auteur d'une question).
 * L'étape 4 ajoutera les receveurs du périmètre de destination.
 */
class DemandePolicy
{
    public function view(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id
            || $utilisateur->estAdministrateur()
            || Valideurs::estValideurAttendu($utilisateur, $demande)
            || $this->receveurConcerne($utilisateur, $demande)
            || $demande->evenements()->where('utilisateur_id', $utilisateur->id)->exists();
    }

    /** Receveur attitré, ou receveur du périmètre une fois la demande transmise. */
    private function receveurConcerne(User $utilisateur, Demande $demande): bool
    {
        if ($demande->receveur_id === $utilisateur->id) {
            return true;
        }

        $transmise = $demande->statut === DemandeStatut::EN_ATTENTE_TRAITEMENT || $demande->receveur_id !== null;

        return $transmise && PerimetreReceveurs::couvre($utilisateur, $demande);
    }

    /** Prise en charge par un receveur du périmètre (RG-23). */
    public function prendreEnCharge(User $utilisateur, Demande $demande): bool
    {
        return $demande->statut === DemandeStatut::EN_ATTENTE_TRAITEMENT
            && $demande->demandeur_id !== $utilisateur->id
            && PerimetreReceveurs::couvre($utilisateur, $demande);
    }

    /** Mise à disposition, demande d'informations, impossibilité : le receveur attitré (SFD §8.3). */
    public function traiter(User $utilisateur, Demande $demande): bool
    {
        return $demande->statut === DemandeStatut::EN_COURS_TRAITEMENT
            && $demande->receveur_id === $utilisateur->id
            && PerimetreReceveurs::estReceveur($utilisateur);
    }

    /** Régularisation d'une anomalie : le receveur attitré (SFD §9.3). */
    public function regulariser(User $utilisateur, Demande $demande): bool
    {
        return $demande->statut === DemandeStatut::EN_COURS_REGULARISATION
            && $demande->receveur_id === $utilisateur->id
            && PerimetreReceveurs::estReceveur($utilisateur);
    }

    /** Confirmer la réception ou signaler une anomalie : le demandeur (SFD §9.1). */
    public function receptionner(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id
            && $demande->statut === DemandeStatut::EN_ATTENTE_CONFIRMATION;
    }

    /** Modifier, supprimer ou soumettre : l'auteur, tant que la demande est en brouillon (SFD §6.4). */
    public function update(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id && $demande->estBrouillon();
    }

    public function delete(User $utilisateur, Demande $demande): bool
    {
        return $this->update($utilisateur, $demande);
    }

    public function soumettre(User $utilisateur, Demande $demande): bool
    {
        return $this->update($utilisateur, $demande);
    }

    /** Valider, rejeter ou demander des informations à l'étape N+1 / N+2 en cours. */
    public function decider(User $utilisateur, Demande $demande): bool
    {
        return Valideurs::estValideurAttendu($utilisateur, $demande);
    }

    /** Répondre à une demande d'informations complémentaires (SFD §10.3). */
    public function repondre(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id
            && $demande->statut === DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE;
    }
}
