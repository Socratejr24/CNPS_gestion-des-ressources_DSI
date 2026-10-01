@extends('layouts.app')

@section('title', 'Design System')
@section('page-title', 'Design System')

@push('styles')
    @vite([
        'resources/css/components/buttons.css',
        'resources/css/components/forms.css',
        'resources/css/components/badges.css',
        'resources/css/components/icons.css',
    ])
@endpush

@section('content')

<div class="max-w-5xl mx-auto space-y-10">

    {{-- ===== BOUTONS ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">① Boutons</h2>
        <p class="text-sm text-cnps-muted mb-4">Variantes et tailles disponibles.</p>

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

    {{-- ===== ICÔNES ===== --}}
    <section>
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-1">④ Icônes</h2>
        <p class="text-sm text-cnps-muted mb-4">Bibliothèque SVG utilisable partout via <code>&lt;x-icon name="…" /&gt;</code>.</p>

        <div class="p-6 bg-white rounded-xl border border-cnps-border">
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4">
                @php
                    $iconNames = ['home','bell','user','users','settings','logout','check','check-circle','x','trash','edit','download','search','plus','file-text','inbox','refresh','rotate','message','shield','building','layers','box','tag','monitor','alert-circle','alert-triangle','info','eye','filter','calendar','clock','mail','lock','link','server','database','globe','chart','chevron-right'];
                @endphp
                @foreach($iconNames as $iconName)
                    <div class="flex flex-col items-center gap-2 text-center">
                        <x-icon :name="$iconName" size="lg" color="primary" />
                        <span class="text-[10px] text-cnps-muted">{{ $iconName }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</div>

@endsection