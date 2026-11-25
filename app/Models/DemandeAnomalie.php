<?php

namespace App\Models;

use App\Enums\NatureAnomalie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Anomalie de réception signalée par le demandeur (SFD §9.2) et sa régularisation (SFD §9.3).
 */
class DemandeAnomalie extends Model
{
    protected $table = 'demande_anomalies';

    protected $fillable = ['signalee_par_id', 'nature', 'description', 'lignes'];

    protected function casts(): array
    {
        return [
            'nature' => NatureAnomalie::class,
            'lignes' => 'array',
            'regularisee_le' => 'datetime',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function signaleePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signalee_par_id');
    }

    public function regulariseePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'regularisee_par_id');
    }

    public function estRegularisee(): bool
    {
        return $this->regularisee_le !== null;
    }
}
