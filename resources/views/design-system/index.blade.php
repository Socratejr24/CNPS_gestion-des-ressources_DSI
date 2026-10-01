@extends('layouts.app')

@section('title', 'Design System')
@section('page-title', 'Design System')

@push('styles')
    @vite([
        'resources/css/components/buttons.css',
        'resources/css/components/forms.css',
        'resources/css/components/badges.css',
        'resources/css/components/icons.css',
        'resources/css/components/cards.css',
        'resources/css/components/tables.css',
        'resources/css/components/alerts.css',
        'resources/css/components/modals.css',
        'resources/css/components/toasts.css',
        'resources/css/components/filters.css',
    ])
@endpush

@section('content')

<div class="max-w-5xl mx-auto space-y-10 pb-20">

    {{-- ===== BOUTONS ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">① Boutons</h2>
        <p class="text-sm text-cnps-muted mb-4">Variantes, tailles, icônes et états.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <x-button variant="primary">Primaire</x-button>
                <x-button variant="accent">Accent</x-button>
                <x-button variant="success">Succès</x-button>
                <x-button variant="danger">Danger</x-button>
                <x-button variant="outline">Contour</x-button>
                <x-button variant="ghost">Discret</x-button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-button variant="primary" disabled>Désactivé</x-button>
                <x-button variant="primary" :loading="true">Chargement</x-button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-button variant="primary" size="sm">Petit</x-button>
                <x-button variant="primary" size="md">Normal</x-button>
                <x-button variant="primary" size="lg">Grand</x-button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-button variant="primary" icon="plus">Nouvelle demande</x-button>
                <x-button variant="outline" icon="download">Exporter</x-button>
                <x-button variant="danger" icon="trash">Supprimer</x-button>
            </div>
        </div>
    </section>

    {{-- ===== BADGES ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">② Badges d'état</h2>
        <p class="text-sm text-cnps-muted mb-4">Statuts des demandes.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border">
            <div class="flex flex-wrap items-center gap-3">
                <x-badge state="brouillon">Brouillon</x-badge>
                <x-badge state="attente">En attente N+1</x-badge>
                <x-badge state="cours">En cours</x-badge>
                <x-badge state="validee">Validée</x-badge>
                <x-badge state="rejetee">Rejetée</x-badge>
                <x-badge state="cloturee">Clôturée</x-badge>
                <x-badge state="info">Information</x-badge>
            </div>
        </div>
    </section>

    {{-- ===== FORMULAIRES ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">③ Champs de formulaire</h2>
        <p class="text-sm text-cnps-muted mb-4">Inputs, selects, textarea, checkbox et switch.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border space-y-5">
            <x-form.input name="objet" label="Objet" placeholder="Ex : Remplacement poste de travail"
                          hint="Texte d'aide standard." required />
            <x-form.input name="email" label="Email" value="jkouadio.cnps" type="email"
                          error="Format d'e-mail invalide." />
            <x-form.input name="matricule" label="Matricule" value="104582"
                          hint="Matricule vérifié." />
            <x-form.select name="type" label="Type de ressource"
                           :options="['1' => 'Matériel', '2' => 'Logiciel', '3' => 'Infrastructure']"
                           placeholder="Sélectionner…" />
            <x-form.textarea name="description" label="Description"
                             placeholder="Détails de la demande…" :rows="4" />
            <div class="space-y-2">
                <x-form.checkbox name="notif_email" label="Notifier par e-mail" checked />
                <x-form.checkbox name="notif_app" label="Notification dans l'application" />
            </div>
            <div class="space-y-3">
                <x-form.switch name="theme_dark" label="Thème sombre (bientôt)" />
                <x-form.switch name="actif" label="Compte actif" checked />
            </div>
        </div>
    </section>

    {{-- ===== KPI ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">④ Cartes KPI</h2>
        <p class="text-sm text-cnps-muted mb-4">Chiffres clés avec couleurs sémantiques.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-kpi value="128" label="Demandes totales" color="blue" icon="file-text" />
            <x-kpi value="14"  label="En attente"       color="orange" icon="clock" trend="+3 cette semaine" trendDirection="up" />
            <x-kpi value="96"  label="Validées"         color="green" icon="check-circle" />
            <x-kpi value="6"   label="Rejetées"         color="red" icon="x-circle" />
        </div>
    </section>

    {{-- ===== CARDS ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑤ Cards</h2>
        <p class="text-sm text-cnps-muted mb-4">Conteneurs de contenu.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-card title="Demande #0142" subtitle="Créée le 29/09/2026" icon="file-text">
                <p class="text-sm text-cnps-muted">
                    Poste de travail — demandé par A. Koffi.
                </p>
            </x-card>

            <x-card title="Prestataire actif" icon="user">
                <p class="text-sm text-cnps-muted">
                    Accès accordé jusqu'au 31/12/2026, renouvelable.
                </p>
                <x-slot:footer>
                    <span>Dernière connexion : hier</span>
                    <x-badge state="validee">Actif</x-badge>
                </x-slot:footer>
            </x-card>
        </div>
    </section>

    {{-- ===== TABLEAUX ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑥ Tableaux</h2>
        <p class="text-sm text-cnps-muted mb-4">Listes de données avec en-tête, hover et état vide.</p>

        <x-table :headers="['Réf.', 'Objet', 'Demandeur', 'Statut', 'Actions']">
            <tr>
                <td>#0142</td>
                <td>Poste de travail</td>
                <td>A. Koffi</td>
                <td><x-badge state="attente">En attente</x-badge></td>
                <td>
                    <div class="table__actions">
                        <button class="table__action" title="Voir"><x-icon name="eye" size="sm" /></button>
                        <button class="table__action table__action--danger" title="Supprimer"><x-icon name="trash" size="sm" /></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>#0141</td>
                <td>Accès serveur applicatif</td>
                <td>M. Touré</td>
                <td><x-badge state="cours">En cours</x-badge></td>
                <td>
                    <div class="table__actions">
                        <button class="table__action" title="Voir"><x-icon name="eye" size="sm" /></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>#0140</td>
                <td>Licence logicielle</td>
                <td>S. Diabaté</td>
                <td><x-badge state="validee">Validée</x-badge></td>
                <td>
                    <div class="table__actions">
                        <button class="table__action" title="Voir"><x-icon name="eye" size="sm" /></button>
                    </div>
                </td>
            </tr>
        </x-table>

        <div class="mt-4">
            <p class="text-sm text-cnps-muted mb-3">Version "vide" :</p>
            <x-table :headers="['Réf.', 'Objet', 'Statut']" :empty="true"
                     emptyTitle="Aucune demande"
                     emptyText="Vous n'avez pas encore créé de demande." />
        </div>
    </section>

    {{-- ===== FILTRES ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑦ Barre de filtres</h2>
        <p class="text-sm text-cnps-muted mb-4">Recherche + filtres + reset.</p>

        <x-filters searchPlaceholder="Rechercher une demande…">
            <select name="statut" class="filters__select">
                <option value="">Tous les statuts</option>
                <option value="attente">En attente</option>
                <option value="cours">En cours</option>
                <option value="validee">Validée</option>
            </select>
            <select name="type" class="filters__select">
                <option value="">Tous les types</option>
                <option value="materiel">Matériel</option>
                <option value="logiciel">Logiciel</option>
            </select>
        </x-filters>
    </section>

    {{-- ===== ALERTS ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑧ Alertes inline</h2>
        <p class="text-sm text-cnps-muted mb-4">Bandeaux de messages contextuels.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border space-y-3">
            <x-alert type="success" title="Demande envoyée">
                Votre demande <strong>#0143</strong> a été transmise à votre validateur N+1.
            </x-alert>

            <x-alert type="error" title="Échec de l'envoi">
                Vérifiez les champs obligatoires avant de soumettre.
            </x-alert>

            <x-alert type="warning" title="Information manquante">
                Une pièce jointe est recommandée pour ce type de demande.
            </x-alert>

            <x-alert type="info" title="À savoir">
                Le traitement d'une demande prend en moyenne 2 jours ouvrés.
            </x-alert>

            <x-alert type="success" title="Fermable" :closable="true">
                Vous pouvez fermer cette alerte avec le bouton à droite.
            </x-alert>
        </div>
    </section>

        {{-- ===== MODALES ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑨ Modales</h2>
        <p class="text-sm text-cnps-muted mb-4">Fenêtres de confirmation et formulaires.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border flex flex-wrap gap-3">
            <x-button variant="danger" @click="openModal('modal-confirm-delete')">
                Ouvrir modale suppression
            </x-button>
            <x-button variant="success" @click="openModal('modal-success')">
                Ouvrir modale succès
            </x-button>
        </div>

        {{-- Modale suppression --}}
        <x-modal id="modal-confirm-delete" size="sm" :centered="true">
            <div class="modal__icon modal__icon--danger">
                <x-icon name="trash" size="lg" />
            </div>
            <h3 class="modal__title text-center">Supprimer la demande ?</h3>
            <p class="text-sm text-cnps-muted text-center mt-2">
                Cette action est irréversible et supprimera l'historique associé.
            </p>

            <x-slot:footer>
                <x-button variant="ghost" @click="closeModal('modal-confirm-delete')">
                    Annuler
                </x-button>
                <x-button variant="danger" @click="closeModal('modal-confirm-delete')">
                    Supprimer
                </x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Modale succès --}}
        <x-modal id="modal-success" size="sm" :centered="true">
            <div class="modal__icon modal__icon--success">
                <x-icon name="check" size="lg" />
            </div>
            <h3 class="modal__title text-center">Demande validée</h3>
            <p class="text-sm text-cnps-muted text-center mt-2">
                Elle sera transmise à l'étape suivante du circuit.
            </p>

            <x-slot:footer>
                <x-button variant="success" @click="closeModal('modal-success')">
                    Compris
                </x-button>
            </x-slot:footer>
        </x-modal>
    </section>
    {{-- ===== TOASTS ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑩ Toasts</h2>
        <p class="text-sm text-cnps-muted mb-4">Notifications éphémères en bas à droite.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border flex flex-wrap gap-3">
            <x-button variant="success" @click="showToast('success', 'Enregistré', 'Votre demande a été créée avec succès.')">
                Toast succès
            </x-button>
            <x-button variant="danger" @click="showToast('error', 'Erreur', 'Impossible de contacter le serveur.')">
                Toast erreur
            </x-button>
            <x-button variant="accent" @click="showToast('warning', 'Attention', 'Vérifiez les informations saisies.')">
                Toast warning
            </x-button>
            <x-button variant="primary" @click="showToast('info', 'Information', 'Une nouvelle version est disponible.')">
                Toast info
            </x-button>
        </div>
    </section>

    {{-- ===== ICÔNES ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">⑪ Icônes</h2>
        <p class="text-sm text-cnps-muted mb-4">
            Bibliothèque utilisable via <code>&lt;x-icon name="…" /&gt;</code>.
        </p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border">
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4">
                @php
                    $iconNames = [
                        'home','bell','user','users','settings','logout','check','check-circle',
                        'x','x-circle','trash','edit','download','search','plus','plus-circle',
                        'file','file-text','inbox','refresh','rotate','message','shield','building',
                        'layers','box','tag','monitor','alert-circle','alert-triangle','info','eye',
                        'eye-off','filter','calendar','clock','mail','lock','link','server',
                        'database','globe','chart','chevron-down','chevron-right','chevron-left',
                        'arrow-right','arrow-left','attachment'
                    ];
                @endphp
                @foreach($iconNames as $iconName)
                    <div class="flex flex-col items-center gap-2 text-center">
                        <x-icon :name="$iconName" size="lg" color="primary" />
                        <span class="text-[10px] text-cnps-muted break-words">{{ $iconName }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>







</div>

@endsection