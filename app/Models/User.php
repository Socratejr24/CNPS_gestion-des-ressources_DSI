<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, HasApiTokens,Notifiable, SoftDeletes, HasAuditFields;
   
    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'matricule',
        'identifiant',
        'fonction',
        'photo_path',
        'telephone',
        'is_active',
        'must_change_password',
        'is_ldap',
        'type_utilisateur_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
             'is_ldap' => 'boolean',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }
 // ==========================================
    // ACCESSORS
    // ==========================================

/**
     * URL complète de la photo de profil.
     * Retourne null si pas de photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }
        return asset('storage/' . $this->photo_path);
    }




//  Relations de utilisateurs:

        //  NOUVELLE RELATION : Rôles de l'utilisateur
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'utilisateurs_roles', 'utilisateur_id', 'role_id')
                    ->withTimestamps();
    }


    // Relation avec le type d'utilisateur N-1
public function typeUtilisateur(): BelongsTo
    {
        return $this->belongsTo(TypeUtilisateur::class);
    }


    
}