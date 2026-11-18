<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Violation d'une règle de gestion (SFD §15).
 *
 * Rendue en 409 Conflict (JSON) ou en retour arrière avec message d'erreur (Blade).
 */
class RegleMetierException extends RuntimeException
{
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('test-api/*')) {
            return response()->json(['message' => $this->getMessage()], 409);
        }

        return back()->withInput()->withErrors(['regle_metier' => $this->getMessage()]);
    }
}
