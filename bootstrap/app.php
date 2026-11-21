<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Active Sanctum SPA (session + cookies)
        $middleware->statefulApi();

        // Exclure test-api du CSRF
        $middleware->validateCsrfTokens(except: [
            'test-api/*',
        ]);

        // ✅ Redirection des invités vers /login (pour les pages web protégées)
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => 
                $request->is('api/*') 
                || $request->is('test-api/*')
                || $request->expectsJson()
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (
                $request->is('api/*') 
                || $request->is('test-api/*')
                || $request->expectsJson()
            ) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            // Sinon → redirection Blade par défaut vers /login
        });
    })->create();