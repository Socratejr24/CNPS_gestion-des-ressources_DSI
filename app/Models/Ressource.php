<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ressource du référentiel (SFD §5) : ce qu'une demande peut solliciter.
 */
class Ressource extends Model
{
    use HasAuditFields, HasFactory;

    protected $table = 'ressources';

    protected $fillable = ['reference', 'designation', 'categorie', 'description', 'informations_complementaires', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
