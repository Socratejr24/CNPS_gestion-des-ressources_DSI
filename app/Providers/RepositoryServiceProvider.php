<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


// REPOSITORIES INTERFACES
use App\Repositories\Interfaces\IRoleRepository;
use App\Repositories\Interfaces\IPermissionRepository; // <-- Ajouté


// REPOSITORIES IMPLEMENTATIONS

     // Roles
use App\Repositories\Implementations\RoleRepository;

     // Permissions
use App\Repositories\Implementations\PermissionRepository; // <-- Ajouté


// SERVICES INTERFACES

     // Roles
use App\Services\Interfaces\IRoleService;

     // Permissions
use App\Services\Interfaces\IPermissionService; // <-- Ajouté


// SERVICES IMPLEMENTATIONS

        // Roles
use App\Services\Implementations\RoleService;

        // Permissions
use App\Services\Implementations\PermissionService; // <-- Ajouté

        // Organisation & utilisateurs
use App\Services\Interfaces\IOrganisationService;
use App\Services\Interfaces\IUtilisateurService;
use App\Services\Implementations\OrganisationService;
use App\Services\Implementations\UtilisateurService;




class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
   
    {

         // Bindings des Repositories

             //Roles
$this->app->bind(IRoleRepository::class, RoleRepository::class);

             //Permissions
$this->app->bind(IPermissionRepository::class, PermissionRepository::class); // <-- Ajouté



         // Bindings des services
            
             //Roles    
        $this->app->bind(IRoleService::class, RoleService::class);

             //Permissions
        $this->app->bind(IPermissionService::class, PermissionService::class); // <-- Ajouté

             //Organisation & utilisateurs
        $this->app->bind(IOrganisationService::class, OrganisationService::class);
        $this->app->bind(IUtilisateurService::class, UtilisateurService::class);

             //Référentiels (applications, ressources)
        $this->app->bind(\App\Services\Interfaces\IReferentielService::class, \App\Services\Implementations\ReferentielService::class);

             //Demandes
        $this->app->bind(\App\Services\Interfaces\IDemandeService::class, \App\Services\Implementations\DemandeService::class);
    }
}