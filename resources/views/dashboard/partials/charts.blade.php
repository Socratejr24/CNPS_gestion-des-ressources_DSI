<div>
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="dash-section-title">Statistiques</h2>
            <p class="dash-section-subtitle">Aperçu de votre activité</p>
        </div>

        <div class="dash-period">
            <button type="button"
                    class="dash-period__btn"
                    :class="period === '7j' ? 'dash-period__btn--active' : ''"
                    @click="setPeriod('7j')">7j</button>
            <button type="button"
                    class="dash-period__btn"
                    :class="period === '30j' ? 'dash-period__btn--active' : ''"
                    @click="setPeriod('30j')">30j</button>
            <button type="button"
                    class="dash-period__btn"
                    :class="period === '3m' ? 'dash-period__btn--active' : ''"
                    @click="setPeriod('3m')">3 mois</button>
            <button type="button"
                    class="dash-period__btn"
                    :class="period === '6m' ? 'dash-period__btn--active' : ''"
                    @click="setPeriod('6m')">6 mois</button>
        </div>
    </div>

    <div class="dash-charts-grid">
        {{-- Donut statuts --}}
        <x-card title="Répartition par statut">
            <div id="chart-statuts" class="dash-chart"></div>
        </x-card>

        {{-- Évolution --}}
        <x-card title="Évolution sur 6 mois">
            <div id="chart-evolution" class="dash-chart"></div>
        </x-card>
    </div>

    <div class="mt-4">
        {{-- Top ressources --}}
        <x-card title="Top 5 des ressources demandées">
            <div id="chart-ressources" class="dash-chart"></div>
        </x-card>
    </div>
</div>