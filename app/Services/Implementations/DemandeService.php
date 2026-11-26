<?php

namespace App\Services\Implementations;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Enums\NatureAnomalie;
use App\Enums\TypeDestination;
use App\Exceptions\RegleMetierException;
use App\Models\Demande;
use App\Models\Departement;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\Demandes\CircuitValidation;
use App\Services\Demandes\Complements;
use App\Services\Demandes\Journal;
use App\Services\Demandes\PerimetreReceveurs;
use App\Services\Interfaces\IDemandeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DemandeService implements IDemandeService
{
    // ==================================================================
    // Consultation
    // ==================================================================

    public function listerPourDemandeur(User $demandeur, array $filtres = [], int $parPage = 15): LengthAwarePaginator
    {
        return Demande::query()
            ->where('demandeur_id', $demandeur->id)
            ->with(['application', 'destinationDepartement', 'destinationService', 'destinationUtilisateur'])
            ->when($filtres['q'] ?? null, function (Builder $q, string $terme) {
                $motif = '%'.$terme.'%';
                $q->where(fn (Builder $q) => $q->where('reference', 'LIKE', $motif)->orWhere('objet', 'LIKE', $motif));
            })
            ->when($filtres['statut'] ?? null, fn (Builder $q, string $statut) => $q->where('statut', $statut))
            ->latest('updated_at')
            ->latest('id')
            ->paginate($parPage)
            ->withQueryString();
    }

    public function rechercherPourAdministration(array $filtres = []): Builder
    {
        $finaux = collect(DemandeStatut::cases())->filter(fn (DemandeStatut $s) => $s->estFinal())->values()->all();
        $etat = $filtres['etat'] ?? null;

        return Demande::query()
            ->where('statut', '!=', DemandeStatut::BROUILLON)
            ->with(['demandeur', 'serviceDemandeur.departement', 'application', 'receveur',
                'destinationDepartement', 'destinationService', 'destinationUtilisateur'])
            ->when($filtres['q'] ?? null, function (Builder $q, string $terme) {
                $motif = '%'.$terme.'%';
                $q->where(fn (Builder $q) => $q
                    ->where('reference', 'LIKE', $motif)
                    ->orWhere('objet', 'LIKE', $motif)
                    ->orWhereHas('demandeur', fn (Builder $q) => $q
                        ->where('nom', 'LIKE', $motif)->orWhere('prenom', 'LIKE', $motif)->orWhere('matricule', 'LIKE', $motif)));
            })
            ->when($etat === 'EN_COURS', fn (Builder $q) => $q->whereNotIn('statut', $finaux))
            ->when($etat === 'TERMINEES', fn (Builder $q) => $q->whereIn('statut', $finaux))
            ->when($etat && DemandeStatut::tryFrom($etat), fn (Builder $q) => $q->where('statut', $etat))
            ->when($filtres['departement_id'] ?? null, fn (Builder $q, $id) => $q
                ->whereHas('serviceDemandeur', fn (Builder $q) => $q->where('departement_id', $id)))
            ->when($filtres['destination_type'] ?? null, fn (Builder $q, $type) => $q->where('destination_type', $type))
            ->when($filtres['du'] ?? null, fn (Builder $q, $du) => $q->whereDate('soumise_le', '>=', $du))
            ->when($filtres['au'] ?? null, fn (Builder $q, $au) => $q->whereDate('soumise_le', '<=', $au))
            ->latest('soumise_le')
            ->latest('id');
    }

    public function compteursDemandeur(User $demandeur): array
    {
        $parStatut = Demande::where('demandeur_id', $demandeur->id)
            ->selectRaw('statut, COUNT(*) as nombre')
            ->groupBy('statut')
            ->pluck('nombre', 'statut');

        $somme = fn (DemandeStatut ...$statuts) => collect($statuts)->sum(fn (DemandeStatut $s) => (int) ($parStatut[$s->value] ?? 0));

        return [
            'total' => (int) $parStatut->sum(),
            'brouillons' => $somme(DemandeStatut::BROUILLON),
            'validation' => $somme(DemandeStatut::EN_ATTENTE_N1, DemandeStatut::EN_ATTENTE_N2),
            'action' => $somme(DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE, DemandeStatut::EN_ATTENTE_CONFIRMATION),
            'cloturees' => $somme(DemandeStatut::CLOTUREE),
        ];
    }

    // ==================================================================
    // Brouillons (SFD §6.2, §6.4)
    // ==================================================================

    public function creer(User $demandeur, array $data): Demande
    {
        $this->verifierDestination($data, $demandeur);

        return DB::transaction(function () use ($demandeur, $data) {
            $demande = new Demande($this->champs($data));
            $demande->reference = $this->nouvelleReference();
            $demande->demandeur_id = $demandeur->id;
            $demande->statut = DemandeStatut::BROUILLON;
            $demande->save();

            $this->remplacerLignes($demande, $data['lignes'] ?? []);
            Journal::ajouter($demande, $demandeur, ActionDemande::CREATION, null, DemandeStatut::BROUILLON);

            return $demande->refresh();
        });
    }

    public function modifier(Demande $demande, array $data, User $auteur): Demande
    {
        $this->exigerBrouillon($demande);
        $this->verifierDestination($data, $demande->demandeur);

        return DB::transaction(function () use ($demande, $data, $auteur) {
            $demande->fill($this->champs($data))->save();
            $this->remplacerLignes($demande, $data['lignes'] ?? []);
            Journal::ajouter($demande, $auteur, ActionDemande::MODIFICATION, DemandeStatut::BROUILLON, DemandeStatut::BROUILLON);

            return $demande->refresh();
        });
    }

    public function supprimer(Demande $demande): void
    {
        $this->exigerBrouillon($demande);

        // Lignes et historique supprimés en cascade : un brouillon n'a jamais circulé.
        $demande->delete();
    }

    // ==================================================================
    // Soumission (SFD §7, §11.2)
    // ==================================================================

    public function soumettre(Demande $demande, User $auteur): Demande
    {
        $this->exigerBrouillon($demande);
        $demande->loadMissing(['demandeur.service', 'lignes.ressource', 'application']);

        $this->verifierCompletude($demande);
        $circuit = CircuitValidation::exiger($demande->demandeur);
        $this->verifierReceveursDisponibles($demande);

        return DB::transaction(function () use ($demande, $auteur, $circuit) {
            $nouveau = $circuit->statutInitial();

            $demande->statut = $nouveau;
            $demande->service_demandeur_id = $circuit->service->id;
            $demande->soumise_le = now();
            $demande->save();

            Journal::ajouter($demande, $auteur, ActionDemande::SOUMISSION, DemandeStatut::BROUILLON, $nouveau, $circuit->explication());

            // RG-20 : sans validation hiérarchique, la demande est transmise immédiatement.
            if ($nouveau === DemandeStatut::EN_ATTENTE_TRAITEMENT) {
                Journal::ajouter($demande, $auteur, ActionDemande::TRANSMISSION, $nouveau, $nouveau, $demande->libelleDestination());
            }

            return $demande->refresh();
        });
    }

    public function repondreAuComplement(Demande $demande, User $demandeur, string $reponse): Demande
    {
        return DB::transaction(function () use ($demande, $demandeur, $reponse) {
            Complements::repondre($demande, $demandeur, $reponse);

            return $demande->refresh();
        });
    }

    // ==================================================================
    // Réception (SFD §9)
    // ==================================================================

    public function confirmerReception(Demande $demande, User $demandeur, ?string $commentaire = null): Demande
    {
        $this->exigerConfirmationAttendue($demande, $demandeur);

        return DB::transaction(function () use ($demande, $demandeur, $commentaire) {
            $demande->statut = DemandeStatut::CLOTUREE;
            $demande->cloturee_le = now();
            $demande->save();

            Journal::ajouter($demande, $demandeur, ActionDemande::CONFIRMATION_RECEPTION,
                DemandeStatut::EN_ATTENTE_CONFIRMATION, DemandeStatut::CLOTUREE, filled($commentaire) ? trim($commentaire) : null);

            return $demande->refresh();
        });
    }

    public function signalerAnomalie(Demande $demande, User $demandeur, string $nature, array $lignes, string $description): Demande
    {
        $this->exigerConfirmationAttendue($demande, $demandeur);

        $natureAnomalie = NatureAnomalie::tryFrom($nature)
            ?? throw new RegleMetierException('Précisez la nature de l\'anomalie.');
        if (blank($description)) {
            throw new RegleMetierException('Décrivez l\'anomalie constatée.');
        }

        $demande->loadMissing('lignes.ressource');
        $concernees = [];
        foreach ($lignes as $ligneId => $quantite) {
            $ligne = $demande->lignes->firstWhere('id', (int) $ligneId);
            if ($ligne === null) {
                throw new RegleMetierException('Ressource concernée inconnue pour cette demande.');
            }
            $concernees[] = ['ligne_id' => $ligne->id, 'quantite' => max(0, (int) $quantite)];
        }
        if ($concernees === []) {
            throw new RegleMetierException('Indiquez au moins une ressource concernée par l\'anomalie.');
        }

        return DB::transaction(function () use ($demande, $demandeur, $natureAnomalie, $concernees, $description) {
            $demande->anomalies()->create([
                'signalee_par_id' => $demandeur->id,
                'nature' => $natureAnomalie,
                'description' => trim($description),
                'lignes' => $concernees,
            ]);

            $demande->statut = DemandeStatut::EN_COURS_REGULARISATION;
            $demande->save();

            $ressources = collect($concernees)
                ->map(fn ($c) => $demande->lignes->firstWhere('id', $c['ligne_id'])->ressource->designation.($c['quantite'] ? " ({$c['quantite']})" : ''))
                ->implode(', ');

            Journal::ajouter($demande, $demandeur, ActionDemande::SIGNALEMENT_ANOMALIE,
                DemandeStatut::EN_ATTENTE_CONFIRMATION, DemandeStatut::EN_COURS_REGULARISATION,
                "{$natureAnomalie->label()} — {$ressources}\n".trim($description));

            return $demande->refresh();
        });
    }

    private function exigerConfirmationAttendue(Demande $demande, User $demandeur): void
    {
        if ($demande->demandeur_id !== $demandeur->id) {
            throw new RegleMetierException('Seul le demandeur peut confirmer la réception ou signaler une anomalie.');
        }
        if ($demande->statut !== DemandeStatut::EN_ATTENTE_CONFIRMATION) {
            throw new RegleMetierException('Cette demande n\'est pas en attente de confirmation de réception.');
        }
    }

    // ==================================================================
    // Règles
    // ==================================================================

    private function exigerBrouillon(Demande $demande): void
    {
        if (! $demande->estBrouillon()) {
            throw new RegleMetierException(
                'Cette demande a déjà été soumise : elle ne peut plus être modifiée, supprimée ou soumise à nouveau (SFD §6.4).'
            );
        }
    }

    /**
     * RG-13 / RG-14 : une seule destination, existante et active.
     * Un utilisateur destinataire doit être un Receveur actif affecté à un service, autre que le demandeur.
     */
    private function verifierDestination(array $data, User $demandeur): void
    {
        $type = isset($data['destination_type']) ? TypeDestination::tryFrom($data['destination_type']) : null;
        if ($type === null) {
            return; // destination facultative tant que la demande est en brouillon
        }

        $id = $this->idDestination($data, $type);
        if ($id === null) {
            throw new RegleMetierException('Choisissez le '.mb_strtolower($type->label()).' destinataire.');
        }

        match ($type) {
            TypeDestination::DEPARTEMENT => Departement::whereKey($id)->where('is_active', true)->exists()
                ?: throw new RegleMetierException('Le département choisi n\'existe pas ou est désactivé.'),
            TypeDestination::SERVICE => Service::whereKey($id)->where('is_active', true)->exists()
                ?: throw new RegleMetierException('Le service choisi n\'existe pas ou est désactivé.'),
            TypeDestination::UTILISATEUR => $this->verifierUtilisateurDestinataire($id, $demandeur),
        };
    }

    private function verifierUtilisateurDestinataire(int $id, User $demandeur): bool
    {
        if ($id === $demandeur->id) {
            throw new RegleMetierException('Vous ne pouvez pas vous adresser votre propre demande.');
        }

        $estReceveur = PerimetreReceveurs::receveurs()->whereKey($id)->whereNotNull('service_id')->exists();
        if (! $estReceveur) {
            throw new RegleMetierException(
                'Le destinataire doit être un utilisateur actif, affecté à un service et ayant le rôle '.Role::RECEVEUR.'.'
            );
        }

        return true;
    }

    /** Avant soumission : contenu complet et référentiels toujours actifs. */
    private function verifierCompletude(Demande $demande): void
    {
        $manques = [];

        if (blank($demande->description)) {
            $manques[] = 'la description ou le motif';
        }
        if ($demande->destination_type === null) {
            $manques[] = 'la destination';
        }
        if ($demande->lignes->isEmpty()) {
            $manques[] = 'au moins une ressource';
        }

        if ($manques !== []) {
            throw new RegleMetierException('Pour soumettre la demande, renseignez : '.implode(', ', $manques).'.');
        }

        if ($demande->application && ! $demande->application->is_active) {
            throw new RegleMetierException("L'application « {$demande->application->nom} » a été désactivée : choisissez-en une autre.");
        }

        $inactives = $demande->lignes->filter(fn ($l) => ! $l->ressource->is_active)->map(fn ($l) => $l->ressource->designation);
        if ($inactives->isNotEmpty()) {
            throw new RegleMetierException('Ressources désactivées depuis la création du brouillon : '.$inactives->implode(', ').'. Retirez-les de la demande.');
        }

        // La destination a pu être désactivée depuis l'enregistrement du brouillon.
        $this->verifierDestination(array_merge(
            $demande->only(['destination_departement_id', 'destination_service_id', 'destination_utilisateur_id']),
            ['destination_type' => $demande->destination_type->value],
        ), $demande->demandeur);
    }

    /** Une demande transmise doit pouvoir être prise en charge par au moins un receveur. */
    private function verifierReceveursDisponibles(Demande $demande): void
    {
        $type = $demande->destination_type;
        $id = $this->idDestination($demande->toArray(), $type);

        if (! PerimetreReceveurs::pour($type, $id)->exists()) {
            throw new RegleMetierException(
                'Aucun receveur n\'est rattaché à cette destination : la demande ne pourrait pas être traitée. Choisissez une autre destination ou contactez l\'administrateur.'
            );
        }
    }

    // ==================================================================
    // Outils
    // ==================================================================

    /** Champs de la demande, avec une seule destination renseignée (RG-14). */
    private function champs(array $data): array
    {
        $type = isset($data['destination_type']) ? TypeDestination::tryFrom($data['destination_type']) : null;

        return [
            'objet' => $data['objet'],
            'description' => $data['description'] ?? null,
            'informations_complementaires' => $data['informations_complementaires'] ?? null,
            'application_id' => $data['application_id'] ?? null,
            'destination_type' => $type,
            'destination_departement_id' => $type === TypeDestination::DEPARTEMENT ? $this->idDestination($data, $type) : null,
            'destination_service_id' => $type === TypeDestination::SERVICE ? $this->idDestination($data, $type) : null,
            'destination_utilisateur_id' => $type === TypeDestination::UTILISATEUR ? $this->idDestination($data, $type) : null,
        ];
    }

    private function idDestination(array $data, TypeDestination $type): ?int
    {
        $cle = 'destination_'.strtolower($type->value).'_id';

        return isset($data[$cle]) && $data[$cle] !== '' ? (int) $data[$cle] : null;
    }

    private function remplacerLignes(Demande $demande, array $lignes): void
    {
        $demande->lignes()->delete();

        foreach ($lignes as $ligne) {
            if (empty($ligne['ressource_id'])) {
                continue;
            }
            $demande->lignes()->create([
                'ressource_id' => (int) $ligne['ressource_id'],
                'quantite_demandee' => max(1, (int) ($ligne['quantite'] ?? 1)),
            ]);
        }
    }

    /** Référence unique DEM-AAAA-NNNNN (SFD §6.2). */
    private function nouvelleReference(): string
    {
        $prefixe = 'DEM-'.now()->year.'-';
        $derniere = Demande::where('reference', 'like', $prefixe.'%')->lockForUpdate()->max('reference');
        $numero = $derniere ? ((int) substr($derniere, strlen($prefixe))) + 1 : 1;

        return $prefixe.str_pad((string) $numero, 5, '0', STR_PAD_LEFT);
    }
}
