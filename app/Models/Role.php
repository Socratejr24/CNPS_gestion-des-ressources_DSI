<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory, SoftDeletes, HasAuditFields;

    protected $table = 'roles';


    protected $fillable = [
        'libelle',
        'description',
        'is_active',
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



  //  Relations de roles:

         // Relation avec les permissions N-N
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions', 'role_id', 'permission_id')
                    ->withTimestamps();
    }
}