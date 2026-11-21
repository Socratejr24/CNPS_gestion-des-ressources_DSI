@extends('layouts.guest')

@section('title', 'Vérification du code')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

<div class="login-bg" x-data="verifyOtpForm()">

    {{-- Blobs décoratifs --}}
    <span class="login-blob login-blob--orange"></span>
    <span class="login-blob login-blob--green"></span>
    <span class="login-blob login-blob--yellow"></span>

    <div class="login-card">

        <img src="{{ asset('images/logo-cnps.jpeg') }}"
             alt="CNPS"
             class="login-logo">

        <h1 class="login-title">Vérification du code</h1>
        <p class="login-subtitle">
            Saisissez le code à 6 chiffres reçu par email
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

        {{-- Info email --}}
        <p class="login-hint" style="text-align:center; margin-bottom: 1rem;">
            Code envoyé à <strong x-text="email"></strong>
        </p>

        <form @submit.prevent="submit">

            {{-- Champ OTP 6 chiffres --}}
            <div class="login-field">
                <label for="otp" class="login-label">Code de vérification</label>
                <input type="text"
                       id="otp"
                       name="otp"
                       x-model="otp"
                       class="login-input"
                       placeholder="123456"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       maxlength="6"
                       minlength="6"
                       autocomplete="one-time-code"
                       style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: 700;"
                       required>
            </div>

            {{-- Bouton --}}
            <button type="submit"
                    class="login-submit"
                    :disabled="loading">
                <span x-show="loading" class="login-submit__spinner"></span>
                <span x-text="loading ? 'Vérification...' : 'Vérifier le code'"></span>
            </button>
        </form>

        {{-- Renvoyer le code --}}
        <p class="login-hint" style="text-align:center; margin-top: 1rem;">
            Code non reçu ?
            <a href="#" @click.prevent="resendCode()" style="color: #27357e; font-weight: 600;">
                Renvoyer le code
            </a>
        </p>

        {{-- Retour --}}
        <p class="login-hint" style="text-align:center; margin-top: 0.5rem;">
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