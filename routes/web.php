<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/dashboard', fn () => view('dashboard.index'))->name('dashboard');
Route::get('/design-system', fn () => view('design-system.index'))->name('design-system');