<?php

namespace App\Services\Demandes;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\DemandeEvenement;
use App\Models\User;
use App\Notifications\EvenementDemande;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Destinataires des notifications pour chaque événement de l'historique (SFD §12.2).
 *
 * Appelé par Journal::ajouter() : toute action tracée prévient les bonnes personnes.
 * L'auteur de l'action n'est jamais notifié de sa propre action.
 */
final class Notificateur
{
    public static function pour(DemandeEvenement $evenement): void
    {
        if (in_array($evenement->action, [ActionDemande::CREATION, ActionDemande::MODIFICATION], true)) {
            return; // brouillon : personne à prévenir
        }

        $demande = $evenement->demande;
        // Rechargement frais : des relations chargées plus tôt (ex. serviceDemandeur à null
        // avant la soumission) fausseraient le calcul des destinataires.
        $demande->load(['demandeur', 'receveur', 'serviceDemandeur.chef', 'serviceDemandeur.departement.responsable']);
        $ref = $demande->reference;

        $envois = match ($evenement->action) {
            ActionDemande::SOUMISSION => self::soumission($demande, $evenement),

            ActionDemande::VALIDATION => $evenement->ancien_statut === $evenement->nouveau_statut
                ? [] // validation N+2 couverte automatiquement : pas de doublon
                : array_filter([
                    [[$demande->demandeur], "Demande {$ref} validée (".self::niveau($evenement).')', self::suite($evenement->nouveau_statut), 'check-circle', 'green'],
                    $evenement->nouveau_statut === DemandeStatut::EN_ATTENTE_N2
                        ? [[Valideurs::attendu($demande)], "Demande {$ref} à valider (N+2)", "{$demande->demandeur->nomComplet()} : « {$demande->objet} ». La validation N+1 est faite.", 'clock', 'orange']
                        : null,
                ]),

            ActionDemande::REJET => [[[$demande->demandeur], "Demande {$ref} rejetée", 'Motif : '.$evenement->commentaire, 'x-circle', 'red']],

            ActionDemande::TRANSMISSION => [[
                self::receveurs($demande),
                "Nouvelle demande {$ref} à prendre en charge",
                "{$demande->demandeur->nomComplet()} : « {$demande->objet} » — {$demande->libelleDestination()}.",
                'inbox', 'blue',
            ]],

            ActionDemande::DEMANDE_INFORMATIONS => [[[$demande->demandeur], "Informations demandées sur {$ref}", $evenement->commentaire, 'message', 'orange']],

            ActionDemande::REPONSE_INFORMATIONS => [[
                [self::auteurDerniereQuestion($demande)],
                "Réponse du demandeur sur {$ref}",
                $evenement->commentaire,
                'message', 'blue',
            ]],

            ActionDemande::PRISE_EN_CHARGE => [[[$demande->demandeur], "Demande {$ref} prise en charge", "{$evenement->utilisateur?->nomComplet()} traite votre demande.", 'user', 'blue']],

            ActionDemande::MISE_A_DISPOSITION => [[[$demande->demandeur], "Ressources mises à disposition ({$ref})", 'Vérifiez les ressources, puis confirmez la réception ou signalez une anomalie.', 'box', 'violet']],

            ActionDemande::CONFIRMATION_RECEPTION => [[[$demande->receveur], "Demande {$ref} clôturée", "{$demande->demandeur->nomComplet()} a confirmé la réception des ressources.", 'check-circle', 'green']],

            ActionDemande::SIGNALEMENT_ANOMALIE => [[[$demande->receveur], "Anomalie de réception sur {$ref}", $evenement->commentaire, 'alert-triangle', 'orange']],

            ActionDemande::REGULARISATION => [[[$demande->demandeur], "Anomalie régularisée ({$ref})", 'Vérifiez de nouveau les ressources, puis confirmez la réception.', 'refresh', 'violet']],

            ActionDemande::IMPOSSIBILITE => [[[$demande->demandeur], "Traitement impossible ({$ref})", $evenement->commentaire, 'x-circle', 'red']],

            ActionDemande::CREATION, ActionDemande::MODIFICATION => [],
        };

        foreach ($envois as [$destinataires, $titre, $message, $icone, $couleur]) {
            $cibles = collect($destinataires)
                ->filter(fn ($u) => $u instanceof User && $u->is_active && $u->id !== $evenement->utilisateur_id)
                ->unique('id');

            if ($cibles->isEmpty()) {
                continue;
            }

            try {
                Notification::send($cibles, new EvenementDemande(
                    $demande, $titre, (string) ($message ?? ''), $icone, $couleur, $evenement->action->value
                ));
            } catch (\Throwable $e) {
                // Une panne d'e-mail ne doit jamais bloquer le circuit de la demande.
                Log::error('Notification de demande non envoyée', ['demande' => $demande->id, 'erreur' => $e->getMessage()]);
            }
        }
    }

    /** @return list<array> */
    private static function soumission(Demande $demande, DemandeEvenement $evenement): array
    {
        $ref = $demande->reference;
        $statut = $evenement->nouveau_statut;

        $envois = [[[$demande->demandeur], "Demande {$ref} soumise", self::suite($statut), 'arrow-right', 'blue']];

        if ($statut === DemandeStatut::EN_ATTENTE_N1 || $statut === DemandeStatut::EN_ATTENTE_N2) {
            $envois[] = [
                [Valideurs::attendu($demande)],
                "Demande {$ref} à valider (".($statut === DemandeStatut::EN_ATTENTE_N1 ? 'N+1' : 'N+2').')',
                "{$demande->demandeur->nomComplet()} : « {$demande->objet} ».",
                'clock', 'orange',
            ];
        }

        return $envois;
    }

    private static function suite(?DemandeStatut $statut): string
    {
        return match ($statut) {
            DemandeStatut::EN_ATTENTE_N1 => 'Elle attend la validation de votre chef de service (N+1).',
            DemandeStatut::EN_ATTENTE_N2 => 'Elle attend la validation du responsable de votre département (N+2).',
            DemandeStatut::EN_ATTENTE_TRAITEMENT => 'Elle a été transmise à son destinataire pour traitement.',
            default => 'Statut : '.($statut?->label() ?? '—').'.',
        };
    }

    private static function niveau(DemandeEvenement $evenement): string
    {
        return $evenement->niveau === 'N2' ? 'N+2' : 'N+1';
    }

    private static function receveurs(Demande $demande): Collection
    {
        if ($demande->destination_type === null) {
            return collect();
        }

        $id = match ($demande->destination_type) {
            \App\Enums\TypeDestination::DEPARTEMENT => $demande->destination_departement_id,
            \App\Enums\TypeDestination::SERVICE => $demande->destination_service_id,
            \App\Enums\TypeDestination::UTILISATEUR => $demande->destination_utilisateur_id,
        };

        return PerimetreReceveurs::pour($demande->destination_type, $id)
            ->where('id', '!=', $demande->demandeur_id)
            ->get();
    }

    private static function auteurDerniereQuestion(Demande $demande): ?User
    {
        return $demande->evenements()
            ->where('action', ActionDemande::DEMANDE_INFORMATIONS)
            ->latest('id')
            ->first()?->utilisateur;
    }
}
