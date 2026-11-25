@extends('layouts.app')

@section('title', $demande->reference)
@section('page-title', $estDemandeur ? 'Mes demandes' : 'Demande '.$demande->reference)

@push('styles')
    @vite(['resources/css/admin/index.css', 'resources/css/demandes/index.css'])
@endpush

@php
    $peutModifier = auth()->user()->can('update', $demande);
    $libellesEtat = ['fait' => 'Terminée', 'en_cours' => 'En cours', 'a_venir' => 'À venir', 'ignore' => 'Non requise', 'refuse' => 'Refusée'];
    $rejet = $demande->statut === \App\Enums\DemandeStatut::REJETEE
        ? $demande->evenements->where('action', \App\Enums\ActionDemande::REJET)->last()
        : null;
    $libelleNiveau = $niveau === 'N1' ? 'N+1 (chef de service)' : 'N+2 (responsable du département)';
@endphp

@section('content')
<div class="adm-page">

    @if($estDemandeur)
        <a href="{{ route('demandes.index') }}" class="adm-back"><x-icon name="arrow-left" size="sm" /> Retour à mes demandes</a>
    @elseif($demande->receveur_id === auth()->id() || $peutPrendre)
        <a href="{{ route('traitement.index') }}" class="adm-back"><x-icon name="arrow-left" size="sm" /> Retour aux demandes reçues</a>
    @elseif(auth()->user()->estValidateur())
        <a href="{{ route('validations.index') }}" class="adm-back"><x-icon name="arrow-left" size="sm" /> Retour aux demandes à valider</a>
    @endif

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
    @if($errors->hasAny(['motif', 'question', 'reponse', 'commentaire']))
        <x-alert type="error" closable>{{ $errors->first('motif') ?: $errors->first('question') ?: $errors->first('reponse') ?: $errors->first('commentaire') }}</x-alert>
    @endif

    @if($rejet)
        <x-alert type="error" title="Demande rejetée par {{ $rejet->utilisateur?->nomComplet() ?? '—' }} ({{ $rejet->niveau === 'N1' ? 'N+1' : 'N+2' }}) le {{ $rejet->created_at->format('d/m/Y') }}">
            Motif : {{ $rejet->commentaire }}
        </x-alert>
    @endif

    @if($impossibilite)
        <x-alert type="error" title="Traitement impossible — signalé par {{ $impossibilite->utilisateur?->nomComplet() ?? '—' }} le {{ $impossibilite->created_at->format('d/m/Y') }}">
            {{ $impossibilite->commentaire }}
        </x-alert>
    @endif

    @if($anomalieEnCours)
        <x-alert type="warning" title="Anomalie de réception en cours de régularisation">
            {{ $anomalieEnCours->nature->label() }} — {{ $anomalieEnCours->description }}
        </x-alert>
    @endif

    @if($errors->hasAny(['lignes', 'lignes.*', 'date', 'justification', 'explication', 'nature', 'description', 'concernees']))
        <x-alert type="error" closable>
            {{ collect($errors->getMessages())->only(['lignes', 'date', 'justification', 'explication', 'nature', 'description', 'concernees'])->flatten()->first()
               ?? collect($errors->get('lignes.*'))->flatten()->first() }}
        </x-alert>
    @endif

    @if($posteVacant)
        <x-alert type="warning" title="Validation bloquée">
            Le poste de valideur {{ $libelleNiveau }} est vacant : personne ne peut statuer sur cette demande.
            L'administrateur doit désigner un titulaire dans l'écran Organisation.
        </x-alert>
    @endif

    {{-- Informations complémentaires demandées (SFD §10) --}}
    @if($demande->statut === \App\Enums\DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE && $question)
        <section class="adm-card dem-question">
            <header class="adm-card__header">
                <h2 class="adm-card__title"><x-icon name="message" size="sm" /> Informations complémentaires demandées</h2>
            </header>
            <div class="adm-card__body">
                <p class="dem-question__meta">
                    {{ $question->utilisateur?->nomComplet() ?? '—' }}
                    @if($question->niveau) ({{ $question->niveau === 'N1' ? 'N+1' : 'N+2' }})@endif
                    · le {{ $question->created_at->format('d/m/Y à H:i') }}
                </p>
                <blockquote class="dem-question__texte">{{ $question->commentaire }}</blockquote>

                @if($peutRepondre)
                    <form method="POST" action="{{ route('demandes.repondre', $demande) }}" class="adm-inline-form">
                        @csrf
                        <x-form.textarea name="reponse" label="Votre réponse" rows="4" required :error="$errors->first('reponse')"
                                         hint="Après votre réponse, la demande revient directement à la personne qui a posé la question, sans repasser par les validations déjà faites." />
                        <div><x-button type="submit" icon="arrow-right">Envoyer ma réponse</x-button></div>
                    </form>
                @else
                    <p class="form-hint">En attente de la réponse du demandeur.</p>
                @endif
            </div>
        </section>
    @endif

    <div class="adm-grid">
        <div class="adm-grid__main">

            {{-- Informations --}}
            <section class="adm-card">
                <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="file-text" size="sm" /> Informations</h2></header>
                <div class="adm-card__body">
                    <dl class="adm-dl">
                        <div><dt>Application</dt><dd>{{ $demande->application ? $demande->application->nom.' ('.$demande->application->reference.')' : '—' }}</dd></div>
                        <div><dt>Destination</dt><dd>{{ $demande->destination_type ? $demande->libelleDestination() : 'Non renseignée' }}</dd></div>
                        @if($demande->receveur)
                            <div><dt>Receveur</dt><dd>{{ $demande->receveur->nomComplet() }} <span class="adm-cell-sub">depuis le {{ $demande->prise_en_charge_le?->format('d/m/Y') }}</span></dd></div>
                        @endif
                        @if($demande->mise_a_disposition_le)
                            <div><dt>Mise à disposition</dt><dd>Le {{ $demande->mise_a_disposition_le->format('d/m/Y') }}</dd></div>
                        @endif
                        @if($demande->cloturee_le)
                            <div><dt>Clôture</dt><dd>Le {{ $demande->cloturee_le->format('d/m/Y à H:i') }}</dd></div>
                        @endif
                    </dl>
                    @if($demande->observations_mise_a_disposition)
                        <div class="dem-texte">
                            <h3>Observations du receveur</h3>
                            <p>{!! nl2br(e($demande->observations_mise_a_disposition)) !!}</p>
                        </div>
                    @endif
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
                            <td>
                                @if($ligne->quantite_fournie === null)
                                    —
                                @else
                                    <span class="{{ $ligne->quantite_fournie < $ligne->quantite_demandee ? 'dem-qte-ecart' : '' }}">{{ $ligne->quantite_fournie }}</span>
                                @endif
                                @if($ligne->observation)<span class="adm-cell-sub">{{ $ligne->observation }}</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </section>

            {{-- Anomalies de réception (SFD §9.2, §9.3) --}}
            @if($demande->anomalies->isNotEmpty())
                <section class="adm-card">
                    <header class="adm-card__header"><h2 class="adm-card__title"><x-icon name="alert-triangle" size="sm" /> Anomalies de réception ({{ $demande->anomalies->count() }})</h2></header>
                    <div class="adm-card__body">
                        <ol class="dem-anomalies">
                            @foreach($demande->anomalies as $anomalie)
                                <li class="dem-anomalie {{ $anomalie->estRegularisee() ? 'dem-anomalie--reglee' : '' }}">
                                    <p class="dem-anomalie__titre">
                                        <strong>{{ $anomalie->nature->label() }}</strong>
                                        @if($anomalie->estRegularisee())
                                            <x-badge state="validee" size="sm">Régularisée</x-badge>
                                        @else
                                            <x-badge state="attente" size="sm">En cours de régularisation</x-badge>
                                        @endif
                                    </p>
                                    <p class="dem-histo__meta">Signalée par {{ $anomalie->signaleePar?->nomComplet() ?? '—' }} le {{ $anomalie->created_at->format('d/m/Y à H:i') }}</p>
                                    <p class="dem-anomalie__ressources">
                                        @foreach($anomalie->lignes as $concernee)
                                            @php($l = $demande->lignes->firstWhere('id', $concernee['ligne_id']))
                                            <span class="adm-role">{{ $l?->ressource->designation ?? 'Ressource supprimée' }}@if($concernee['quantite']) · {{ $concernee['quantite'] }}@endif</span>
                                        @endforeach
                                    </p>
                                    <p class="dem-histo__commentaire">{{ $anomalie->description }}</p>
                                    @if($anomalie->estRegularisee())
                                        <p class="dem-histo__meta">
                                            Régularisée par {{ $anomalie->regulariseePar?->nomComplet() ?? '—' }} le {{ $anomalie->regularisee_le->format('d/m/Y à H:i') }} :
                                            {{ $anomalie->explication_regularisation }}
                                        </p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @endif

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
            @if($peutDecider)
                <section class="adm-card dem-decision" x-data>
                    <header class="adm-card__header">
                        <h2 class="adm-card__title"><x-icon name="shield" size="sm" /> Votre décision</h2>
                    </header>
                    <div class="adm-card__body">
                        <p class="adm-card__text">Vous intervenez en tant que <strong>{{ $libelleNiveau }}</strong> du demandeur.</p>
                        <div class="dem-decision__boutons">
                            <x-button variant="success" icon="check-circle" block @click="$dispatch('open-modal-valider')">Valider</x-button>
                            <x-button variant="outline" icon="message" block @click="$dispatch('open-modal-informations')">Demander des informations</x-button>
                            <x-button variant="ghost-danger" icon="x-circle" block @click="$dispatch('open-modal-rejeter')">Rejeter</x-button>
                        </div>
                    </div>
                </section>
            @endif

            @include('demandes.partials.traitement')
            @include('demandes.partials.reception')

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

    {{-- ===== FENÊTRES DE DÉCISION (valideur N+1 / N+2) ===== --}}
    @if($peutDecider)
        <x-modal id="modal-valider" title="Valider la demande" size="md">
            <form method="POST" action="{{ route('validations.valider', $demande) }}" id="form-valider">
                @csrf
                <p class="adm-card__text">
                    @if($niveau === 'N1')
                        La demande sera transmise au responsable du département (N+2), ou directement au destinataire si la validation N+2 n'est pas requise.
                    @else
                        La demande sera transmise à son destinataire : {{ $demande->libelleDestination() }}.
                    @endif
                </p>
                <x-form.textarea name="commentaire" label="Commentaire (facultatif)" rows="3" />
            </form>
            <x-slot:footer>
                <x-button variant="ghost" @click="$dispatch('close-modal-valider')">Annuler</x-button>
                <x-button type="submit" form="form-valider" variant="success" icon="check-circle">Valider</x-button>
            </x-slot:footer>
        </x-modal>

        <x-modal id="modal-informations" title="Demander des informations complémentaires" size="md">
            <form method="POST" action="{{ route('validations.informations', $demande) }}" id="form-informations">
                @csrf
                <p class="adm-card__text">La demande est mise en attente ; après la réponse du demandeur, elle vous reviendra directement.</p>
                <x-form.textarea name="question" label="Informations attendues" rows="4" required
                                 placeholder="Ex. Précisez la date d'arrivée de l'agent et le modèle souhaité." />
            </form>
            <x-slot:footer>
                <x-button variant="ghost" @click="$dispatch('close-modal-informations')">Annuler</x-button>
                <x-button type="submit" form="form-informations" icon="message">Envoyer la demande</x-button>
            </x-slot:footer>
        </x-modal>

        <x-modal id="modal-rejeter" title="Rejeter la demande" size="md">
            <form method="POST" action="{{ route('validations.rejeter', $demande) }}" id="form-rejeter">
                @csrf
                <p class="adm-card__text">Le rejet est <strong>définitif</strong> : la demande ne poursuivra pas son circuit. Le demandeur verra le motif.</p>
                <x-form.textarea name="motif" label="Motif du rejet" rows="4" required />
            </form>
            <x-slot:footer>
                <x-button variant="ghost" @click="$dispatch('close-modal-rejeter')">Annuler</x-button>
                <x-button type="submit" form="form-rejeter" variant="danger" icon="x-circle">Rejeter définitivement</x-button>
            </x-slot:footer>
        </x-modal>
    @endif
</div>
@endsection
