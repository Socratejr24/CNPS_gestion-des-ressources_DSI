<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\Admin\UtilisateurController as AdminUtilisateurController;
use App\Http\Controllers\Admin\OrganisationController as AdminOrganisationController;
use App\Http\Controllers\Admin\StructureController as AdminStructureController;
use App\Http\Controllers\Admin\DepartementController as AdminDepartementController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Models\Role;

Route::get('/', fn () => redirect('/login'));

// --- ROUTES POUR LES TESTS POSTMAN ---
// ⚠️ Sans authentification ni protection CSRF : enregistrées uniquement
// en local et en test. À remplacer par des routes protégées
// (auth + rôle Administrateur) une fois l'authentification en place.
if (app()->environment('local', 'testing')) {
Route::prefix('test-api')->name('test-api.')->group(function () {
    
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

    // 3. Organisation (SFD §2) — pas de suppression : désactivation uniquement
    Route::apiResource('structures', StructureController::class)->except('destroy');
    Route::patch('structures/{structure}/toggle-status', [StructureController::class, 'toggleStatus']);

    Route::apiResource('departements', DepartementController::class)->except('destroy');
    Route::patch('departements/{departement}/toggle-status', [DepartementController::class, 'toggleStatus']);
    Route::put('departements/{departement}/responsable', [DepartementController::class, 'designerResponsable']);

    Route::apiResource('services', ServiceController::class)->except('destroy');
    Route::patch('services/{service}/toggle-status', [ServiceController::class, 'toggleStatus']);
    Route::put('services/{service}/chef', [ServiceController::class, 'designerChef']);

    // 4. Utilisateurs (SFD §3)
    Route::apiResource('utilisateurs', UtilisateurController::class)->except('destroy');
    Route::patch('utilisateurs/{utilisateur}/toggle-status', [UtilisateurController::class, 'toggleStatus']);
    Route::put('utilisateurs/{utilisateur}/affectation', [UtilisateurController::class, 'affecter']);
    Route::put('utilisateurs/{utilisateur}/roles', [UtilisateurController::class, 'attribuerRoles']);
});
}

// --- ROUTES DU FRONT (Blade) ---

// Health check : route `/up` fournie par Laravel (voir bootstrap/app.php).

// Authentification (SFD §3.1)
Route::middleware('guest')->group(function () {
    Route::get('/login', [ConnexionController::class, 'create'])->name('login');
    Route::post('/login', [ConnexionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [ConnexionController::class, 'destroy'])->name('logout');

    // RG-11 : page affichée tant que l'administrateur n'a pas affecté l'utilisateur.
    Route::get('/affectation-en-attente', function () {
        return auth()->user()->peutAccederAuMetier()
            ? redirect()->route('dashboard')
            : view('auth.affectation-en-attente');
    })->name('affectation.attente');

    // Fonctionnalités métier : nécessitent une affectation organisationnelle.
    Route::middleware('affectation')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/demandes/mes-demandes', [DemandeController::class, 'index'])->name('demandes.index');
    });

    // Administration (SFD §3) — réservée au rôle Administrateur.
    Route::prefix('admin')->name('admin.')->middleware('role:'.Role::ADMINISTRATEUR)->group(function () {
        Route::get('/utilisateurs', [AdminUtilisateurController::class, 'index'])->name('utilisateurs.index');
        Route::get('/utilisateurs/nouveau', [AdminUtilisateurController::class, 'create'])->name('utilisateurs.create');
        Route::post('/utilisateurs', [AdminUtilisateurController::class, 'store'])->name('utilisateurs.store');
        Route::get('/utilisateurs/{utilisateur}', [AdminUtilisateurController::class, 'show'])->name('utilisateurs.show');
        Route::put('/utilisateurs/{utilisateur}', [AdminUtilisateurController::class, 'update'])->name('utilisateurs.update');
        Route::put('/utilisateurs/{utilisateur}/affectation', [AdminUtilisateurController::class, 'affecter'])->name('utilisateurs.affectation');
        Route::put('/utilisateurs/{utilisateur}/roles', [AdminUtilisateurController::class, 'attribuerRoles'])->name('utilisateurs.roles');
        Route::patch('/utilisateurs/{utilisateur}/statut', [AdminUtilisateurController::class, 'changerStatut'])->name('utilisateurs.statut');

        // Organisation (SFD §2) — pas de suppression : désactivation uniquement.
        Route::prefix('organisation')->name('organisation.')->group(function () {
            Route::get('/', [AdminOrganisationController::class, 'index'])->name('index');

            Route::get('/structures/nouvelle', [AdminStructureController::class, 'create'])->name('structures.create');
            Route::post('/structures', [AdminStructureController::class, 'store'])->name('structures.store');
            Route::get('/structures/{structure}/modifier', [AdminStructureController::class, 'edit'])->name('structures.edit');
            Route::put('/structures/{structure}', [AdminStructureController::class, 'update'])->name('structures.update');
            Route::patch('/structures/{structure}/statut', [AdminStructureController::class, 'changerStatut'])->name('structures.statut');

            Route::get('/departements/nouveau', [AdminDepartementController::class, 'create'])->name('departements.create');
            Route::post('/departements', [AdminDepartementController::class, 'store'])->name('departements.store');
            Route::get('/departements/{departement}/modifier', [AdminDepartementController::class, 'edit'])->name('departements.edit');
            Route::put('/departements/{departement}', [AdminDepartementController::class, 'update'])->name('departements.update');
            Route::patch('/departements/{departement}/statut', [AdminDepartementController::class, 'changerStatut'])->name('departements.statut');
            Route::put('/departements/{departement}/responsable', [AdminDepartementController::class, 'designerResponsable'])->name('departements.responsable');

            Route::get('/services/nouveau', [AdminServiceController::class, 'create'])->name('services.create');
            Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
            Route::get('/services/{service}/modifier', [AdminServiceController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
            Route::patch('/services/{service}/statut', [AdminServiceController::class, 'changerStatut'])->name('services.statut');
            Route::put('/services/{service}/chef', [AdminServiceController::class, 'designerChef'])->name('services.chef');
        });
    });
});
