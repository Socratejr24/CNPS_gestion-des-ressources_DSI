<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Application du référentiel (SFD §4). Une demande peut y être associée (RG-12).
 */
class Application extends Model
{
    use HasAuditFields, HasFactory;

    protected $table = 'applications';

    protected $fillable = ['reference', 'nom', 'description', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
