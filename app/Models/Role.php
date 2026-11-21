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

    // Rôles fonctionnels de la SFD (§3.3)
    public const ADMINISTRATEUR = 'Administrateur';

    public const DEMANDEUR = 'Demandeur';

    public const RECEVEUR = 'Receveur';

    /**
     * Rôles référencés par le code (accès admin, rôle par défaut LDAP, menu) :
     * ils ne peuvent être ni renommés, ni désactivés, ni supprimés.
     */
    public const SYSTEME = [self::ADMINISTRATEUR, self::DEMANDEUR, self::RECEVEUR];

    public function estSysteme(): bool
    {
        return in_array($this->getOriginal('libelle') ?? $this->libelle, self::SYSTEME, true);
    }

    protected $table = 'roles';

    protected $attributes = ['is_active' => true];

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

    // Relation avec les utilisateurs N-N
    public function utilisateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'utilisateurs_roles', 'role_id', 'utilisateur_id')
            ->withTimestamps();
    }
}