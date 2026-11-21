@extends('layouts.guest')

@section('title', 'Affectation en attente')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

@php($user = current_user())

<div class="login">

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
        </div>
        <p class="login-brand__footer">© {{ date('Y') }} Caisse Nationale de Prévoyance Sociale · Côte d'Ivoire</p>
    </aside>

    {{-- ===== MESSAGE ===== --}}
    <main class="login-main">
        <div class="login-panel">

            <div class="login-status">
                <x-icon name="clock" size="lg" />
            </div>

            <header class="login-head">
                <h2 class="login-head__title">Affectation en attente</h2>
                <p class="login-head__text">
                    Bonjour {{ $user->prenom }}, votre compte a bien été créé.
                    Pour accéder aux demandes de ressources, l'administrateur de l'application doit d'abord
                    vous rattacher à votre service.
                </p>
            </header>

            <dl class="login-recap">
                <div><dt>Nom</dt><dd>{{ trim($user->prenom.' '.$user->nom) }}</dd></div>
                <div><dt>Matricule</dt><dd>{{ $user->matricule }}</dd></div>
                <div><dt>Adresse e-mail</dt><dd>{{ $user->email }}</dd></div>
            </dl>

            <a href="{{ route('affectation.attente') }}" class="login-submit">
                <x-icon name="refresh" size="sm" />
                Vérifier à nouveau
            </a>

            <form method="POST" action="{{ route('logout') }}" class="login-secondary">
                @csrf
                <button type="submit" class="login-link">
                    <x-icon name="logout" size="sm" /> Se déconnecter
                </button>
            </form>

            <div class="login-help">
                <x-icon name="info" size="sm" class="login-help__icon" />
                <p>
                    Communiquez votre <strong>matricule</strong> à l'administrateur de l'application si l'attente se prolonge.
                    Une fois l'affectation faite, cliquez sur « Vérifier à nouveau ».
                </p>
            </div>
        </div>

        <p class="login-main__footer">© {{ date('Y') }} CNPS — Direction du Système d'Information</p>
    </main>
</div>

@endsection
