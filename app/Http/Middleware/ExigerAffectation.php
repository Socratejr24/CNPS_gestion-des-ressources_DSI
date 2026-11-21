<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RG-11 : un utilisateur sans affectation organisationnelle ne peut pas accéder
 * aux fonctionnalités métier ; il est redirigé vers la page « affectation en attente ».
 * L'administrateur en est dispensé.
 */
class ExigerAffectation
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        if ($utilisateur && ! $utilisateur->peutAccederAuMetier()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Votre affectation organisationnelle est en attente.'], 403)
                : redirect()->route('affectation.attente');
        }

        return $next($request);
    }
}
