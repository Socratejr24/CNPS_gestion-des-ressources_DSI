@extends('layouts.app')

@php($creation = $ressource === null)

@section('title', $creation ? 'Nouvelle ressource' : $ressource->designation)
@section('page-title', 'Ressources')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page adm-page--narrow">

    <a href="{{ route('admin.ressources.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour aux ressources
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">
                {{ $creation ? 'Nouvelle ressource' : 'Modifier la ressource' }}
                @if(! $creation && ! $ressource->is_active)<x-badge state="rejetee" size="sm">Désactivée</x-badge>@endif
            </h1>
            <p class="adm-subtitle">Les ressources actives sont proposées aux demandeurs lors de la création d'une demande.</p>
        </div>
    </div>

    <form method="POST" class="adm-card adm-form"
          action="{{ $creation ? route('admin.ressources.store') : route('admin.ressources.update', $ressource) }}">
        @csrf
        @unless($creation) @method('PUT') @endunless

        <x-form.input name="designation" label="Désignation" :value="$ressource?->designation" :error="$errors->first('designation')" required
                      placeholder="Ex. Ordinateur portable" />
        <div class="adm-form__grid">
            <x-form.input name="categorie" label="Catégorie" :value="$ressource?->categorie" :error="$errors->first('categorie')" required
                          list="categories-existantes" placeholder="Ex. Matériel"
                          hint="Choisissez une catégorie existante ou saisissez-en une nouvelle." />
            <x-form.input name="reference" label="Référence" :value="$ressource?->reference" :error="$errors->first('reference')" required
                          maxlength="50" placeholder="Ex. MAT-PC-PORT" hint="Identifiant court et unique, mis en majuscules." />
        </div>
        <datalist id="categories-existantes">
            @foreach($categories as $categorie)
                <option value="{{ $categorie }}"></option>
            @endforeach
        </datalist>
        <x-form.textarea name="description" label="Description" :value="$ressource?->description" :error="$errors->first('description')" rows="3" />
        <x-form.textarea name="informations_complementaires" label="Informations complémentaires" :value="$ressource?->informations_complementaires"
                         :error="$errors->first('informations_complementaires')" rows="3"
                         hint="Caractéristiques, conditions d'attribution, délai indicatif…" />

        <div class="adm-form__footer">
            <x-button :href="route('admin.ressources.index')" variant="ghost">Annuler</x-button>
            <x-button type="submit" icon="check">{{ $creation ? 'Créer la ressource' : 'Enregistrer' }}</x-button>
        </div>
    </form>
</div>
@endsection
