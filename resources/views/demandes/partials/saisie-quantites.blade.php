{{--
    Saisie des quantités fournies par ressource (mise à disposition ou régularisation).
    Variables : $demande, $valeurParDefaut ('demandee' | 'fournie')
--}}
<table class="dem-quantites">
    <thead>
        <tr>
            <th>Ressource</th>
            <th class="dem-quantites__num">Demandée</th>
            <th class="dem-quantites__num">Fournie</th>
            <th>Observation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($demande->lignes as $ligne)
            @php
                $defaut = $valeurParDefaut === 'fournie' ? ($ligne->quantite_fournie ?? $ligne->quantite_demandee) : $ligne->quantite_demandee;
            @endphp
            <tr>
                <td>{{ $ligne->ressource->designation }}</td>
                <td class="dem-quantites__num">{{ $ligne->quantite_demandee }}</td>
                <td class="dem-quantites__num">
                    <input type="number" min="0" max="9999" required class="form-control dem-ligne__qte"
                           name="lignes[{{ $ligne->id }}][quantite_fournie]"
                           value="{{ old('lignes.'.$ligne->id.'.quantite_fournie', $defaut) }}"
                           aria-label="Quantité fournie : {{ $ligne->ressource->designation }}">
                </td>
                <td>
                    <input type="text" maxlength="500" class="form-control"
                           name="lignes[{{ $ligne->id }}][observation]"
                           value="{{ old('lignes.'.$ligne->id.'.observation', $ligne->observation) }}"
                           placeholder="Facultatif" aria-label="Observation : {{ $ligne->ressource->designation }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
