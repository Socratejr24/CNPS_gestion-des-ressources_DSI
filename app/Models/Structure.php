<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Structure extends Model
{
    use HasAuditFields, HasFactory;

    protected $table = 'structures';

    protected $fillable = ['code', 'libelle', 'description', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function departements(): HasMany
    {
        return $this->hasMany(Departement::class);
    }
}
