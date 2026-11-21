@extends('layouts.app')

@php($creation = $structure === null)

@section('title', $creation ? 'Nouvelle structure' : $structure->libelle)
@section('page-title', 'Organisation')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page adm-page--narrow">

    <a href="{{ route('admin.organisation.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour à l'organisation
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">{{ $creation ? 'Nouvelle structure' : 'Modifier la structure' }}</h1>
            <p class="adm-subtitle">Niveau le plus haut de l'organisation (ex. DSI). Elle regroupe des départements.</p>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    <form method="POST" class="adm-card adm-form"
          action="{{ $creation ? route('admin.organisation.structures.store') : route('admin.organisation.structures.update', $structure) }}">
        @csrf
        @unless($creation) @method('PUT') @endunless

        <div class="adm-form__grid">
            <x-form.input name="code" label="Code" :value="$structure?->code" :error="$errors->first('code')" required
                          maxlength="30" hint="Court et unique, ex. DSI." />
            <x-form.input name="libelle" label="Libellé" :value="$structure?->libelle" :error="$errors->first('libelle')" required />
        </div>
        <x-form.textarea name="description" label="Description" :value="$structure?->description" :error="$errors->first('description')" rows="3" />

        <div class="adm-form__footer">
            <x-button :href="route('admin.organisation.index')" variant="ghost">Annuler</x-button>
            <x-button type="submit" icon="check">{{ $creation ? 'Créer la structure' : 'Enregistrer' }}</x-button>
        </div>
    </form>
</div>
@endsection
