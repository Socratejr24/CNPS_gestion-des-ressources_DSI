<div class="dash-kpis">
    @foreach($kpis as $kpi)
        <x-kpi
            :value="$kpi['valeur']"
            :label="$kpi['label']"
            :hint="$kpi['aide']"
            :color="$kpi['couleur']"
            :icon="$kpi['icone']"
        />
    @endforeach
</div>
