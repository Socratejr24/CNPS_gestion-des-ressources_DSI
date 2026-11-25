<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\TraitementController;
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
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\RessourceController as AdminRessourceController;
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
        // Demandes (SFD §6) : l'accès à chaque demande est contrôlé par DemandePolicy (RG-34).
        Route::get('/demandes/mes-demandes', [DemandeController::class, 'index'])->name('demandes.index');
        Route::get('/demandes/nouvelle', [DemandeController::class, 'create'])->name('demandes.create');
        Route::post('/demandes', [DemandeController::class, 'store'])->name('demandes.store');
        Route::get('/demandes/{demande}', [DemandeController::class, 'show'])->whereNumber('demande')->name('demandes.show');
        Route::get('/demandes/{demande}/modifier', [DemandeController::class, 'edit'])->whereNumber('demande')->name('demandes.edit');
        Route::put('/demandes/{demande}', [DemandeController::class, 'update'])->whereNumber('demande')->name('demandes.update');
        Route::delete('/demandes/{demande}', [DemandeController::class, 'destroy'])->whereNumber('demande')->name('demandes.destroy');
        Route::post('/demandes/{demande}/soumettre', [DemandeController::class, 'soumettre'])->whereNumber('demande')->name('demandes.soumettre');
        Route::post('/demandes/{demande}/reponse', [DemandeController::class, 'repondre'])->whereNumber('demande')->name('demandes.repondre');

        // Validation hiérarchique N+1 / N+2 (SFD §7) — contrôle fin par DemandePolicy::decider.
        Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');
        Route::post('/demandes/{demande}/valider', [ValidationController::class, 'valider'])->whereNumber('demande')->name('validations.valider');
        Route::post('/demandes/{demande}/rejeter', [ValidationController::class, 'rejeter'])->whereNumber('demande')->name('validations.rejeter');
        Route::post('/demandes/{demande}/informations', [ValidationController::class, 'demanderInformations'])->whereNumber('demande')->name('validations.informations');

        // Traitement par le receveur (SFD §8, §9.3) — contrôle fin par DemandePolicy.
        Route::get('/traitement', [TraitementController::class, 'index'])->name('traitement.index');
        Route::post('/demandes/{demande}/prise-en-charge', [TraitementController::class, 'prendreEnCharge'])->whereNumber('demande')->name('traitement.prendre');
        Route::post('/demandes/{demande}/informations-receveur', [TraitementController::class, 'demanderInformations'])->whereNumber('demande')->name('traitement.informations');
        Route::post('/demandes/{demande}/mise-a-disposition', [TraitementController::class, 'mettreADisposition'])->whereNumber('demande')->name('traitement.disposition');
        Route::post('/demandes/{demande}/impossibilite', [TraitementController::class, 'signalerImpossibilite'])->whereNumber('demande')->name('traitement.impossibilite');
        Route::post('/demandes/{demande}/regularisation', [TraitementController::class, 'regulariser'])->whereNumber('demande')->name('traitement.regularisation');

        // Réception par le demandeur (SFD §9.1, §9.2).
        Route::post('/demandes/{demande}/confirmation', [DemandeController::class, 'confirmerReception'])->whereNumber('demande')->name('demandes.confirmer');
        Route::post('/demandes/{demande}/anomalie', [DemandeController::class, 'signalerAnomalie'])->whereNumber('demande')->name('demandes.anomalie');
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

        // Référentiels des demandes (SFD §4 et §5) — pas de suppression : désactivation.
        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/nouvelle', [AdminApplicationController::class, 'create'])->name('applications.create');
        Route::post('/applications', [AdminApplicationController::class, 'store'])->name('applications.store');
        Route::get('/applications/{application}/modifier', [AdminApplicationController::class, 'edit'])->name('applications.edit');
        Route::put('/applications/{application}', [AdminApplicationController::class, 'update'])->name('applications.update');
        Route::patch('/applications/{application}/statut', [AdminApplicationController::class, 'changerStatut'])->name('applications.statut');

        Route::get('/ressources', [AdminRessourceController::class, 'index'])->name('ressources.index');
        Route::get('/ressources/nouvelle', [AdminRessourceController::class, 'create'])->name('ressources.create');
        Route::post('/ressources', [AdminRessourceController::class, 'store'])->name('ressources.store');
        Route::get('/ressources/{ressource}/modifier', [AdminRessourceController::class, 'edit'])->name('ressources.edit');
        Route::put('/ressources/{ressource}', [AdminRessourceController::class, 'update'])->name('ressources.update');
        Route::patch('/ressources/{ressource}/statut', [AdminRessourceController::class, 'changerStatut'])->name('ressources.statut');

        // Rôles fonctionnels (SFD §3.3) et catalogue des permissions.
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [AdminRoleController::class, 'index'])->name('index');
            Route::get('/nouveau', [AdminRoleController::class, 'create'])->name('create');
            Route::post('/', [AdminRoleController::class, 'store'])->name('store');

            Route::get('/permissions/nouvelle', [AdminPermissionController::class, 'create'])->name('permissions.create');
            Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('permissions.store');
            Route::get('/permissions/{permission}/modifier', [AdminPermissionController::class, 'edit'])->name('permissions.edit')->whereNumber('permission');
            Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('permissions.update')->whereNumber('permission');
            Route::patch('/permissions/{permission}/statut', [AdminPermissionController::class, 'changerStatut'])->name('permissions.statut')->whereNumber('permission');
            Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy'])->name('permissions.destroy')->whereNumber('permission');

            Route::get('/{role}/modifier', [AdminRoleController::class, 'edit'])->name('edit')->whereNumber('role');
            Route::put('/{role}', [AdminRoleController::class, 'update'])->name('update')->whereNumber('role');
            Route::put('/{role}/permissions', [AdminRoleController::class, 'permissions'])->name('permissions.sync')->whereNumber('role');
            Route::patch('/{role}/statut', [AdminRoleController::class, 'changerStatut'])->name('statut')->whereNumber('role');
            Route::delete('/{role}', [AdminRoleController::class, 'destroy'])->name('destroy')->whereNumber('role');
        });
    });
});
