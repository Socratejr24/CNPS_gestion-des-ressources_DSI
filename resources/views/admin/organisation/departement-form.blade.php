@extends('layouts.app')

@php($creation = $departement === null)

@section('title', $creation ? 'Nouveau département' : $departement->libelle)
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
                {{ $creation ? 'Nouveau département' : $departement->libelle }}
                @if(! $creation && ! $departement->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endif
            </h1>
            <p class="adm-subtitle">
                Un département appartient à une structure et regroupe des services. Son responsable est le N+2 des demandes.
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
                    <h2 class="adm-card__title"><x-icon name="layers" size="sm" /> Informations</h2>
                </header>
                <form method="POST" class="adm-card__body adm-form"
                      action="{{ $creation ? route('admin.organisation.departements.store') : route('admin.organisation.departements.update', $departement) }}">
                    @csrf
                    @unless($creation) @method('PUT') @endunless

                    @if($structures->isEmpty())
                        <p class="adm-note adm-note--warning">
                            <x-icon name="alert-triangle" size="sm" />
                            Aucune structure active : <a href="{{ route('admin.organisation.structures.create') }}">créez d'abord une structure</a>.
                        </p>
                    @endif

                    <x-form.select name="structure_id" label="Structure" required placeholder="— Choisir une structure —"
                                   :options="$structures->mapWithKeys(fn ($s) => [$s->id => $s->libelle.($s->is_active ? '' : ' (inactive)')])->all()"
                                   :selected="$departement?->structure_id ?? $structureParDefaut"
                                   :error="$errors->first('structure_id')" />
                    <div class="adm-form__grid">
                        <x-form.input name="code" label="Code" :value="$departement?->code" :error="$errors->first('code')" required
                                      maxlength="30" hint="Court et unique, ex. DEV." />
                        <x-form.input name="libelle" label="Libellé" :value="$departement?->libelle" :error="$errors->first('libelle')" required />
                    </div>
                    <x-form.textarea name="description" label="Description" :value="$departement?->description" :error="$errors->first('description')" rows="3" />

                    <div class="adm-form__footer">
                        <x-button :href="route('admin.organisation.index')" variant="ghost">{{ $creation ? 'Annuler' : 'Retour' }}</x-button>
                        <x-button type="submit" icon="check">{{ $creation ? 'Créer le département' : 'Enregistrer' }}</x-button>
                    </div>
                </form>
            </section>

            @unless($creation)
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="server" size="sm" /> Services du département</h2>
                    </header>
                    <div class="adm-card__body">
                        @if($departement->services->isEmpty())
                            <p class="adm-card__text">Aucun service pour le moment.</p>
                        @else
                            <ul class="org-link-list">
                                @foreach($departement->services as $service)
                                    <li>
                                        <a href="{{ route('admin.organisation.services.edit', $service) }}">{{ $service->libelle }}</a>
                                        <span class="org-code">{{ $service->code }}</span>
                                        @unless($service->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endunless
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if($departement->is_active)
                            <x-button :href="route('admin.organisation.services.create', ['departement_id' => $departement->id])"
                                      variant="outline" size="sm" icon="plus">Ajouter un service</x-button>
                        @endif
                    </div>
                </section>
            @endunless
        </div>

        @unless($creation)
            <aside class="adm-grid__side">
                <section class="adm-card">
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="check-circle" size="sm" /> Responsable (N+2)</h2>
                    </header>
                    <div class="adm-card__body">
                        @if($departement->responsable)
                            <p class="org-current">
                                <a href="{{ route('admin.utilisateurs.show', $departement->responsable) }}">{{ $departement->responsable->nomComplet() }}</a>
                                <span class="adm-cell-sub">{{ $departement->responsable->fonction }}</span>
                            </p>
                        @else
                            <p class="adm-note adm-note--warning">
                                <x-icon name="alert-triangle" size="sm" />
                                Aucun responsable : les demandes de ce département ne peuvent pas franchir l'étape N+2.
                            </p>
                        @endif

                        @if(! $departement->is_active)
                            <p class="form-hint">Réactivez le département pour désigner un responsable.</p>
                        @elseif($candidats->isEmpty())
                            <p class="form-hint">
                                Le responsable doit être affecté à un service de ce département.
                                Aucun utilisateur actif ne l'est encore.
                            </p>
                        @endif

                        @if($departement->is_active && ($candidats->isNotEmpty() || $departement->responsable))
                            <form method="POST" action="{{ route('admin.organisation.departements.responsable', $departement) }}" class="adm-inline-form">
                                @csrf
                                @method('PUT')
                                <label for="utilisateur_id" class="form-label">Désigner</label>
                                <select id="utilisateur_id" name="utilisateur_id" class="form-control">
                                    <option value="">— Aucun responsable —</option>
                                    @foreach($candidats as $candidat)
                                        <option value="{{ $candidat->id }}" @selected($departement->responsable_id === $candidat->id)>
                                            {{ $candidat->nomComplet() }} · {{ $candidat->service->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="form-hint">Seuls les utilisateurs actifs affectés à un service du département sont proposés.</p>
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
                            {{ $departement->is_active
                                ? 'Un département ne peut être désactivé que si tous ses services le sont.'
                                : 'Ce département est désactivé.' }}
                        </p>
                        <form method="POST" action="{{ route('admin.organisation.departements.statut', $departement) }}"
                              @if($departement->is_active) onsubmit="return confirm('Désactiver ce département ?')" @endif>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_active" value="{{ $departement->is_active ? 0 : 1 }}">
                            <x-button type="submit" size="sm" block
                                      :variant="$departement->is_active ? 'ghost-danger' : 'success'"
                                      :icon="$departement->is_active ? 'x-circle' : 'check-circle'">
                                {{ $departement->is_active ? 'Désactiver le département' : 'Réactiver le département' }}
                            </x-button>
                        </form>
                    </div>
                </section>
            </aside>
        @endunless
    </div>
</div>
@endsection
