@extends('layouts.app')

@section('title', $demande->reference)
@section('page-title', 'Mes demandes')

@push('styles')
    @vite(['resources/css/admin/index.css', 'resources/css/demandes/index.css'])
@endpush

@php
    $peutModifier = auth()->user()->can('update', $demande);
    $libellesEtat = ['fait' => 'Terminée', 'en_cours' => 'En cours', 'a_venir' => 'À venir', 'ignore' => 'Non requise', 'refuse' => 'Refusée'];
@endphp

@section('content')
<div class="adm-page">

    <a href="{{ route('demandes.index') }}" class="adm-back"><x-icon name="arrow-left" size="sm" /> Retour à mes demandes</a>

    {{-- ===== EN-TÊTE ===== --}}
    <div class="adm-header">
        <div>
            <p class="dem-ref">{{ $demande->reference }}</p>
            <h1 class="adm-title">{{ $demande->objet }}</h1>
            <div class="adm-profile__meta">
                @include('demandes.partials.statut', ['statut' => $demande->statut, 'complet' => true])
                <span>Créée le {{ $demande->created_at->format('d/m/Y à H:i') }}</span>
                @if($demande->soumise_le)<span>· Soumise le {{ $demande->soumise_le->format('d/m/Y à H:i') }}</span>@endif
            </div>
        </div>
        @if($peutModifier)
            <div class="dem-header-actions">
                <form method="POST" action="{{ route('demandes.destroy', $demande) }}"
                      onsubmit="return confirm(@js('Supprimer définitivement le brouillon '.$demande->reference.' ?'))">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="ghost-danger" icon="trash">Supprimer</x-button>
                </form>
                <x-button :href="route('demandes.edit', $demande)" variant="outline" icon="edit">Modifier</x-button>
                <form method="POST" action="{{ route('demandes.soumettre', $demande) }}"
                      onsubmit="return confirm('Soumettre la demande ? Elle ne sera plus modifiable.')">
                    @csrf
                    <x-button type="submit" icon="arrow-right">Soumettre</x-button>
                </form>
            </div>
        @endif
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    <div class="adm-grid">
        <div class="adm-grid__main">

            {{-- Informations --}}
            <section class="adm-card">
                <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="file-text" size="sm" /> Informations</h2></header>
                <div class="adm-card__body">
                    <dl class="adm-dl">
                        <div><dt>Application</dt><dd>{{ $demande->application ? $demande->application->nom.' ('.$demande->application->reference.')' : '—' }}</dd></div>
                        <div><dt>Destination</dt><dd>{{ $demande->destination_type ? $demande->libelleDestination() : 'Non renseignée' }}</dd></div>
                    </dl>
                    <div class="dem-texte">
                        <h3>Description ou motif</h3>
                        <p>{!! $demande->description ? nl2br(e($demande->description)) : '<span class="adm-cell-sub">Non renseignée</span>' !!}</p>
                    </div>
                    @if($demande->informations_complementaires)
                        <div class="dem-texte">
                            <h3>Informations complémentaires</h3>
                            <p>{!! nl2br(e($demande->informations_complementaires)) !!}</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Ressources --}}
            <section class="adm-card">
                <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="box" size="sm" /> Ressources demandées ({{ $demande->lignes->count() }})</h2></header>
                <x-table :headers="['Ressource', 'Catégorie', 'Quantité demandée', 'Quantité fournie']" :empty="$demande->lignes->isEmpty()"
                         empty-title="Aucune ressource" empty-text="Ajoutez au moins une ressource avant de soumettre.">
                    @foreach($demande->lignes as $ligne)
                        <tr>
                            <td>
                                <span class="adm-cell-main">{{ $ligne->ressource->designation }}</span>
                                <span class="adm-cell-sub">{{ $ligne->ressource->reference }}</span>
                            </td>
                            <td><span class="adm-role">{{ $ligne->ressource->categorie }}</span></td>
                            <td>{{ $ligne->quantite_demandee }}</td>
                            <td>{{ $ligne->quantite_fournie ?? '—' }}</td>
                        </tr>
                    @endforeach
                </x-table>
            </section>

            {{-- Historique --}}
            <section class="adm-card">
                <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="clock" size="sm" /> Historique</h2></header>
                <div class="adm-card__body">
                    <ol class="dem-histo">
                        @foreach($demande->evenements->reverse() as $evenement)
                            <li class="dem-histo__item">
                                <span class="dem-histo__icon"><x-icon :name="$evenement->action->icone()" size="sm" /></span>
                                <div>
                                    <p class="dem-histo__titre">
                                        <strong>{{ $evenement->action->label() }}</strong>
                                        @if($evenement->niveau)<span class="adm-role">{{ $evenement->niveau === 'N1' ? 'N+1' : 'N+2' }}</span>@endif
                                        @if($evenement->ancien_statut && $evenement->nouveau_statut && $evenement->ancien_statut !== $evenement->nouveau_statut)
                                            <span class="dem-histo__transition">
                                                {{ $evenement->ancien_statut->label() }} → {{ $evenement->nouveau_statut->label() }}
                                            </span>
                                        @endif
                                    </p>
                                    <p class="dem-histo__meta">
                                        {{ $evenement->utilisateur?->nomComplet() ?? 'Système' }} · {{ $evenement->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                    @if($evenement->commentaire)<p class="dem-histo__commentaire">{{ $evenement->commentaire }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        </div>

        {{-- ===== COLONNE LATÉRALE ===== --}}
        <aside class="adm-grid__side">
            <section class="adm-card">
                <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="check-circle" size="sm" /> Suivi</h2></header>
                <div class="adm-card__body">
                    <ol class="dem-etapes">
                        @foreach($etapes as $etape)
                            <li class="dem-etape dem-etape--{{ $etape['etat'] }}">
                                <span class="dem-etape__puce"></span>
                                <div>
                                    <p class="dem-etape__titre">{{ $etape['libelle'] }} <span class="dem-etape__etat">{{ $libellesEtat[$etape['etat']] }}</span></p>
                                    <p class="dem-etape__acteur">{{ $etape['acteur'] }}</p>
                                    @if($etape['note'])<p class="dem-etape__note">{{ $etape['note'] }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section class="adm-card">
                <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="user" size="sm" /> Demandeur</h2></header>
                <div class="adm-card__body">
                    <dl class="adm-dl adm-dl--stack">
                        <div><dt>Nom</dt><dd>{{ $demande->demandeur->nomComplet() }}</dd></div>
                        <div><dt>Service</dt><dd>{{ ($demande->serviceDemandeur ?? $demande->demandeur->service)?->libelle ?? '—' }}</dd></div>
                        <div><dt>Département</dt><dd>{{ ($demande->serviceDemandeur ?? $demande->demandeur->service)?->departement?->libelle ?? '—' }}</dd></div>
                    </dl>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
