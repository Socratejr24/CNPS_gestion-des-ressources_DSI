<?php

namespace App\Models;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Événement de l'historique d'une demande (SFD §13.2, RG-33).
 * Écrit uniquement par DemandeService ; jamais modifié.
 */
class DemandeEvenement extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'demande_evenements';

    protected $fillable = ['utilisateur_id', 'action', 'ancien_statut', 'nouveau_statut', 'niveau', 'commentaire'];

    protected function casts(): array
    {
        return [
            'action' => ActionDemande::class,
            'ancien_statut' => DemandeStatut::class,
            'nouveau_statut' => DemandeStatut::class,
            'created_at' => 'datetime',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
