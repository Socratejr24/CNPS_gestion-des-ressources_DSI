<?php

namespace App\Services\Demandes;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Models\Demande;

/**
 * Étapes du cycle de vie d'une demande, pour l'affichage du suivi (page de détail).
 *
 * État de chaque étape : fait | en_cours | a_venir | ignore | refuse.
 */
final class SuiviCircuit
{
    /**
     * @return list<array{code:string, libelle:string, acteur:string, etat:string, note:?string}>
     */
    public static function etapes(Demande $demande): array
    {
        $demande->loadMissing(['evenements', 'demandeur', 'serviceDemandeur']);
        $statut = $demande->statut;
        $evenements = $demande->evenements;

        $circuit = CircuitValidation::evaluer($demande->demandeur, $demande->serviceDemandeur);

        // Après soumission, le circuit réellement appliqué se lit dans l'historique.
        $soumission = $evenements->firstWhere('action', ActionDemande::SOUMISSION);
        if ($soumission) {
            $n1Requis = $soumission->nouveau_statut === DemandeStatut::EN_ATTENTE_N1;
            $n2Requis = $soumission->nouveau_statut === DemandeStatut::EN_ATTENTE_N2
                || ($n1Requis && $circuit->n2Requis);
        } else {
            $n1Requis = $circuit->n1Requis;
            $n2Requis = $circuit->n2Requis;
        }

        $a = fn (ActionDemande $action, ?string $niveau = null) => $evenements->contains(
            fn ($e) => $e->action === $action && ($niveau === null || $e->niveau === $niveau)
        );

        $etatValidation = function (string $niveau, bool $requis, DemandeStatut $attente) use ($statut, $a) {
            return match (true) {
                ! $requis => 'ignore',
                $a(ActionDemande::VALIDATION, $niveau) => 'fait',
                $a(ActionDemande::REJET, $niveau) => 'refuse',
                $statut === $attente => 'en_cours',
                default => 'a_venir',
            };
        };

        return [
            [
                'code' => 'SOUMISSION',
                'libelle' => 'Soumission',
                'acteur' => $demande->demandeur?->nomComplet() ?? '—',
                'etat' => $statut === DemandeStatut::BROUILLON ? 'en_cours' : 'fait',
                'note' => $demande->soumise_le ? 'Le '.$demande->soumise_le->format('d/m/Y à H:i') : 'Brouillon non soumis',
            ],
            [
                'code' => 'N1',
                'libelle' => 'Validation N+1',
                'acteur' => $circuit->n1?->nomComplet() ?? 'Chef de service non désigné',
                'etat' => $etatValidation('N1', $n1Requis, DemandeStatut::EN_ATTENTE_N1),
                'note' => $n1Requis ? 'Chef de service' : 'Non requise : le demandeur est chef de service ou responsable du département',
            ],
            [
                'code' => 'N2',
                'libelle' => 'Validation N+2',
                'acteur' => $circuit->n2?->nomComplet() ?? 'Responsable non désigné',
                'etat' => $etatValidation('N2', $n2Requis, DemandeStatut::EN_ATTENTE_N2),
                'note' => $n2Requis ? 'Responsable du département' : ($n1Requis ? 'Non requise : même personne que le N+1' : 'Non requise : le demandeur est responsable du département'),
            ],
            [
                'code' => 'TRAITEMENT',
                'libelle' => 'Traitement',
                'acteur' => $demande->libelleDestination(),
                'etat' => match (true) {
                    $statut === DemandeStatut::TRAITEMENT_IMPOSSIBLE => 'refuse',
                    $a(ActionDemande::MISE_A_DISPOSITION) => 'fait',
                    in_array($statut, [DemandeStatut::EN_ATTENTE_TRAITEMENT, DemandeStatut::EN_COURS_TRAITEMENT, DemandeStatut::EN_COURS_REGULARISATION], true) => 'en_cours',
                    default => 'a_venir',
                },
                'note' => 'Prise en charge et mise à disposition',
            ],
            [
                'code' => 'RECEPTION',
                'libelle' => 'Confirmation de réception',
                'acteur' => $demande->demandeur?->nomComplet() ?? '—',
                'etat' => match ($statut) {
                    DemandeStatut::CLOTUREE => 'fait',
                    DemandeStatut::EN_ATTENTE_CONFIRMATION => 'en_cours',
                    default => 'a_venir',
                },
                'note' => 'Clôture de la demande',
            ],
        ];
    }
}
