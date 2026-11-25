{{--
    Réception par le demandeur (SFD §9.1, §9.2) : confirmation ou anomalie.
    Variables : $demande, $peutReceptionner, $natures
--}}
@if($peutReceptionner)
    <section class="adm-card dem-decision" x-data>
        <header class="adm-card__header">
            <h2 class="adm-card__title"><x-icon name="box" size="sm" /> Réception</h2>
        </header>
        <div class="adm-card__body">
            <p class="adm-card__text">
                {{ $demande->receveur?->nomComplet() ?? 'Le receveur' }} a déclaré les ressources mises à disposition
                le {{ $demande->mise_a_disposition_le?->format('d/m/Y') }}. Vérifiez-les, puis confirmez la réception
                ou signalez une anomalie.
            </p>
            <div class="dem-decision__boutons">
                <x-button variant="success" icon="check-circle" block @click="$dispatch('open-modal-confirmer')">Confirmer la réception</x-button>
                <x-button variant="ghost-danger" icon="alert-triangle" block @click="$dispatch('open-modal-anomalie')">Signaler une anomalie</x-button>
            </div>
        </div>
    </section>

    <x-modal id="modal-confirmer" title="Confirmer la réception" size="md">
        <form method="POST" action="{{ route('demandes.confirmer', $demande) }}" id="form-confirmer">
            @csrf
            <p class="adm-card__text">
                En confirmant, vous attestez avoir bien reçu les ressources annoncées.
                La demande sera <strong>clôturée</strong> (RG-28) et ne pourra plus évoluer.
            </p>
            <x-form.textarea name="commentaire" label="Commentaire (facultatif)" rows="3" />
        </form>
        <x-slot:footer>
            <x-button variant="ghost" @click="$dispatch('close-modal-confirmer')">Annuler</x-button>
            <x-button type="submit" form="form-confirmer" variant="success" icon="check-circle">Confirmer et clôturer</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="modal-anomalie" title="Signaler une anomalie de réception" size="lg">
        <form method="POST" action="{{ route('demandes.anomalie', $demande) }}" id="form-anomalie">
            @csrf
            <p class="adm-card__text">
                Le receveur sera chargé de régulariser la situation ; la demande reviendra ensuite vers vous pour confirmation (RG-30).
            </p>
            <x-form.select name="nature" label="Nature de l'anomalie" required placeholder="— Choisir —"
                           :options="collect($natures)->mapWithKeys(fn ($n) => [$n->value => $n->label()])->all()" />

            <p class="form-label form-label--required">Ressources concernées</p>
            <table class="dem-quantites">
                <thead>
                    <tr>
                        <th></th>
                        <th>Ressource</th>
                        <th class="dem-quantites__num">Annoncée</th>
                        <th class="dem-quantites__num">Quantité concernée</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demande->lignes as $ligne)
                        <tr>
                            <td><input type="checkbox" name="concernees[]" value="{{ $ligne->id }}" aria-label="{{ $ligne->ressource->designation }}"
                                       @checked(in_array($ligne->id, old('concernees', [])))></td>
                            <td>{{ $ligne->ressource->designation }}</td>
                            <td class="dem-quantites__num">{{ $ligne->quantite_fournie ?? '—' }}</td>
                            <td class="dem-quantites__num">
                                <input type="number" min="0" max="9999" class="form-control dem-ligne__qte" name="quantites[{{ $ligne->id }}]"
                                       value="{{ old('quantites.'.$ligne->id) }}" placeholder="—" aria-label="Quantité concernée">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <x-form.textarea name="description" label="Description" rows="4" required
                             placeholder="Ex. un seul écran reçu au lieu de deux ; le clavier ne fonctionne pas…" />
        </form>
        <x-slot:footer>
            <x-button variant="ghost" @click="$dispatch('close-modal-anomalie')">Annuler</x-button>
            <x-button type="submit" form="form-anomalie" variant="danger" icon="alert-triangle">Signaler l'anomalie</x-button>
        </x-slot:footer>
    </x-modal>
@endif
