<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TypeUtilisateurController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// ============================================
//  ROUTES PUBLIQUES (AUTHENTIFICATION)
// ============================================
Route::prefix('test-api/auth')->group(function () {
    Route::post('/login/agent', [AuthController::class, 'loginAgent']);
    Route::post('/login/prestataire', [AuthController::class, 'loginPrestataire']);
    
    // Mot de passe oublié (Phase 1)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// ============================================
//  ROUTES PROTÉGÉES (AUTH REQUISE)
// ============================================
Route::prefix('test-api')->middleware('auth:sanctum')->group(function () {
    
    // ============ AUTH PROTÉGÉ ============
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // ============ ADMINISTRATION ============
    Route::prefix('admin')->group(function () {
        // Création de prestataire
        Route::post('/prestataires', [AuthController::class, 'storePrestataire']);
        
        // (Futur) Autres routes admin : lister les prestataires, etc.
    });

    // ============ RÔLES ============
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);

        Route::get('/search', [RoleController::class, 'search']);
        Route::get('/paginate', [RoleController::class, 'paginate']);
        Route::get('/name/{name}', [RoleController::class, 'getByName']);

        Route::get('/{id}/permissions', [RoleController::class, 'getPermissions'])->whereNumber('id');
        Route::post('/{id}/permissions', [RoleController::class, 'assignPermissions'])->whereNumber('id');
        Route::delete('/{id}/permissions/{permissionId}', [RoleController::class, 'removePermission'])
            ->whereNumber('id')->whereNumber('permissionId');

        Route::get('/{id}', [RoleController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [RoleController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->whereNumber('id');
        Route::patch('/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->whereNumber('id');
    });

    // ============ PERMISSIONS ============
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store']);

        Route::get('/search', [PermissionController::class, 'search']);
        Route::get('/paginate', [PermissionController::class, 'paginate']);
        Route::get('/slug/{slug}', [PermissionController::class, 'getBySlug']);

        Route::get('/{id}', [PermissionController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [PermissionController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->whereNumber('id');
        Route::patch('/{id}/toggle-status', [PermissionController::class, 'toggleStatus'])->whereNumber('id');
    });

    // ============ UTILISATEURS (Étape 5) ============
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/search', [UserController::class, 'search']);
        Route::get('/{id}', [UserController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [UserController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [UserController::class, 'destroy'])->whereNumber('id');
        Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->whereNumber('id');

        // Photo
        Route::post('/{id}/photo', [UserController::class, 'uploadPhoto'])->whereNumber('id');
        Route::delete('/{id}/photo', [UserController::class, 'deletePhoto'])->whereNumber('id');

        // Reset password (admin)
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->whereNumber('id');

        // Rôles (Étape 6)
        Route::get('/{id}/roles', [UserController::class, 'getRoles'])->whereNumber('id');
        Route::post('/{id}/roles', [UserController::class, 'assignRoles'])->whereNumber('id');
        Route::delete('/{id}/roles/{roleId}', [UserController::class, 'removeRole'])
            ->whereNumber('id')->whereNumber('roleId');
    });




    // ============ TYPES UTILISATEURS ============
    Route::prefix('types-utilisateurs')->group(function () {
        Route::get('/', [TypeUtilisateurController::class, 'index']);
        Route::post('/', [TypeUtilisateurController::class, 'store']);
        Route::get('/{id}', [TypeUtilisateurController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [TypeUtilisateurController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [TypeUtilisateurController::class, 'destroy'])->whereNumber('id');
        Route::patch('/{id}/toggle-status', [TypeUtilisateurController::class, 'toggleStatus'])->whereNumber('id');
    });
});

// ============================================
// 🖥️ ROUTES DU FRONT (Blade)
// ============================================
Route::get('/', fn () => redirect('/login'));

Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/design-system', fn () => view('design-system.index'))->name('design-system');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/demandes/mes-demandes', [DemandeController::class, 'index'])->name('demandes.index');

// Health check
Route::get('/up', fn () => response()->json(['status' => 'ok'], 200));