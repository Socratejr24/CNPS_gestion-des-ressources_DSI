<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ressource demandée et quantité (SFD §6.2), puis quantité fournie (SFD §8.4).
 */
class DemandeLigne extends Model
{
    protected $table = 'demande_lignes';

    protected $fillable = ['ressource_id', 'quantite_demandee', 'quantite_fournie', 'observation'];

    protected function casts(): array
    {
        return [
            'quantite_demandee' => 'integer',
            'quantite_fournie' => 'integer',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class);
    }
}
