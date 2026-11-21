@extends('layouts.app')

@php
    $creation = $role === null;
    $systeme = ! $creation && $role->estSysteme();
    $actuelles = $creation ? [] : $role->permissions->pluck('id')->all();
@endphp

@section('title', $creation ? 'Nouveau rôle' : $role->libelle)
@section('page-title', 'Rôles et permissions')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page">

    <a href="{{ route('admin.roles.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour aux rôles
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">
                {{ $creation ? 'Nouveau rôle' : $role->libelle }}
                @if($systeme)<span class="adm-role">Système</span>@endif
                @if(! $creation && ! $role->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endif
            </h1>
            <p class="adm-subtitle">
                @if($creation)
                    Un rôle regroupe des permissions et s'attribue aux utilisateurs depuis leur fiche.
                @else
                    Attribué à {{ $role->utilisateurs_count }} utilisateur(s).
                @endif
            </p>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    <div class="{{ $creation ? 'adm-page--narrow' : 'adm-grid' }}">
        <div class="adm-grid__main">

            {{-- Informations --}}
            <section class="adm-card">
                <header class="adm-card__header">
                    <h2 class="adm-card__title"><x-icon name="shield" size="sm" /> Informations</h2>
                </header>
                <form method="POST" class="adm-card__body adm-form"
                      action="{{ $creation ? route('admin.roles.store') : route('admin.roles.update', $role) }}">
                    @csrf
                    @unless($creation) @method('PUT') @endunless

                    @if($systeme)
                        <p class="adm-note">
                            <x-icon name="info" size="sm" />
                            Rôle utilisé par l'application : son libellé est figé, seule la description est modifiable.
                        </p>
                        <input type="hidden" name="libelle" value="{{ $role->libelle }}">
                        <x-form.input name="libelle_affiche" label="Libellé" :value="$role->libelle" disabled />
                    @else
                        <x-form.input name="libelle" label="Libellé" :value="$role?->libelle" :error="$errors->first('libelle')" required
                                      placeholder="Ex. Gestionnaire de parc" />
                    @endif
                    <x-form.textarea name="description" label="Description" :value="$role?->description" :error="$errors->first('description')" rows="3" />

                    <div class="adm-form__footer">
                        <x-button :href="route('admin.roles.index')" variant="ghost">{{ $creation ? 'Annuler' : 'Retour' }}</x-button>
                        <x-button type="submit" icon="check">{{ $creation ? 'Créer le rôle' : 'Enregistrer' }}</x-button>
                    </div>
                </form>
            </section>

            {{-- Permissions --}}
            @unless($creation)
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="lock" size="sm" /> Permissions ({{ count($actuelles) }})</h2>
                    </header>
                    <form method="POST" action="{{ route('admin.roles.permissions.sync', $role) }}" class="adm-card__body">
                        @csrf
                        @method('PUT')
                        @if($permissions->isEmpty())
                            <p class="adm-card__text">
                                Aucune permission n'existe encore.
                                <a href="{{ route('admin.roles.permissions.create') }}">Créer une permission</a>.
                            </p>
                        @else
                            <p class="adm-card__text">Cochez les permissions accordées par ce rôle. Une permission désactivée n'a aucun effet.</p>
                            <div class="adm-role-list">
                                @foreach($permissions as $permission)
                                    <label class="adm-role-option {{ $permission->is_active ? '' : 'adm-role-option--off' }}">
                                        <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                               @checked(in_array($permission->id, old('permission_ids', $actuelles)))>
                                        <span>
                                            <strong>{{ $permission->libelle }}</strong>
                                            <span class="org-code">{{ $permission->slug }}</span>
                                            @unless($permission->is_active)<em>(désactivée)</em>@endunless
                                            @if($permission->description)<small>{{ $permission->description }}</small>@endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="adm-form__footer">
                                <x-button type="submit" icon="check">Enregistrer les permissions</x-button>
                            </div>
                        @endif
                    </form>
                </section>
            @endunless
        </div>

        {{-- Statut et suppression --}}
        @unless($creation)
            <aside class="adm-grid__side">
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="settings" size="sm" /> Statut</h2>
                    </header>
                    <div class="adm-card__body">
                        @if($systeme)
                            <p class="adm-card__text">Rôle utilisé par l'application : il ne peut être ni désactivé, ni supprimé.</p>
                        @else
                            <p class="adm-card__text">
                                {{ $role->is_active
                                    ? 'Un rôle désactivé reste attribué mais n\'accorde plus aucun droit.'
                                    : 'Ce rôle est désactivé : il n\'accorde aucun droit.' }}
                            </p>
                            <form method="POST" action="{{ route('admin.roles.statut', $role) }}"
                                  @if($role->is_active) onsubmit="return confirm('Désactiver ce rôle ?')" @endif>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $role->is_active ? 0 : 1 }}">
                                <x-button type="submit" size="sm" block
                                          :variant="$role->is_active ? 'ghost-danger' : 'success'"
                                          :icon="$role->is_active ? 'x-circle' : 'check-circle'">
                                    {{ $role->is_active ? 'Désactiver le rôle' : 'Réactiver le rôle' }}
                                </x-button>
                            </form>
                        @endif
                    </div>
                </section>

                @unless($systeme)
                    <section class="adm-card">
                        <header class="adm-card__header">
                            <h2 class="adm-card__title"><x-icon name="trash" size="sm" /> Suppression</h2>
                        </header>
                        <div class="adm-card__body">
                            @if($role->utilisateurs_count > 0)
                                <p class="adm-card__text">
                                    Ce rôle est attribué à {{ $role->utilisateurs_count }} utilisateur(s) : retirez-le d'abord de leur fiche, ou désactivez-le.
                                </p>
                            @else
                                <p class="adm-card__text">Le rôle n'est attribué à personne : il peut être supprimé.</p>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                      onsubmit="return confirm(@js('Supprimer définitivement le rôle « '.$role->libelle.' » ?'))">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm" icon="trash" block>Supprimer le rôle</x-button>
                                </form>
                            @endif
                        </div>
                    </section>
                @endunless
            </aside>
        @endunless
    </div>
</div>
@endsection
