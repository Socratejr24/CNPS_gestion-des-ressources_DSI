<div class="dash-charts-grid">
    <x-card title="Répartition par statut" :subtitle="$global ? 'Toutes les demandes soumises' : 'Toutes mes demandes'">
        @if(array_sum($chartStatuts['series']) === 0)
            <p class="dash-empty__text dash-chart-vide">Aucune demande pour le moment.</p>
        @else
            <div id="chart-statuts" class="dash-chart"></div>
        @endif
    </x-card>

    <x-card title="Demandes créées" subtitle="6 derniers mois">
        <div id="chart-evolution" class="dash-chart"></div>
    </x-card>
</div>
