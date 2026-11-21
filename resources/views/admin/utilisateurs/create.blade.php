@extends('layouts.app')

@section('title', 'Nouveau prestataire')
@section('page-title', 'Utilisateurs')

@push('styles')
    @vite('resources/css/admin/utilisateurs.css')
@endpush

@section('content')
<div class="adm-page adm-page--narrow">

    <a href="{{ route('admin.utilisateurs.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour aux utilisateurs
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Nouveau compte prestataire</h1>
            <p class="adm-subtitle">
                Compte local, avec identifiant et mot de passe, pour un intervenant externe (RG-08).
                Les agents CNPS n'ont pas besoin de compte ici : il est créé à leur première connexion LDAP.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.utilisateurs.store') }}" class="adm-card adm-form">
        @csrf
        @include('admin.utilisateurs.partials.champs-prestataire', ['utilisateur' => null])

        <div class="adm-form__footer">
            <x-button :href="route('admin.utilisateurs.index')" variant="ghost">Annuler</x-button>
            <x-button type="submit" icon="check">Créer le compte</x-button>
        </div>
    </form>
</div>
@endsection
