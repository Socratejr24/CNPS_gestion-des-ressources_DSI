<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::get('/', fn () => redirect('/login'));

// --- ROUTES POUR LES TESTS POSTMAN ---
// ⚠️ Sans authentification ni protection CSRF : enregistrées uniquement
// en local et en test. À remplacer par des routes protégées
// (auth + rôle Administrateur) une fois l'authentification en place.
if (app()->environment('local', 'testing')) {
Route::prefix('test-api')->group(function () {
    
    // 1. Rôles
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);

        Route::get('/search', [RoleController::class, 'search']);
        Route::get('/paginate', [RoleController::class, 'paginate']);
        Route::get('/name/{name}', [RoleController::class, 'getByName']);

      // Gestion des permissions pour les rôles
      Route::get('/{id}/permissions', [RoleController::class, 'getPermissions'])->whereNumber('id');
    Route::post('/{id}/permissions', [RoleController::class, 'assignPermissions'])->whereNumber('id');
    Route::delete('/{id}/permissions/{permissionId}', [RoleController::class, 'removePermission'])
        ->whereNumber('id')->whereNumber('permissionId');

        
        Route::get('/{id}', [RoleController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [RoleController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->whereNumber('id');

    Route::patch('/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->whereNumber('id');
    });

    // 2. Permissions
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store']);

        Route::get('/search', [PermissionController::class, 'search']);
        Route::get('/paginate', [PermissionController::class, 'paginate']);

        Route::get('/slug/{slug}', [PermissionController::class, 'getBySlug']); // Spécifique d'abord
        Route::get('/{id}', [PermissionController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [PermissionController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->whereNumber('id');

         Route::patch('/{id}/toggle-status', [PermissionController::class, 'toggleStatus'])->whereNumber('id');
    });
});
}

// --- ROUTES DU FRONT (Blade) ---
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/design-system', fn () => view('design-system.index'))->name('design-system');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Health check : route `/up` fournie par Laravel (voir bootstrap/app.php).

Route::get('/demandes/mes-demandes', [DemandeController::class, 'index'])->name('demandes.index');