<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',

        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Routes Postman de développement (enregistrées seulement en local/test,
        // cf. routes/web.php).
        $middleware->validateCsrfTokens(except: [
            'test-api/*',
        ]);

        // Authentification
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
        $middleware->web(append: [
            \App\Http\Middleware\VerifierCompteActif::class,
        ]);

        $middleware->alias([
            'affectation' => \App\Http\Middleware\ExigerAffectation::class,
            'role' => \App\Http\Middleware\ExigerRole::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'test-api/*') || $request->expectsJson(),
        );
    })->create();
