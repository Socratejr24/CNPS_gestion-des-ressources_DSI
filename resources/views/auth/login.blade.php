@extends('layouts.guest')

@section('title', 'Connexion')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

<div class="login-bg" x-data="loginForm()">

    {{-- Blobs décoratifs --}}
    <span class="login-blob login-blob--orange"></span>
    <span class="login-blob login-blob--green"></span>
    <span class="login-blob login-blob--yellow"></span>

    {{-- Carte formulaire --}}
    <div class="login-card">

        {{-- Logo --}}
        <img src="{{ asset('images/logo-cnps.jpeg') }}"
             alt="CNPS"
             class="login-logo">

        <h1 class="login-title">Bienvenue</h1>
        <p class="login-subtitle">Connectez-vous pour gérer vos demandes de ressources</p>

        {{-- Sélecteur Agent / Prestataire --}}
        <div class="login-tabs">
            <button type="button"
                    @click="switchMode('agent')"
                    :class="mode === 'agent' ? 'login-tab--active' : ''"
                    class="login-tab">
                Agent CNPS
            </button>
            <button type="button"
                    @click="switchMode('prestataire')"
                    :class="mode === 'prestataire' ? 'login-tab--active' : ''"
                    class="login-tab">
                Prestataire
            </button>
        </div>

        {{-- Message d'erreur --}}
        <template x-if="errorMessage">
            <div class="login-error" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="errorMessage"></span>
            </div>
        </template>

        {{-- Formulaire --}}
        <form method="POST" action="#" @submit.prevent="submit" novalidate>

            @csrf

            {{-- Identifiant --}}
            <div class="login-field">
                <label for="identifiant"
                       class="login-label"
                       x-text="identifiantLabel"></label>
                <input type="text"
                       id="identifiant"
                       name="identifiant"
                       :type="identifiantType"
                       :placeholder="identifiantPlaceholder"
                       :class="errorMessage ? 'login-input login-input--error' : 'login-input'"
                       autocomplete="username"
                       required>
            </div>

            {{-- Mot de passe --}}
            <div class="login-field">
                <label for="password" class="login-label">Mot de passe</label>
                <div class="login-password-wrap">
                    <input :type="showPassword ? 'text' : 'password'"
                           id="password"
                           name="password"
                           :class="errorMessage ? 'login-input login-input--error' : 'login-input'"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>

                    <button type="button"
                            class="login-eye-btn"
                            @click="togglePassword()"
                            :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">

                        {{-- Œil ouvert --}}
                        <svg x-show="!showPassword"
                             xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>

                        {{-- Œil barré --}}
                        <svg x-show="showPassword"
                             xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mot de passe oublié (prestataires uniquement) --}}
            <template x-if="isPrestataire">
                <a href="#" class="login-forgot" @click.prevent>
                    Mot de passe oublié ?
                </a>
            </template>

            {{-- Indice --}}
            <p class="login-hint" x-text="hint"></p>

            {{-- Bouton submit --}}
            <button type="submit"
                    class="login-submit"
                    :disabled="loading">
                <span x-show="!loading">Se connecter</span>
                <span x-show="loading">Connexion en cours...</span>
            </button>

        </form>

        <p class="login-footer">
            © {{ date('Y') }} CNPS — Direction du Système d'Information
        </p>

    </div>
</div>

@endsection