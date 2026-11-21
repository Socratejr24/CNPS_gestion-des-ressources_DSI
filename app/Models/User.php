<?php

namespace App\Models;

use App\Enums\CategorieUtilisateur;
use App\Traits\HasAuditFields;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasAuditFields, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'utilisateurs';

    /**
     * service_id n'est volontairement pas assignable en masse :
     * seul l'administrateur affecte un utilisateur (RG-06), via OrganisationService.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'matricule',
        'identifiant',
        'fonction',
        'categorie',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = [
        'categorie' => 'AGENT',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'categorie' => CategorieUtilisateur::class,
        ];
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    /** Service principal (RG-03). */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'utilisateurs_roles', 'utilisateur_id', 'role_id')
            ->withTimestamps();
    }

    // ------------------------------------------------------------------
    // Organisation (RG-04 : département et structure déduits du service)
    // ------------------------------------------------------------------

    public function aUneAffectation(): bool
    {
        return $this->service_id !== null;
    }

    /**
     * Accès aux fonctionnalités métier (RG-11). L'administrateur en est dispensé :
     * c'est lui qui crée l'organisation et affecte les utilisateurs.
     */
    public function peutAccederAuMetier(): bool
    {
        return $this->aUneAffectation() || $this->estAdministrateur();
    }

    public function departement(): ?Departement
    {
        return $this->service?->departement;
    }

    public function structure(): ?Structure
    {
        return $this->departement()?->structure;
    }

    /** Le N+1 du demandeur (RG-15). */
    public function chefDeService(): ?User
    {
        return $this->service?->chef;
    }

    /** Le N+2 du demandeur (RG-16). */
    public function responsableDepartement(): ?User
    {
        return $this->departement()?->responsable;
    }

    public function estChefDeSonService(): bool
    {
        return $this->service !== null && $this->service->chef_id === $this->id;
    }

    /**
     * Vrai si l'utilisateur porte une responsabilité de validation (N+1 ou N+2).
     */
    public function estValidateur(): bool
    {
        return Service::where('chef_id', $this->id)->exists()
            || Departement::where('responsable_id', $this->id)->exists();
    }

    public function estResponsableDeSonDepartement(): bool
    {
        $departement = $this->departement();

        return $departement !== null && $departement->responsable_id === $this->id;
    }

    // ------------------------------------------------------------------
    // Rôles fonctionnels (SFD §3.3)
    // ------------------------------------------------------------------

    public function hasRole(string $libelle): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->is_active && $role->libelle === $libelle);
    }

    public function hasAnyRole(string ...$libelles): bool
    {
        return collect($libelles)->contains(fn (string $libelle) => $this->hasRole($libelle));
    }

    /**
     * Permission (slug) accordée par l'un des rôles actifs de l'utilisateur.
     */
    public function hasPermission(string $slug): bool
    {
        $this->loadMissing('roles.permissions');

        return $this->roles
            ->where('is_active', true)
            ->flatMap->permissions
            ->contains(fn (Permission $p) => $p->is_active && $p->slug === $slug);
    }

    public function estAdministrateur(): bool
    {
        return $this->hasRole(Role::ADMINISTRATEUR);
    }

    public function nomComplet(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }
}
