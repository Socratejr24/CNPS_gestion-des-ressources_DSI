@extends('layouts.app')

@php
    $creation = $permission === null;
    $systeme = ! $creation && $permission->estSysteme();
@endphp

@section('title', $creation ? 'Nouvelle permission' : $permission->libelle)
@section('page-title', 'Rôles et permissions')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page adm-page--narrow">

    <a href="{{ route('admin.roles.index', ['onglet' => 'permissions']) }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour aux permissions
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">
                {{ $creation ? 'Nouvelle permission' : 'Modifier la permission' }}
                @if($systeme)<span class="adm-role">Système</span>@endif
            </h1>
            <p class="adm-subtitle">
                Le slug est l'identifiant technique utilisé par l'application pour vérifier un droit (ex. <code>voir.traitement</code>).
            </p>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    <form method="POST" class="adm-card adm-form"
          action="{{ $creation ? route('admin.roles.permissions.store') : route('admin.roles.permissions.update', $permission) }}">
        @csrf
        @unless($creation) @method('PUT') @endunless

        <div class="adm-form__grid">
            <x-form.input name="libelle" label="Libellé" :value="$permission?->libelle" :error="$errors->first('libelle')" required
                          placeholder="Ex. Voir la section Traitement" />
            @if($systeme)
                <input type="hidden" name="slug" value="{{ $permission->slug }}">
                <x-form.input name="slug_affiche" label="Slug" :value="$permission->slug" disabled hint="Figé : utilisé par l'application." />
            @else
                <x-form.input name="slug" label="Slug" :value="$permission?->slug" :error="$errors->first('slug')" required
                              placeholder="ex. demandes.valider" hint="Minuscules, chiffres, points, tirets ou soulignés." />
            @endif
        </div>
        <x-form.textarea name="description" label="Description" :value="$permission?->description" :error="$errors->first('description')" rows="3" />

        @unless($creation)
            <div class="adm-form__section">
                <h3 class="adm-form__section-title">Rôles qui accordent cette permission</h3>
                @if($permission->roles->isEmpty())
                    <p class="adm-form__section-text">Aucun. L'attribution se fait depuis la page de chaque rôle.</p>
                @else
                    <div class="adm-roles">
                        @foreach($permission->roles as $role)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="adm-role">{{ $role->libelle }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endunless

        <div class="adm-form__footer">
            <x-button :href="route('admin.roles.index', ['onglet' => 'permissions'])" variant="ghost">Annuler</x-button>
            <x-button type="submit" icon="check">{{ $creation ? 'Créer la permission' : 'Enregistrer' }}</x-button>
        </div>
    </form>

    @if(! $creation && ! $systeme)
        <section class="adm-card">
            <header class="adm-card__header">
                <h2 class="adm-card__title"><x-icon name="trash" size="sm" /> Suppression</h2>
            </header>
            <div class="adm-card__body adm-danger-row">
                <p class="adm-card__text">La permission sera retirée de tous les rôles qui l'accordent.</p>
                <form method="POST" action="{{ route('admin.roles.permissions.destroy', $permission) }}"
                      onsubmit="return confirm(@js('Supprimer la permission « '.$permission->libelle.' » ?'))">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm" icon="trash">Supprimer</x-button>
                </form>
            </div>
        </section>
    @endif
</div>
@endsection
