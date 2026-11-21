@extends('layouts.app')

@php
    use App\Enums\TypeDestination;

    $creation = $demande === null;
    $lignesInitiales = old('lignes', $creation
        ? []
        : $demande->lignes->map(fn ($l) => ['ressource_id' => $l->ressource_id, 'quantite' => $l->quantite_demandee])->all());
    if ($lignesInitiales === []) {
        $lignesInitiales = [['ressource_id' => '', 'quantite' => 1]];
    }
    $typeInitial = old('destination_type', $demande?->destination_type?->value ?? '');
    $icones = [TypeDestination::DEPARTEMENT->value => 'layers', TypeDestination::SERVICE->value => 'server', TypeDestination::UTILISATEUR->value => 'user'];
@endphp

@section('title', $creation ? 'Nouvelle demande' : 'Modifier '.$demande->reference)
@section('page-title', $creation ? 'Nouvelle demande' : 'Mes demandes')

@push('styles')
    @vite(['resources/css/admin/index.css', 'resources/css/demandes/index.css'])
@endpush

@section('content')
<div class="adm-page">

    <a href="{{ $creation ? route('demandes.index') : route('demandes.show', $demande) }}" class="adm-back">
        <x-icon name="arrow-left" size="sm" /> {{ $creation ? 'Retour à mes demandes' : 'Retour à la demande' }}
    </a>

    <div class="adm-header">
        <div>
            <h1 class="adm-title">
                {{ $creation ? 'Nouvelle demande de ressources' : 'Modifier le brouillon' }}
                @unless($creation)<span class="org-code">{{ $demande->reference }}</span>@endunless
            </h1>
            <p class="adm-subtitle">
                Enregistrez votre demande en brouillon pour la compléter plus tard, ou soumettez-la pour lancer la validation hiérarchique.
                Une fois soumise, elle n'est plus modifiable.
            </p>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="La demande n'a pas pu être soumise" closable>{{ $message }}</x-alert>
    @enderror
    @if($errors->any() && ! $errors->has('regle_metier'))
        <x-alert type="error" closable>Le formulaire contient des erreurs : vérifiez les champs signalés.</x-alert>
    @endif

    <form method="POST" action="{{ $creation ? route('demandes.store') : route('demandes.update', $demande) }}"
          x-data="{
              type: @js($typeInitial),
              lignes: @js(array_values($lignesInitiales)),
              ajouter() { this.lignes.push({ ressource_id: '', quantite: 1 }); },
              retirer(i) { this.lignes.splice(i, 1); if (this.lignes.length === 0) this.ajouter(); },
          }">
        @csrf
        @unless($creation) @method('PUT') @endunless

        <div class="adm-grid">
            <div class="adm-grid__main">

                {{-- 1. Objet --}}
                <section class="adm-card">
                    <header class="adm-card__header"><h2 class="adm-card__title"><span class="dem-step">1</span> Objet de la demande</h2></header>
                    <div class="adm-card__body">
                        <x-form.input name="objet" label="Objet" :value="$demande?->objet" :error="$errors->first('objet')" required
                                      maxlength="255" placeholder="Ex. Poste de travail pour un nouvel arrivant" />
                        <x-form.select name="application_id" label="Application concernée" placeholder="— Aucune —"
                                       :options="$applications->mapWithKeys(fn ($a) => [$a->id => $a->nom.' ('.$a->reference.')'])->all()"
                                       :selected="$demande?->application_id" :error="$errors->first('application_id')"
                                       hint="Facultatif : à renseigner si la demande porte sur une application (RG-12)." />
                    </div>
                </section>

                {{-- 2. Ressources --}}
                <section class="adm-card">
                    <header class="adm-card__header"><h2 class="adm-card__title"><span class="dem-step">2</span> Ressources souhaitées</h2></header>
                    <div class="adm-card__body">
                        @if($ressources->isEmpty())
                            <p class="adm-note adm-note--warning"><x-icon name="alert-triangle" size="sm" /> Aucune ressource n'est disponible : contactez l'administrateur.</p>
                        @endif
                        <div class="dem-lignes">
                            <template x-for="(ligne, i) in lignes" :key="i">
                                <div class="dem-ligne">
                                    <select class="form-control" :name="`lignes[${i}][ressource_id]`" x-model="ligne.ressource_id" aria-label="Ressource">
                                        <option value="">— Choisir une ressource —</option>
                                        @foreach($ressources as $categorie => $liste)
                                            <optgroup label="{{ $categorie }}">
                                                @foreach($liste as $ressource)
                                                    <option value="{{ $ressource->id }}">{{ $ressource->designation }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" max="999" class="form-control dem-ligne__qte" :name="`lignes[${i}][quantite]`"
                                           x-model="ligne.quantite" aria-label="Quantité">
                                    <button type="button" class="adm-icon-btn org-btn-off" @click="retirer(i)" title="Retirer cette ressource">
                                        <x-icon name="trash" size="sm" />
                                    </button>
                                </div>
                            </template>
                        </div>
                        @if($errors->has('lignes') || $errors->has('lignes.*'))
                            <p class="form-error">{{ $errors->first('lignes') ?: collect($errors->get('lignes.*'))->flatten()->first() }}</p>
                        @endif
                        <x-button variant="outline" size="sm" icon="plus" @click="ajouter()">Ajouter une ressource</x-button>
                    </div>
                </section>

                {{-- 3. Destination --}}
                <section class="adm-card">
                    <header class="adm-card__header"><h2 class="adm-card__title"><span class="dem-step">3</span> Destination</h2></header>
                    <div class="adm-card__body">
                        <p class="adm-card__text">
                            À qui la demande est-elle adressée ? Une seule destination (RG-13). Elle n'a rien à voir avec les
                            personnes qui valideront la demande : la validation suit toujours votre propre hiérarchie.
                        </p>
                        <div class="dem-types">
                            @foreach($types as $t)
                                <label class="dem-type" :class="type === @js($t->value) && 'dem-type--actif'">
                                    <input type="radio" name="destination_type" value="{{ $t->value }}" x-model="type">
                                    <x-icon :name="$icones[$t->value]" size="sm" />
                                    <span>Un {{ mb_strtolower($t->label()) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('destination_type')<p class="form-error">{{ $message }}</p>@enderror

                        <div x-show="type === 'DEPARTEMENT'" x-cloak class="form-group">
                            <label class="form-label form-label--required" for="destination_departement_id">Département destinataire</label>
                            <select id="destination_departement_id" name="destination_departement_id" class="form-control">
                                <option value="">— Choisir —</option>
                                @foreach($departements as $structure => $liste)
                                    <optgroup label="{{ $structure }}">
                                        @foreach($liste as $d)
                                            <option value="{{ $d->id }}" @selected((int) old('destination_departement_id', $demande?->destination_departement_id) === $d->id)>{{ $d->libelle }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('destination_departement_id')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="type === 'SERVICE'" x-cloak class="form-group">
                            <label class="form-label form-label--required" for="destination_service_id">Service destinataire</label>
                            <select id="destination_service_id" name="destination_service_id" class="form-control">
                                <option value="">— Choisir —</option>
                                @foreach($services as $departement => $liste)
                                    <optgroup label="{{ $departement }}">
                                        @foreach($liste as $s)
                                            <option value="{{ $s->id }}" @selected((int) old('destination_service_id', $demande?->destination_service_id) === $s->id)>{{ $s->libelle }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('destination_service_id')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="type === 'UTILISATEUR'" x-cloak class="form-group">
                            <label class="form-label form-label--required" for="destination_utilisateur_id">Utilisateur destinataire</label>
                            <select id="destination_utilisateur_id" name="destination_utilisateur_id" class="form-control">
                                <option value="">— Choisir —</option>
                                @foreach($receveurs as $r)
                                    <option value="{{ $r->id }}" @selected((int) old('destination_utilisateur_id', $demande?->destination_utilisateur_id) === $r->id)>
                                        {{ $r->nomComplet() }} · {{ $r->service?->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="form-hint">Seuls les utilisateurs ayant le rôle Receveur sont proposés.</p>
                            @error('destination_utilisateur_id')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- 4. Description --}}
                <section class="adm-card">
                    <header class="adm-card__header"><h2 class="adm-card__title"><span class="dem-step">4</span> Description et motif</h2></header>
                    <div class="adm-card__body">
                        <x-form.textarea name="description" label="Description ou motif" :value="$demande?->description" :error="$errors->first('description')"
                                         rows="4" placeholder="Pourquoi avez-vous besoin de ces ressources ? Pour qui, pour quand ?"
                                         hint="Obligatoire pour soumettre." />
                        <x-form.textarea name="informations_complementaires" label="Informations complémentaires" :value="$demande?->informations_complementaires"
                                         :error="$errors->first('informations_complementaires')" rows="3" placeholder="Facultatif" />
                    </div>
                </section>
            </div>

            {{-- ===== COLONNE LATÉRALE ===== --}}
            <aside class="adm-grid__side">
                <section class="adm-card">
                    <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="check-circle" size="sm" /> Circuit de validation</h2></header>
                    <div class="adm-card__body">
                        @include('demandes.partials.apercu-circuit', ['circuit' => $circuit])
                    </div>
                </section>

                <section class="adm-card dem-actions-card">
                    <div class="adm-card__body">
                        <x-button type="submit" name="action" value="soumettre" icon="arrow-right" block
                                  onclick="return confirm('Soumettre la demande ? Elle ne sera plus modifiable.')">Soumettre la demande</x-button>
                        <x-button type="submit" name="action" value="brouillon" variant="outline" icon="file" block>Enregistrer le brouillon</x-button>
                        <x-button :href="$creation ? route('demandes.index') : route('demandes.show', $demande)" variant="ghost" block>Annuler</x-button>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection
