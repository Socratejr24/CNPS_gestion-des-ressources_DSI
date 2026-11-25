{{--
    Actions du receveur (SFD §8, §9.3) : prise en charge, mise à disposition,
    informations complémentaires, impossibilité, régularisation.
    Variables : $demande, $peutPrendre, $peutTraiter, $peutRegulariser, $anomalieEnCours
--}}
@if($peutPrendre || $peutTraiter || $peutRegulariser)
    <section class="adm-card dem-decision" x-data>
        <header class="adm-card__header">
            <h2 class="adm-card__title"><x-icon name="inbox" size="sm" /> Traitement</h2>
        </header>
        <div class="adm-card__body">
            @if($peutPrendre)
                <p class="adm-card__text">
                    Cette demande est adressée à votre périmètre. En la prenant en charge, vous en devenez le receveur attitré (RG-23).
                </p>
                <form method="POST" action="{{ route('traitement.prendre', $demande) }}">
                    @csrf
                    <x-button type="submit" icon="user" block>Prendre en charge</x-button>
                </form>
            @elseif($peutTraiter)
                <p class="adm-card__text">Vous traitez cette demande depuis le {{ $demande->prise_en_charge_le?->format('d/m/Y') }}.</p>
                <div class="dem-decision__boutons">
                    <x-button variant="success" icon="box" block @click="$dispatch('open-modal-disposition')">Déclarer la mise à disposition</x-button>
                    <x-button variant="outline" icon="message" block @click="$dispatch('open-modal-informations-receveur')">Demander des informations</x-button>
                    <x-button variant="ghost-danger" icon="x-circle" block @click="$dispatch('open-modal-impossibilite')">Signaler une impossibilité</x-button>
                </div>
            @elseif($peutRegulariser)
                <p class="adm-card__text">Le demandeur a signalé une anomalie de réception : corrigez la situation puis déclarez la régularisation.</p>
                <x-button variant="success" icon="refresh" block @click="$dispatch('open-modal-regularisation')">Déclarer la régularisation</x-button>
            @endif
        </div>
    </section>
@endif

@if($peutTraiter)
    <x-modal id="modal-disposition" title="Déclarer la mise à disposition" size="lg">
        <form method="POST" action="{{ route('traitement.disposition', $demande) }}" id="form-disposition">
            @csrf
            <p class="adm-card__text">Indiquez les quantités effectivement fournies (RG-27). Le demandeur devra ensuite confirmer la réception.</p>
            @include('demandes.partials.saisie-quantites', ['valeurParDefaut' => 'demandee'])
            <div class="adm-form__grid">
                <x-form.input name="date" type="date" label="Date de mise à disposition" :value="now()->format('Y-m-d')" required
                              max="{{ now()->format('Y-m-d') }}" />
            </div>
            <x-form.textarea name="observations" label="Observations (facultatif)" rows="3"
                             placeholder="Ex. matériel à récupérer au bureau 214, identifiants envoyés par e-mail…" />
        </form>
        <x-slot:footer>
            <x-button variant="ghost" @click="$dispatch('close-modal-disposition')">Annuler</x-button>
            <x-button type="submit" form="form-disposition" variant="success" icon="box">Déclarer la mise à disposition</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="modal-informations-receveur" title="Demander des informations complémentaires" size="md">
        <form method="POST" action="{{ route('traitement.informations', $demande) }}" id="form-informations-receveur">
            @csrf
            <p class="adm-card__text">La demande est mise en attente ; après la réponse du demandeur, elle vous reviendra directement.</p>
            <x-form.textarea name="question" label="Informations attendues" rows="4" required />
        </form>
        <x-slot:footer>
            <x-button variant="ghost" @click="$dispatch('close-modal-informations-receveur')">Annuler</x-button>
            <x-button type="submit" form="form-informations-receveur" icon="message">Envoyer la demande</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="modal-impossibilite" title="Signaler une impossibilité de traitement" size="md">
        <form method="POST" action="{{ route('traitement.impossibilite', $demande) }}" id="form-impossibilite">
            @csrf
            <p class="adm-card__text">
                La demande passera au statut <strong>Traitement impossible</strong>, qui est <strong>définitif</strong>.
                Le demandeur verra la justification et pourra, s'il le souhaite, créer une nouvelle demande.
            </p>
            <x-form.textarea name="justification" label="Justification" rows="4" required
                             placeholder="Ex. matériel en rupture chez le fournisseur, licence non disponible…" />
        </form>
        <x-slot:footer>
            <x-button variant="ghost" @click="$dispatch('close-modal-impossibilite')">Annuler</x-button>
            <x-button type="submit" form="form-impossibilite" variant="danger" icon="x-circle">Signaler l'impossibilité</x-button>
        </x-slot:footer>
    </x-modal>
@endif

@if($peutRegulariser)
    <x-modal id="modal-regularisation" title="Déclarer la régularisation" size="lg">
        <form method="POST" action="{{ route('traitement.regularisation', $demande) }}" id="form-regularisation">
            @csrf
            @if($anomalieEnCours)
                <p class="adm-note adm-note--warning">
                    <x-icon name="alert-triangle" size="sm" />
                    Anomalie signalée : {{ $anomalieEnCours->nature->label() }} — {{ $anomalieEnCours->description }}
                </p>
            @endif
            <p class="adm-card__text">Mettez à jour les quantités désormais fournies. La demande reviendra en attente de confirmation du demandeur (RG-31).</p>
            @include('demandes.partials.saisie-quantites', ['valeurParDefaut' => 'fournie'])
            <x-form.textarea name="explication" label="Régularisation effectuée" rows="3" required
                             placeholder="Ex. écran manquant livré ce jour, quantité corrigée…" />
        </form>
        <x-slot:footer>
            <x-button variant="ghost" @click="$dispatch('close-modal-regularisation')">Annuler</x-button>
            <x-button type="submit" form="form-regularisation" variant="success" icon="refresh">Déclarer la régularisation</x-button>
        </x-slot:footer>
    </x-modal>
@endif
