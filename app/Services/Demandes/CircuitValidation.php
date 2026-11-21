<?php

namespace App\Services\Demandes;

use App\Enums\DemandeStatut;
use App\Exceptions\RegleMetierException;
use App\Models\Service;
use App\Models\User;

/**
 * Circuit de validation hiérarchique applicable à un demandeur (SFD §7, RG-15 à RG-20).
 *
 *  - agent classique       : N+1 (chef de service) → N+2 (responsable du département) → destinataire
 *  - chef de service       : N+2 → destinataire                       (RG-19)
 *  - responsable du dépt.  : destinataire directement                 (RG-20)
 *
 * Arbitrages (SFD §18) : un poste N+1 / N+2 vacant BLOQUE la soumission ;
 * si la même personne est N+1 et N+2, une seule validation suffit.
 *
 * Le circuit dépend du demandeur, jamais du destinataire (SFD §7.1).
 */
final class CircuitValidation
{
    private function __construct(
        public readonly ?Service $service,
        public readonly ?User $n1,
        public readonly ?User $n2,
        public readonly bool $n1Requis,
        public readonly bool $n2Requis,
        public readonly ?string $blocage,
    ) {}

    /**
     * Évalue le circuit sans lever d'exception (aperçu dans les écrans).
     * Le service peut être imposé (service figé à la soumission d'une demande).
     */
    public static function evaluer(User $demandeur, ?Service $service = null): self
    {
        $service ??= $demandeur->service;

        if ($service === null) {
            return new self(null, null, null, false, false,
                'Vous n\'êtes rattaché à aucun service : votre demande ne peut pas être soumise. Contactez l\'administrateur.');
        }

        $service->loadMissing(['chef', 'departement.responsable']);
        $chef = $service->chef;
        $responsable = $service->departement?->responsable;

        // RG-20 : le demandeur est responsable de son département.
        if ($responsable?->is($demandeur)) {
            return new self($service, null, $responsable, false, false, null);
        }

        // RG-19 : le demandeur est chef de son service → directement au N+2.
        $n1Requis = ! $chef?->is($demandeur);

        // Cumul N+1 / N+2 par la même personne : une seule validation (au niveau N+1).
        $n2Requis = ! ($n1Requis && $chef !== null && $responsable !== null && $chef->is($responsable));

        $blocage = match (true) {
            $n1Requis && ! self::disponible($chef) => "Le service « {$service->libelle} » n'a pas de chef de service (N+1) en fonction : la demande ne peut pas être soumise. Contactez l'administrateur.",
            $n2Requis && ! self::disponible($responsable) => 'Le département « '.($service->departement?->libelle ?? '—')." » n'a pas de responsable (N+2) en fonction : la demande ne peut pas être soumise. Contactez l'administrateur.",
            default => null,
        };

        return new self($service, $chef, $responsable, $n1Requis, $n2Requis, $blocage);
    }

    /**
     * @throws RegleMetierException si le circuit ne peut pas être appliqué (poste vacant, pas de service)
     */
    public static function exiger(User $demandeur, ?Service $service = null): self
    {
        $circuit = self::evaluer($demandeur, $service);

        if ($circuit->blocage !== null) {
            throw new RegleMetierException($circuit->blocage);
        }

        return $circuit;
    }

    /** Statut de la demande juste après sa soumission (SFD §11.2, ligne « Soumettre »). */
    public function statutInitial(): DemandeStatut
    {
        return match (true) {
            $this->n1Requis => DemandeStatut::EN_ATTENTE_N1,
            $this->n2Requis => DemandeStatut::EN_ATTENTE_N2,
            default => DemandeStatut::EN_ATTENTE_TRAITEMENT,
        };
    }

    /** Explication du circuit retenu, pour l'historique et l'aperçu. */
    public function explication(): string
    {
        return match (true) {
            ! $this->n1Requis && ! $this->n2Requis => 'Demandeur responsable de son département : aucune validation hiérarchique requise (RG-20).',
            ! $this->n1Requis => 'Demandeur chef de son service : validation N+1 non requise (RG-19).',
            ! $this->n2Requis => 'Le chef de service est aussi responsable du département : une seule validation.',
            default => 'Circuit complet : validation N+1 puis N+2.',
        };
    }

    private static function disponible(?User $utilisateur): bool
    {
        return $utilisateur !== null && $utilisateur->is_active;
    }
}
