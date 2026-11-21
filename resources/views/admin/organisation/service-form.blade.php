@extends('layouts.app')

@php($creation = $service === null)

@section('title', $creation ? 'Nouveau service' : $service->libelle)
@section('page-title', 'Organisation')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page">

    <a href="{{ route('admin.organisation.index') }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> Retour à l'organisation
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">
                {{ $creation ? 'Nouveau service' : $service->libelle }}
                @if(! $creation && ! $service->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endif
            </h1>
            <p class="adm-subtitle">
                @if($creation)
                    Un service appartient à un département. Les utilisateurs y sont affectés et son chef est le N+1 des demandes.
                @else
                    {{ $service->departement?->structure?->libelle }} · {{ $service->departement?->libelle }}
                @endif
            </p>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    <div class="{{ $creation ? 'adm-page--narrow' : 'adm-grid' }}">
        <div class="adm-grid__main">
            <section class="adm-card">
                <header class="adm-card__header">
                    <h2 class="adm-card__title"><x-icon name="server" size="sm" /> Informations</h2>
                </header>
                <form method="POST" class="adm-card__body adm-form"
                      action="{{ $creation ? route('admin.organisation.services.store') : route('admin.organisation.services.update', $service) }}">
                    @csrf
                    @unless($creation) @method('PUT') @endunless

                    @if($departements === [])
                        <p class="adm-note adm-note--warning">
                            <x-icon name="alert-triangle" size="sm" />
                            Aucun département actif : <a href="{{ route('admin.organisation.index') }}">créez d'abord un département</a>.
                        </p>
                    @endif

                    @php($departementCourant = old('departement_id', $service?->departement_id ?? $departementParDefaut))
                    <div class="form-group">
                        <label for="departement_id" class="form-label form-label--required">Département</label>
                        <select id="departement_id" name="departement_id" required
                                class="form-control {{ $errors->has('departement_id') ? 'form-control--error' : '' }}">
                            <option value="">— Choisir un département —</option>
                            @foreach($departements as $structure => $options)
                                <optgroup label="{{ $structure }}">
                                    @foreach($options as $id => $libelle)
                                        <option value="{{ $id }}" @selected((int) $departementCourant === $id)>{{ $libelle }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('departement_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="adm-form__grid">
                        <x-form.input name="code" label="Code" :value="$service?->code" :error="$errors->first('code')" required
                                      maxlength="30" hint="Court et unique, ex. DEV-IT." />
                        <x-form.input name="libelle" label="Libellé" :value="$service?->libelle" :error="$errors->first('libelle')" required />
                    </div>
                    <x-form.textarea name="description" label="Description" :value="$service?->description" :error="$errors->first('description')" rows="3" />

                    <div class="adm-form__footer">
                        <x-button :href="route('admin.organisation.index')" variant="ghost">{{ $creation ? 'Annuler' : 'Retour' }}</x-button>
                        <x-button type="submit" icon="check">{{ $creation ? 'Créer le service' : 'Enregistrer' }}</x-button>
                    </div>
                </form>
            </section>

            @unless($creation)
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="users" size="sm" /> Membres ({{ $membres->count() }})</h2>
                    </header>
                    <div class="adm-card__body">
                        @if($membres->isEmpty())
                            <p class="adm-card__text">
                                Aucun utilisateur affecté. L'affectation se fait depuis la
                                <a href="{{ route('admin.utilisateurs.index', ['affectation' => 'sans']) }}">fiche de chaque utilisateur</a>.
                            </p>
                        @else
                            <ul class="org-link-list">
                                @foreach($membres as $membre)
                                    <li>
                                        <a href="{{ route('admin.utilisateurs.show', $membre) }}">{{ $membre->nomComplet() }}</a>
                                        <span class="adm-cell-sub">{{ $membre->fonction }}</span>
                                        @if($service->chef_id === $membre->id)<span class="adm-role">N+1</span>@endif
                                        @unless($membre->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endunless
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>
            @endunless
        </div>

        @unless($creation)
            @php($candidats = $membres->where('is_active', true))
            <aside class="adm-grid__side">
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="check-circle" size="sm" /> Chef de service (N+1)</h2>
                    </header>
                    <div class="adm-card__body">
                        @if($service->chef)
                            <p class="org-current">
                                <a href="{{ route('admin.utilisateurs.show', $service->chef) }}">{{ $service->chef->nomComplet() }}</a>
                                <span class="adm-cell-sub">{{ $service->chef->fonction }}</span>
                            </p>
                        @else
                            <p class="adm-note adm-note--warning">
                                <x-icon name="alert-triangle" size="sm" />
                                Aucun chef : les demandes de ce service ne peuvent pas franchir l'étape N+1.
                            </p>
                        @endif

                        @if(! $service->is_active)
                            <p class="form-hint">Réactivez le service pour désigner un chef.</p>
                        @elseif($candidats->isEmpty())
                            <p class="form-hint">Le chef doit être affecté à ce service. Aucun utilisateur actif ne l'est encore.</p>
                        @endif

                        @if($service->is_active && ($candidats->isNotEmpty() || $service->chef))
                            <form method="POST" action="{{ route('admin.organisation.services.chef', $service) }}" class="adm-inline-form">
                                @csrf
                                @method('PUT')
                                <label for="utilisateur_id" class="form-label">Désigner</label>
                                <select id="utilisateur_id" name="utilisateur_id" class="form-control">
                                    <option value="">— Aucun chef —</option>
                                    @foreach($candidats as $candidat)
                                        <option value="{{ $candidat->id }}" @selected($service->chef_id === $candidat->id)>{{ $candidat->nomComplet() }}</option>
                                    @endforeach
                                </select>
                                <x-button type="submit" size="sm" icon="check" block>Enregistrer</x-button>
                            </form>
                        @endif
                    </div>
                </section>

                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="lock" size="sm" /> Statut</h2>
                    </header>
                    <div class="adm-card__body">
                        <p class="adm-card__text">
                            {{ $service->is_active
                                ? 'Un service ne peut être désactivé que si aucun utilisateur actif n\'y est affecté.'
                                : 'Ce service est désactivé.' }}
                        </p>
                        <form method="POST" action="{{ route('admin.organisation.services.statut', $service) }}"
                              @if($service->is_active) onsubmit="return confirm('Désactiver ce service ?')" @endif>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_active" value="{{ $service->is_active ? 0 : 1 }}">
                            <x-button type="submit" size="sm" block
                                      :variant="$service->is_active ? 'ghost-danger' : 'success'"
                                      :icon="$service->is_active ? 'x-circle' : 'check-circle'">
                                {{ $service->is_active ? 'Désactiver le service' : 'Réactiver le service' }}
                            </x-button>
                        </form>
                    </div>
                </section>
            </aside>
        @endunless
    </div>
</div>
@endsection
