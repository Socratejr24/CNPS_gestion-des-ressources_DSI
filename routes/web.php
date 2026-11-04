<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', fn () => redirect('/login'));

Route::get('/login', fn () => view('auth.login'))->name('login');
// Route de test 404 (à retirer plus tard)

Route::get('/design-system', fn () => view('design-system.index'))->name('design-system');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


// Health check pour la détection offline
Route::get('/up', function () {
    return response()->json(['status' => 'ok'], 200);
});