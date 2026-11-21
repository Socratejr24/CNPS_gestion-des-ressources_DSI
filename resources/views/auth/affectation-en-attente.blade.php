@extends('layouts.guest')

@section('title', 'Affectation en attente')

@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

@section('content')

@php($user = current_user())

<div class="login-bg">

    <span class="login-blob login-blob--orange"></span>
    <span class="login-blob login-blob--green"></span>
    <span class="login-blob login-blob--yellow"></span>

    <div class="login-card">

        <img src="{{ asset('images/logo-cnps.jpeg') }}" alt="CNPS" class="login-logo">

        <h1 class="login-title">Affectation en attente</h1>

        <p class="login-subtitle">
            Bonjour {{ $user->prenom }}, votre compte a bien été créé.
        </p>

        <p class="login-hint">
            Pour accéder aux demandes de ressources, l'administrateur de l'application
            doit d'abord vous rattacher à votre service. Vous serez notifié dès que
            votre affectation sera effective ; il vous suffira alors de recharger cette page.
        </p>

        <div class="login-field">
            <span class="login-label">Matricule</span>
            <p>{{ $user->matricule }}</p>
        </div>

        <div class="login-field">
            <span class="login-label">Adresse e-mail</span>
            <p>{{ $user->email }}</p>
        </div>

        <a href="{{ route('affectation.attente') }}" class="login-submit">
            Vérifier à nouveau
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="login-forgot">Se déconnecter</button>
        </form>

        <p class="login-footer">
            © {{ date('Y') }} CNPS — Direction du Système d'Information
        </p>

    </div>
</div>

@endsection
