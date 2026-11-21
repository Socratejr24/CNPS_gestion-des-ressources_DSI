<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginAgentRequest;
use App\Http\Requests\LoginPrestataireRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\StorePrestataireRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\Interfaces\IAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function __construct(
        private IAuthService $authService
    ) {}

   public function loginAgent(LoginAgentRequest $request)
{
    $result = $this->authService->loginAgentLDAP(
        $request->validated('matricule'), 
        $request->validated('password')
    );

    return response()->json([
        'message' => 'Connexion Agent réussie',
        'user'    => new UserResource($result['user']),
        'token'   => $result['token'],
    ]);
}

    public function loginPrestataire(LoginPrestataireRequest $request)
    {
        $result = $this->authService->loginPrestataire(
            $request->validated('identifiant'),
            $request->validated('password')
        );

        return response()->json([
            'message' => 'Connexion Prestataire réussie',
            'user'    => new UserResource($result['user']),
            'token'   => $result['token'],
        ]);
    }

    public function storePrestataire(StorePrestataireRequest $request)
    {
        $prestataire = $this->authService->createPrestataire($request->validated());

        return response()->json([
            'message' => 'Compte prestataire créé avec succès',
            'user'    => new UserResource($prestataire),
        ], 201);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->authService->forgotPassword($request->validated('email'));

        return response()->json([
            'message' => 'Si un compte existe avec cet email, un code vous a été envoyé.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $isValid = $this->authService->verifyOtp(
            $request->validated('email'),
            $request->validated('otp')
        );

        if (!$isValid) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 400);
        }

        return response()->json(['message' => 'Code valide.']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword(
            $request->validated('email'),
            $request->validated('otp'),
            $request->validated('password')
        );

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }

/**
 * Login WEB (session, pas token) — pour le formulaire Blade
 */
public function loginWeb(Request $request)
{
    //  Validation avec messages en français
    $validated = $request->validate([
        'mode'        => ['required', 'in:agent,prestataire'],
        'identifiant' => ['required', 'string', 'min:1'],
        'password'    => ['required', 'string', 'min:1'],
    ], [
        'mode.required'        => 'Le mode de connexion est requis.',
        'mode.in'              => 'Le mode de connexion est invalide.',
        'identifiant.required' => 'Le champ identifiant est requis.',
        'identifiant.string'   => 'L\'identifiant doit être une chaîne de caractères.',
        'identifiant.min'      => 'L\'identifiant est trop court.',
        'password.required'    => 'Le mot de passe est requis.',
        'password.string'      => 'Le mot de passe doit être une chaîne de caractères.',
        'password.min'         => 'Le mot de passe est trop court.',
    ]);

    try {
        if ($validated['mode'] === 'agent') {
            $result = $this->authService->loginAgentLDAP(
                $validated['identifiant'],
                $validated['password']
            );
        } else {
            $result = $this->authService->loginPrestataire(
                $validated['identifiant'],
                $validated['password']
            );
        }

        // Connexion en session
        Auth::login($result['user']);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'redirect' => route('dashboard'),
            'user'     => new UserResource($result['user']),
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();
        $message = $errors['matricule'][0] 
                ?? $errors['identifiant'][0] 
                ?? $errors['email'][0] 
                ?? 'Identifiants invalides.';

        return response()->json([
            'success' => false,
            'message' => $message,
        ], 422);
    }
}
/**
 * Logout WEB — détruit la session
 */
public function logoutWeb(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
}

public function changePassword(ChangePasswordRequest $request)
{
    $this->authService->changePassword(
        $request->user(),
        $request->validated('current_password'),
        $request->validated('password')
    );

    return response()->json([
        'success' => true,
        'message' => 'Mot de passe modifié avec succès.',
    ]);
}
}