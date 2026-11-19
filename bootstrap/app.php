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
        // Exclure les routes test-api du CSRF pour Postman
        $middleware->validateCsrfTokens(except: [
            'test-api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //  Retourner du JSON pour l'API (test-api inclus !)
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => 
                $request->is('api/*') 
                || $request->is('test-api/*')   
                || $request->expectsJson()
        );

        // Gérer les erreurs d'auth en JSON (jamais de redirection /login)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (
                $request->is('api/*') 
                || $request->is('test-api/*')   
                || $request->expectsJson()
            ) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
        });
    })->create();