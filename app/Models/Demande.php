<?php

namespace App\Models;

use App\Enums\DemandeStatut;
use App\Enums\TypeDestination;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Demande de ressources (SFD §6).
 *
 * Le statut, la référence, le demandeur et le contexte de soumission ne sont pas
 * assignables en masse : ils ne changent que via DemandeService (cycle de vie).
 */
class Demande extends Model
{
    use HasAuditFields, HasFactory;

    protected $table = 'demandes';

    protected $fillable = [
        'application_id',
        'objet',
        'description',
        'informations_complementaires',
        'destination_type',
        'destination_departement_id',
        'destination_service_id',
        'destination_utilisateur_id',
    ];

    protected $attributes = ['statut' => 'BROUILLON'];

    protected function casts(): array
    {
        return [
            'statut' => DemandeStatut::class,
            'statut_retour_complement' => DemandeStatut::class,
            'destination_type' => TypeDestination::class,
            'soumise_le' => 'datetime',
            'prise_en_charge_le' => 'datetime',
            'mise_a_disposition_le' => 'date',
            'cloturee_le' => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(DemandeLigne::class);
    }

    public function evenements(): HasMany
    {
        return $this->hasMany(DemandeEvenement::class)->orderBy('created_at')->orderBy('id');
    }

    public function destinationDepartement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'destination_departement_id');
    }

    public function destinationService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'destination_service_id');
    }

    public function destinationUtilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destination_utilisateur_id');
    }

    /** Receveur ayant pris en charge la demande (RG-23). */
    public function receveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receveur_id');
    }

    /** Anomalies de réception (SFD §9.2), de la plus ancienne à la plus récente. */
    public function anomalies(): HasMany
    {
        return $this->hasMany(DemandeAnomalie::class)->orderBy('created_at')->orderBy('id');
    }

    /** Anomalie en attente de régularisation, s'il y en a une. */
    public function anomalieEnCours(): ?DemandeAnomalie
    {
        return $this->anomalies->first(fn (DemandeAnomalie $a) => ! $a->estRegularisee());
    }

    /** Auteur de la demande d'informations complémentaires en cours (SFD §10.2). */
    public function complementDemandePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complement_demande_par_id');
    }

    /** Dernière demande d'informations complémentaires (question posée au demandeur). */
    public function derniereQuestion(): ?DemandeEvenement
    {
        return $this->evenements->where('action', \App\Enums\ActionDemande::DEMANDE_INFORMATIONS)->last();
    }

    /** Service du demandeur figé à la soumission (base du circuit N+1 / N+2). */
    public function serviceDemandeur(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_demandeur_id');
    }

    // ------------------------------------------------------------------
    // Lecture
    // ------------------------------------------------------------------

    public function estBrouillon(): bool
    {
        return $this->statut === DemandeStatut::BROUILLON;
    }

    /** Libellé de la destination, ex. « Service : Réseaux ». */
    public function libelleDestination(): string
    {
        return match ($this->destination_type) {
            TypeDestination::DEPARTEMENT => 'Département : '.($this->destinationDepartement?->libelle ?? '—'),
            TypeDestination::SERVICE => 'Service : '.($this->destinationService?->libelle ?? '—'),
            TypeDestination::UTILISATEUR => 'Utilisateur : '.($this->destinationUtilisateur?->nomComplet() ?? '—'),
            default => 'Non renseignée',
        };
    }
}
