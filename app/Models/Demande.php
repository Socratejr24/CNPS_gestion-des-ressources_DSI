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
            'destination_type' => TypeDestination::class,
            'soumise_le' => 'datetime',
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
