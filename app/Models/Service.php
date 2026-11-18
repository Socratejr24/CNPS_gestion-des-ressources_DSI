<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasAuditFields, HasFactory;

    protected $table = 'services';

    /** chef_id n'est pas assignable en masse : passer par OrganisationService. */
    protected $fillable = ['departement_id', 'code', 'libelle', 'description', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    /** N+1 (RG-15). */
    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
