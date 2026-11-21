@extends('layouts.app')

@section('title', $utilisateur->nomComplet())
@section('page-title', 'Utilisateurs')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@php
    use App\Enums\CategorieUtilisateur;
    $estAgent = $utilisateur->categorie === CategorieUtilisateur::AGENT;
    $rolesActuels = $utilisateur->roles->pluck('id')->all();
    $aDesResponsabilites = $responsabilites['services']->isNotEmpty() || $responsabilites['departements']->isNotEmpty();
@endphp

@section('content')
<div class="adm-page">

    <a href="{{ route('admin.utilisateurs.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour aux utilisateurs
    </a>

    {{-- ===== EN-TÊTE ===== --}}
    <div class="adm-profile">
        <span class="adm-avatar adm-avatar--lg">{{ mb_strtoupper(mb_substr($utilisateur->prenom, 0, 1).mb_substr($utilisateur->nom, 0, 1)) }}</span>
        <div class="adm-profile__info">
            <h1 class="adm-title">
                {{ $utilisateur->nomComplet() }}
                @if($estMoi)<span class="adm-cell-sub">(vous)</span>@endif
            </h1>
            <div class="adm-profile__meta">
                <span class="adm-tag {{ $estAgent ? 'adm-tag--agent' : 'adm-tag--presta' }}">{{ $utilisateur->categorie->label() }}</span>
                @if($utilisateur->is_active)
                    <x-badge state="validee" size="sm">Actif</x-badge>
                @else
                    <x-badge state="rejetee" size="sm">Désactivé</x-badge>
                @endif
                <span>{{ $utilisateur->email }}</span>
                @if($utilisateur->fonction)<span>· {{ $utilisateur->fonction }}</span>@endif
            </div>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror
    @if($errors->hasAny(['service_id', 'role_ids', 'role_ids.*', 'is_active']))
        <x-alert type="error" closable>{{ $errors->first('service_id') ?: $errors->first('role_ids') ?: $errors->first('role_ids.*') ?: $errors->first('is_active') }}</x-alert>
    @endif

    <div class="adm-grid">

        {{-- ===== COLONNE PRINCIPALE ===== --}}
        <div class="adm-grid__main">

            {{-- Informations --}}
            <section class="adm-card">
                <header class="adm-card__header">
                    <h2 class="adm-card__title"><x-icon name="user" size="sm" /> Informations</h2>
                </header>

                @if($estAgent)
                    <div class="adm-card__body">
                        <p class="adm-note">
                            <x-icon name="info" size="sm" />
                            Ces informations proviennent de l'annuaire LDAP de la CNPS et sont mises à jour à chaque connexion de l'agent.
                        </p>
                        <dl class="adm-dl">
                            <div><dt>Matricule</dt><dd>{{ $utilisateur->matricule ?? '—' }}</dd></div>
                            <div><dt>Identifiant</dt><dd>{{ $utilisateur->identifiant ?? '—' }}</dd></div>
                            <div><dt>Prénom</dt><dd>{{ $utilisateur->prenom }}</dd></div>
                            <div><dt>Nom</dt><dd>{{ $utilisateur->nom }}</dd></div>
                            <div><dt>E-mail</dt><dd>{{ $utilisateur->email }}</dd></div>
                            <div><dt>Fonction</dt><dd>{{ $utilisateur->fonction ?? '—' }}</dd></div>
                        </dl>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.utilisateurs.update', $utilisateur) }}" class="adm-card__body adm-form">
                        @csrf
                        @method('PUT')
                        @include('admin.utilisateurs.partials.champs-prestataire')
                        <div class="adm-form__footer">
                            <x-button type="submit" icon="check">Enregistrer</x-button>
                        </div>
                    </form>
                @endif
            </section>

            {{-- Rôles --}}
            <section class="adm-card">
                <header class="adm-card__header">
                    <h2 class="adm-card__title"><x-icon name="shield" size="sm" /> Rôles fonctionnels</h2>
                </header>
                <form method="POST" action="{{ route('admin.utilisateurs.roles', $utilisateur) }}" class="adm-card__body">
                    @csrf
                    @method('PUT')
                    <p class="adm-card__text">
                        Les responsabilités de validation (N+1 chef de service, N+2 responsable de département) ne sont pas des rôles :
                        elles se désignent depuis l'organisation.
                    </p>
                    <div class="adm-role-list">
                        @foreach($roles as $role)
                            @php($verrou = $estMoi && $role->libelle === \App\Models\Role::ADMINISTRATEUR)
                            <label class="adm-role-option {{ $role->is_active ? '' : 'adm-role-option--off' }}">
                                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                       @checked(in_array($role->id, old('role_ids', $rolesActuels)))
                                       @disabled(! $role->is_active || $verrou)>
                                @if($verrou)
                                    {{-- case désactivée = non envoyée : on la renvoie en caché --}}
                                    <input type="hidden" name="role_ids[]" value="{{ $role->id }}">
                                @endif
                                <span>
                                    <strong>{{ $role->libelle }}</strong>
                                    @unless($role->is_active)<em>(rôle désactivé)</em>@endunless
                                    @if($verrou)<em>(vous ne pouvez pas vous le retirer)</em>@endif
                                    @if($role->description)<small>{{ $role->description }}</small>@endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="adm-form__footer">
                        <x-button type="submit" icon="check">Enregistrer les rôles</x-button>
                    </div>
                </form>
            </section>
        </div>

        {{-- ===== COLONNE LATÉRALE ===== --}}
        <aside class="adm-grid__side">

            {{-- Affectation --}}
            <section class="adm-card">
                <header class="adm-card__header">
                    <h2 class="adm-card__title"><x-icon name="building" size="sm" /> Affectation</h2>
                </header>
                <div class="adm-card__body">
                    @if($utilisateur->service)
                        <dl class="adm-dl adm-dl--stack">
                            <div><dt>Structure</dt><dd>{{ $utilisateur->service->departement?->structure?->libelle ?? '—' }}</dd></div>
                            <div><dt>Département</dt><dd>{{ $utilisateur->service->departement?->libelle ?? '—' }}</dd></div>
                            <div><dt>Service</dt><dd>{{ $utilisateur->service->libelle }}</dd></div>
                        </dl>
                    @else
                        <p class="adm-note adm-note--warning">
                            <x-icon name="alert-triangle" size="sm" />
                            Aucune affectation : l'utilisateur ne peut pas encore accéder aux fonctionnalités métier (RG-11).
                        </p>
                    @endif

                    <form method="POST" action="{{ route('admin.utilisateurs.affectation', $utilisateur) }}" class="adm-inline-form">
                        @csrf
                        @method('PUT')
                        <label for="service_id" class="form-label">{{ $utilisateur->service ? 'Changer de service' : 'Affecter à un service' }}</label>
                        <select id="service_id" name="service_id" class="form-control">
                            <option value="">— Aucune affectation —</option>
                            @foreach($services as $groupe => $options)
                                <optgroup label="{{ $groupe }}">
                                    @foreach($options as $id => $libelle)
                                        <option value="{{ $id }}" @selected((int) old('service_id', $utilisateur->service_id) === $id)>{{ $libelle }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @if($services === [])
                            <p class="form-hint">Aucun service actif : créez d'abord l'organisation.</p>
                        @endif
                        <x-button type="submit" size="sm" icon="check" block>Enregistrer l'affectation</x-button>
                    </form>
                </div>
            </section>

            {{-- Responsabilités --}}
            @if($aDesResponsabilites)
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="check-circle" size="sm" /> Responsabilités</h2>
                    </header>
                    <ul class="adm-card__body adm-list">
                        @foreach($responsabilites['services'] as $libelle)
                            <li>Chef du service <strong>{{ $libelle }}</strong> (N+1)</li>
                        @endforeach
                        @foreach($responsabilites['departements'] as $libelle)
                            <li>Responsable du département <strong>{{ $libelle }}</strong> (N+2)</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Statut --}}
            <section class="adm-card" x-data>
                <header class="adm-card__header">
                    <h2 class="adm-card__title"><x-icon name="lock" size="sm" /> Statut du compte</h2>
                </header>
                <div class="adm-card__body">
                    @if($utilisateur->is_active)
                        <p class="adm-card__text">
                            Un compte désactivé ne peut plus se connecter. Son historique est conservé.
                        </p>
                        @if($estMoi)
                            <p class="form-hint">Vous ne pouvez pas désactiver votre propre compte.</p>
                        @else
                            <x-button variant="ghost-danger" size="sm" icon="x-circle" block
                                      @click="$dispatch('open-modal-desactivation')">Désactiver le compte</x-button>
                        @endif
                    @else
                        <p class="adm-card__text">Ce compte est désactivé : l'utilisateur ne peut pas se connecter.</p>
                        <form method="POST" action="{{ route('admin.utilisateurs.statut', $utilisateur) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_active" value="1">
                            <x-button type="submit" variant="success" size="sm" icon="check-circle" block>Réactiver le compte</x-button>
                        </form>
                    @endif
                </div>
            </section>
        </aside>
    </div>

    {{-- Confirmation de désactivation --}}
    @if($utilisateur->is_active && ! $estMoi)
        <x-modal id="modal-desactivation" title="Désactiver ce compte ?" size="sm">
            <p>
                <strong>{{ $utilisateur->nomComplet() }}</strong> ne pourra plus se connecter à l'application.
                Vous pourrez réactiver le compte à tout moment.
            </p>
            @if($aDesResponsabilites)
                <p class="adm-note adm-note--warning">
                    <x-icon name="alert-triangle" size="sm" />
                    Cet utilisateur est validateur : désignez d'abord un remplaçant, sinon la désactivation sera refusée.
                </p>
            @endif
            <x-slot:footer>
                <x-button variant="ghost" @click="$dispatch('close-modal-desactivation')">Annuler</x-button>
                <form method="POST" action="{{ route('admin.utilisateurs.statut', $utilisateur) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="0">
                    <x-button type="submit" variant="danger" icon="x-circle">Désactiver</x-button>
                </form>
            </x-slot:footer>
        </x-modal>
    @endif
</div>
@endsection
