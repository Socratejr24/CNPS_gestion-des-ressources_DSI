@extends('layouts.guest')

@section('title', 'Mot de passe oublié')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

<div class="login-bg" x-data="forgotPasswordForm()">

    {{-- Blobs décoratifs --}}
    <span class="login-blob login-blob--orange"></span>
    <span class="login-blob login-blob--green"></span>
    <span class="login-blob login-blob--yellow"></span>

    <div class="login-card">

        <img src="{{ asset('images/logo-cnps.jpeg') }}"
             alt="CNPS"
             class="login-logo">

        <h1 class="login-title">Mot de passe oublié</h1>
        <p class="login-subtitle">
            Saisissez votre email pour recevoir un code de vérification
        </p>

        {{-- Erreur --}}
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
            <div class="login-field">
                <label for="email" class="login-label">Adresse email</label>
                <input type="email"
                       id="email"
                       name="email"
                       x-model="email"
                       class="login-input"
                       placeholder="votre.email@cnps.ci"
                       required>
            </div>

            <button type="submit"
                    class="login-submit"
                    :disabled="loading">
                <span x-show="loading" class="login-submit__spinner"></span>
                <span x-text="loading ? 'Envoi en cours...' : 'Recevoir le code'"></span>
            </button>
        </form>

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