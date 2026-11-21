@extends('layouts.guest')

@section('title', 'Nouveau mot de passe')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

<div class="login-bg" x-data="resetPasswordForm()">

    {{-- Blobs décoratifs --}}
    <span class="login-blob login-blob--orange"></span>
    <span class="login-blob login-blob--green"></span>
    <span class="login-blob login-blob--yellow"></span>

    <div class="login-card">

        <img src="{{ asset('images/logo-cnps.jpeg') }}"
             alt="CNPS"
             class="login-logo">

        <h1 class="login-title">Nouveau mot de passe</h1>
        <p class="login-subtitle">
            Choisissez un nouveau mot de passe pour votre compte
        </p>

        {{-- Erreur globale --}}
        <template x-if="errorMessage">
            <div class="login-error" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2"
                     style="width:16px;height:16px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="errorMessage"></span>
            </div>
        </template>

        {{-- Succès --}}
        <template x-if="successMessage">
            <div class="login-error" role="alert"
                 style="background:#e6f7eb;color:#088e39;border-left-color:#088e39;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2"
                     style="width:16px;height:16px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="successMessage"></span>
            </div>
        </template>

        <form @submit.prevent="submit">

            {{-- Nouveau mot de passe --}}
            <div class="login-field">
                <label for="password" class="login-label">
                    Nouveau mot de passe
                </label>
                <div class="login-password-wrap">
                    <input :type="showPassword ? 'text' : 'password'"
                           id="password"
                           name="password"
                           x-model="password"
                           class="login-input"
                           placeholder="••••••••"
                           minlength="8"
                           required>
                    <button type="button"
                            class="login-eye-btn"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Masquer' : 'Afficher'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                             fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8"
                             style="width:20px;height:20px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
                <p class="login-hint" style="margin-top: 0.4rem;">
                    Minimum 8 caractères, avec lettres, chiffres et symboles.
                </p>
            </div>

            {{-- Confirmation --}}
            <div class="login-field">
                <label for="password_confirmation" class="login-label">
                    Confirmer le mot de passe
                </label>
                <div class="login-password-wrap">
                    <input :type="showConfirmation ? 'text' : 'password'"
                           id="password_confirmation"
                           name="password_confirmation"
                           x-model="passwordConfirmation"
                           class="login-input"
                           placeholder="••••••••"
                           required>
                    <button type="button"
                            class="login-eye-btn"
                            @click="showConfirmation = !showConfirmation"
                            :aria-label="showConfirmation ? 'Masquer' : 'Afficher'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                             fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8"
                             style="width:20px;height:20px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Bouton --}}
            <button type="submit"
                    class="login-submit"
                    :disabled="loading">
                <span x-show="loading" class="login-submit__spinner"></span>
                <span x-text="loading ? 'Enregistrement...' : 'Réinitialiser le mot de passe'"></span>
            </button>
        </form>

        {{-- Retour --}}
        <p class="login-hint" style="text-align:center; margin-top: 1rem;">
            <a href="{{ route('login') }}" style="color: #27357e; font-weight: 600;">
                ← Retour à la connexion
            </a>
        </p>

        <p class="login-footer">
            © {{ date('Y') }} CNPS — Direction du Système d'Information
        </p>

    </div>
</div>

@endsection