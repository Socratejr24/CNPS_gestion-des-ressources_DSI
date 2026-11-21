@extends('layouts.app')

@php($creation = $application === null)

@section('title', $creation ? 'Nouvelle application' : $application->nom)
@section('page-title', 'Applications')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page adm-page--narrow">

    <a href="{{ route('admin.applications.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour aux applications
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">
                {{ $creation ? 'Nouvelle application' : 'Modifier l\'application' }}
                @if(! $creation && ! $application->is_active)<x-badge state="rejetee" size="sm">Désactivée</x-badge>@endif
            </h1>
            <p class="adm-subtitle">Une demande de ressources peut être rattachée à une application du référentiel (RG-12).</p>
        </div>
    </div>

    <form method="POST" class="adm-card adm-form"
          action="{{ $creation ? route('admin.applications.store') : route('admin.applications.update', $application) }}">
        @csrf
        @unless($creation) @method('PUT') @endunless

        <div class="adm-form__grid">
            <x-form.input name="nom" label="Nom" :value="$application?->nom" :error="$errors->first('nom')" required
                          placeholder="Ex. Système de gestion RH" />
            <x-form.input name="reference" label="Référence" :value="$application?->reference" :error="$errors->first('reference')" required
                          maxlength="50" placeholder="Ex. SIGRH" hint="Identifiant court et unique, mis en majuscules." />
        </div>
        <x-form.textarea name="description" label="Description" :value="$application?->description" :error="$errors->first('description')" rows="3" />

        @unless($creation)
            <p class="adm-form__section-text">Créée le {{ $application->created_at?->format('d/m/Y à H:i') }}.</p>
        @endunless

        <div class="adm-form__footer">
            <x-button :href="route('admin.applications.index')" variant="ghost">Annuler</x-button>
            <x-button type="submit" icon="check">{{ $creation ? 'Créer l\'application' : 'Enregistrer' }}</x-button>
        </div>
    </form>
</div>
@endsection
