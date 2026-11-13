<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;

// Routes pour tester les rôles via Postman
Route::apiResource('roles', RoleController::class);