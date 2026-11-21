<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CategorieUtilisateur;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthentificationService;
use App\Services\Auth\EchecAuthentificationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConnexionController extends Controller
{
    private const TENTATIVES_MAX = 5;

    private const BLOCAGE_SECONDES = 60;

    public function __construct(private AuthentificationService $authentification) {}

    public function create()
    {
        return view('auth.login');
    }

    /**
     * Connexion. Accepte un formulaire classique ou un appel JSON (page de login Alpine).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:agent,prestataire'],
            'identifiant' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'identifiant.required' => 'Veuillez saisir votre identifiant.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        $cle = $this->cleLimitation($request, $validated['identifiant']);

        if (RateLimiter::tooManyAttempts($cle, self::TENTATIVES_MAX)) {
            $secondes = RateLimiter::availableIn($cle);

            return $this->echec($request, "Trop de tentatives. Réessayez dans {$secondes} secondes.", 429);
        }

        $categorie = $validated['mode'] === 'prestataire'
            ? CategorieUtilisateur::PRESTATAIRE
            : CategorieUtilisateur::AGENT;

        try {
            $utilisateur = $this->authentification->authentifier(
                $categorie, $validated['identifiant'], $validated['password']
            );
        } catch (EchecAuthentificationException $e) {
            RateLimiter::hit($cle, self::BLOCAGE_SECONDES);
            Log::info('Échec de connexion', ['mode' => $validated['mode'], 'identifiant' => $validated['identifiant'], 'ip' => $request->ip()]);

            return $this->echec($request, $e->getMessage());
        }

        RateLimiter::clear($cle);
        Auth::login($utilisateur);
        $request->session()->regenerate();

        Log::info('Connexion réussie', ['utilisateur_id' => $utilisateur->id, 'ip' => $request->ip()]);

        $destination = $utilisateur->peutAccederAuMetier()
            ? redirect()->intended(route('dashboard'))->getTargetUrl()
            : route('affectation.attente');

        return $request->expectsJson()
            ? response()->json(['redirect' => $destination])
            : redirect()->to($destination);
    }

    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->expectsJson()
            ? response()->json(['redirect' => route('login')])
            : redirect()->route('login');
    }

    private function echec(Request $request, string $message, int $statut = 422): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $statut);
        }

        throw ValidationException::withMessages(['identifiant' => $message])->status($statut);
    }

    private function cleLimitation(Request $request, string $identifiant): string
    {
        return 'connexion:'.Str::transliterate(Str::lower(trim($identifiant))).'|'.$request->ip();
    }
}
