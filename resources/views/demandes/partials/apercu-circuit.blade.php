{{-- Aperçu du circuit de validation du demandeur connecté. Paramètre : $circuit (CircuitValidation). --}}
@if($circuit->blocage)
    <p class="adm-note adm-note--warning"><x-icon name="alert-triangle" size="sm" /> {{ $circuit->blocage }}</p>
    <p class="form-hint">Vous pouvez tout de même enregistrer votre demande en brouillon.</p>
@else
    <ol class="dem-apercu">
        <li><strong>Vous</strong> soumettez la demande</li>
        @if($circuit->n1Requis)
            <li><strong>{{ $circuit->n1->nomComplet() }}</strong> valide (N+1, chef de service)</li>
        @endif
        @if($circuit->n2Requis)
            <li><strong>{{ $circuit->n2->nomComplet() }}</strong> valide (N+2, responsable du département)</li>
        @endif
        <li>Le <strong>destinataire</strong> traite la demande</li>
        <li><strong>Vous</strong> confirmez la réception</li>
    </ol>
    <p class="form-hint">{{ $circuit->explication() }}</p>
@endif
