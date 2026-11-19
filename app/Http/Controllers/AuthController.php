<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginAgentRequest;
use App\Http\Requests\LoginPrestataireRequest;
use App\Http\Requests\StorePrestataireRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\Interfaces\IAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private IAuthService $authService
    ) {}

   public function loginAgent(LoginAgentRequest $request)
{
    $result = $this->authService->loginAgentLDAP(
        $request->validated('matricule'), // ✅ On passe le matricule
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
}