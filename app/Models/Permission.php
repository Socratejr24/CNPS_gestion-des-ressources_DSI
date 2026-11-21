<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory, SoftDeletes, HasAuditFields;

    protected $table = 'permissions';

    protected $attributes = ['is_active' => true];

    /**
     * Permissions créées par le seeder et identifiées par leur slug :
     * leur slug ne peut pas changer et elles ne peuvent pas être supprimées.
     */
    public const SYSTEME = ['voir.traitement', 'voir.administration'];

    public function estSysteme(): bool
    {
        return in_array($this->getOriginal('slug') ?? $this->slug, self::SYSTEME, true);
    }


    protected $fillable = [
        'libelle',
        'description',
        'is_active',
        'slug',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

 // Relations de permissions:
   
    // Relation avec les roles N-N
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_permissions', 'permission_id', 'role_id')
                    ->withTimestamps();
    }
}