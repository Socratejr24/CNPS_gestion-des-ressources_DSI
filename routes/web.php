<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NouveauPassword123;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TypeUtilisateurController;
use App\Http\Controllers\UserController;


// ============================================
// 🔓 AUTHENTIFICATION WEB (SESSION)
// ============================================
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.forgot');
Route::get('/verify-otp', fn () => view('auth.verify-otp'))->name('password.verify');
Route::get('/reset-password', fn () => view('auth.reset-password'))->name('password.reset');
    
// ============================================
// 🔒 PAGES WEB PROTÉGÉES (session)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect('/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/demandes/mes-demandes', [DemandeController::class, 'index'])->name('demandes.index');
    Route::get('/design-system', fn () => view('design-system.index'))->name('design-system');
});

// ============================================
// 🔓 API — AUTHENTIFICATION (PUBLIC)
// ============================================
Route::prefix('test-api/auth')->group(function () {
    Route::post('/login/agent', [AuthController::class, 'loginAgent']);
    Route::post('/login/prestataire', [AuthController::class, 'loginPrestataire']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// ============================================
// 🔒 API — ROUTES PROTÉGÉES (Sanctum)
// ============================================
Route::prefix('test-api')->middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });


    // Admin
    Route::prefix('admin')->group(function () {
        Route::post('/prestataires', [AuthController::class, 'storePrestataire']);
    });

    // Rôles
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

    // Permissions
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

    // Utilisateurs
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/search', [UserController::class, 'search']);
        Route::get('/{id}', [UserController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [UserController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [UserController::class, 'destroy'])->whereNumber('id');
        Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->whereNumber('id');
        Route::post('/{id}/photo', [UserController::class, 'uploadPhoto'])->whereNumber('id');
        Route::delete('/{id}/photo', [UserController::class, 'deletePhoto'])->whereNumber('id');
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->whereNumber('id');
        Route::get('/{id}/roles', [UserController::class, 'getRoles'])->whereNumber('id');
        Route::post('/{id}/roles', [UserController::class, 'assignRoles'])->whereNumber('id');
        Route::delete('/{id}/roles/{roleId}', [UserController::class, 'removeRole'])
            ->whereNumber('id')->whereNumber('roleId');
    });

    // Types utilisateurs
    Route::prefix('types-utilisateurs')->group(function () {
        Route::get('/', [TypeUtilisateurController::class, 'index']);
        Route::post('/', [TypeUtilisateurController::class, 'store']);
        Route::get('/{id}', [TypeUtilisateurController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [TypeUtilisateurController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [TypeUtilisateurController::class, 'destroy'])->whereNumber('id');
        Route::patch('/{id}/toggle-status', [TypeUtilisateurController::class, 'toggleStatus'])->whereNumber('id');
    });
});

// Health check
Route::get('/up', fn () => response()->json(['status' => 'ok'], 200));