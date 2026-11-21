@extends('layouts.guest')

@section('title', 'Connexion')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

<div class="login" x-data="loginForm()" x-init="init(@js($errors->first('identifiant') ?: ''))">

    {{-- ===== PANNEAU INSTITUTIONNEL ===== --}}
    <aside class="login-brand">
        <div class="login-brand__top">
            <div class="login-brand__logo">
                <img src="{{ asset('images/logo-cnps.jpeg') }}" alt="CNPS — Caisse Nationale de Prévoyance Sociale">
            </div>
        </div>

        <div class="login-brand__body">
            <p class="login-brand__eyebrow">Direction du Système d'Information</p>
            <h1 class="login-brand__title">Gestion des ressources DSI</h1>
            <p class="login-brand__lead">
                Demandez, validez et suivez les ressources informatiques de la CNPS depuis un seul espace.
            </p>

            <ul class="login-brand__features">
                <li>
                    <span class="login-brand__icon"><x-icon name="file-text" size="sm" /></span>
                    <span><strong>Demandes en ligne</strong>Accès applicatifs, matériel, comptes : plus de formulaires papier.</span>
                </li>
                <li>
                    <span class="login-brand__icon"><x-icon name="check-circle" size="sm" /></span>
                    <span><strong>Circuit de validation</strong>Validation par le chef de service (N+1) puis le responsable de département (N+2).</span>
                </li>
                <li>
                    <span class="login-brand__icon"><x-icon name="clock" size="sm" /></span>
                    <span><strong>Suivi en temps réel</strong>Chaque étape notifiée, jusqu'à la confirmation de réception.</span>
                </li>
            </ul>
        </div>

        <p class="login-brand__footer">© {{ date('Y') }} Caisse Nationale de Prévoyance Sociale · Côte d'Ivoire</p>
    </aside>

    {{-- ===== FORMULAIRE ===== --}}
    <main class="login-main">
        <div class="login-panel">

            <header class="login-head">
                <h2 class="login-head__title">Connexion</h2>
                <p class="login-head__text">Choisissez votre type de compte puis saisissez vos identifiants.</p>
            </header>

            {{-- Type de compte --}}
            <div class="login-switch" role="tablist" aria-label="Type de compte">
                <button type="button" role="tab" class="login-switch__btn"
                        :class="mode === 'agent' && 'login-switch__btn--active'"
                        :aria-selected="mode === 'agent'"
                        @click="switchMode('agent')">
                    <x-icon name="building" size="sm" />
                    Agent CNPS
                </button>
                <button type="button" role="tab" class="login-switch__btn"
                        :class="mode === 'prestataire' && 'login-switch__btn--active'"
                        :aria-selected="mode === 'prestataire'"
                        @click="switchMode('prestataire')">
                    <x-icon name="users" size="sm" />
                    Prestataire
                </button>
            </div>

            {{-- Erreur --}}
            <div class="login-alert" role="alert" x-show="errorMessage" x-cloak x-transition.opacity>
                <x-icon name="alert-circle" size="sm" />
                <span x-text="errorMessage"></span>
            </div>

            <form method="POST" action="{{ route('login.store') }}" @submit.prevent="submit" novalidate class="login-form">
                @csrf
                <input type="hidden" name="mode" :value="mode">

                {{-- Identifiant --}}
                <div class="login-field">
                    <label for="identifiant" class="login-field__label" x-text="identifiantLabel">Matricule</label>
                    <div class="login-field__control" :class="errorMessage && 'login-field__control--error'">
                        <x-icon name="user" size="sm" class="login-field__icon" />
                        <input type="text" :type="identifiantType" id="identifiant" name="identifiant" x-ref="identifiant"
                               :placeholder="identifiantPlaceholder" placeholder="Votre matricule"
                               autocomplete="username" autocapitalize="none" spellcheck="false" autofocus required
                               @input="errorMessage = ''">
                    </div>
                </div>

                {{-- Mot de passe --}}
                <div class="login-field">
                    <label for="password" class="login-field__label">Mot de passe</label>
                    <div class="login-field__control" :class="errorMessage && 'login-field__control--error'">
                        <x-icon name="lock" size="sm" class="login-field__icon" />
                        <input :type="showPassword ? 'text' : 'password'" type="password" id="password" name="password"
                               placeholder="Votre mot de passe" autocomplete="current-password" required
                               @input="errorMessage = ''"
                               @keyup="capsLock = $event.getModifierState && $event.getModifierState('CapsLock')"
                               @blur="capsLock = false">
                        <button type="button" class="login-field__toggle" @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                :title="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                            <span x-show="!showPassword"><x-icon name="eye" size="sm" /></span>
                            <span x-show="showPassword" x-cloak><x-icon name="eye-off" size="sm" /></span>
                        </button>
                    </div>
                    <p class="login-field__warn" x-show="capsLock" x-cloak>
                        <x-icon name="alert-triangle" size="xs" /> La touche Verr. Maj est activée.
                    </p>
                </div>

                <button type="submit" class="login-submit" :disabled="loading">
                    <span class="login-submit__spinner" x-show="loading" x-cloak></span>
                    <span x-text="loading ? 'Connexion en cours…' : 'Se connecter'">Se connecter</span>
                    <span x-show="!loading"><x-icon name="arrow-right" size="sm" /></span>
                </button>
            </form>

            {{-- Aide contextuelle --}}
            <div class="login-help">
                <x-icon name="info" size="sm" class="login-help__icon" />
                <p x-show="mode === 'agent'">
                    Utilisez votre <strong>matricule</strong> et votre mot de passe habituel de l'annuaire CNPS.
                    Votre compte est créé automatiquement à la première connexion.
                </p>
                <p x-show="mode === 'prestataire'" x-cloak>
                    Utilisez l'<strong>adresse e-mail</strong> enregistrée par la DSI et le mot de passe qui vous a été transmis.
                    <strong>Mot de passe oublié ?</strong> Contactez l'administrateur de l'application pour le réinitialiser.
                </p>
            </div>
        </div>

        <p class="login-main__footer">© {{ date('Y') }} CNPS — Direction du Système d'Information</p>
    </main>
</div>

@endsection
