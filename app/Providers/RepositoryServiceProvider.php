<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Auth
use App\Repositories\Interfaces\IAuthRepository;
use App\Repositories\Implementations\AuthRepository;
use App\Repositories\Interfaces\IOtpRepository;
use App\Repositories\Implementations\OtpRepository;
use App\Services\Interfaces\IAuthService;
use App\Services\Implementations\AuthService;

// Users
use App\Repositories\Interfaces\IUserRepository;
use App\Repositories\Implementations\UserRepository;
use App\Services\Interfaces\IUserService;
use App\Services\Implementations\UserService;



// Roles
use App\Repositories\Interfaces\IRoleRepository;
use App\Repositories\Implementations\RoleRepository;
use App\Services\Interfaces\IRoleService;
use App\Services\Implementations\RoleService;

// Permissions
use App\Repositories\Interfaces\IPermissionRepository;
use App\Repositories\Implementations\PermissionRepository;
use App\Services\Interfaces\IPermissionService;
use App\Services\Implementations\PermissionService;

// Types Utilisateurs
use App\Repositories\Interfaces\ITypeUtilisateurRepository;
use App\Repositories\Implementations\TypeUtilisateurRepository;
use App\Services\Interfaces\ITypeUtilisateurService;
use App\Services\Implementations\TypeUtilisateurService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ============ AUTH ============
        $this->app->bind(IAuthRepository::class, AuthRepository::class);
        $this->app->bind(IOtpRepository::class, OtpRepository::class);
        $this->app->bind(IAuthService::class, AuthService::class);

        // ============ ROLES ============
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
        $this->app->bind(IRoleService::class, RoleService::class);

        // ============ PERMISSIONS ============
        $this->app->bind(IPermissionRepository::class, PermissionRepository::class);
        $this->app->bind(IPermissionService::class, PermissionService::class);

        // ============ TYPES UTILISATEURS ============
        $this->app->bind(ITypeUtilisateurRepository::class, TypeUtilisateurRepository::class);
        $this->app->bind(ITypeUtilisateurService::class, TypeUtilisateurService::class);

        // ============ USERS ============
$this->app->bind(IUserRepository::class, UserRepository::class);
$this->app->bind(IUserService::class, UserService::class);
    }

    public function boot(): void
    {
        //
    }
}