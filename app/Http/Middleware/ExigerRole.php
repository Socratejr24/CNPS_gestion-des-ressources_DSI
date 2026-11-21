<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à un ou plusieurs rôles fonctionnels.
 *
 * Usage : ->middleware('role:Administrateur') ou 'role:Receveur,Administrateur'
 */
class ExigerRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = $request->user();

        abort_unless($utilisateur && $utilisateur->hasAnyRole(...$roles), 403, 'Accès non autorisé.');

        return $next($request);
    }
}
