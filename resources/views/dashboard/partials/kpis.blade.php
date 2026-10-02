<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-kpi
        :value="$kpis['en_cours']"
        label="En cours"
        color="blue"
        icon="clock"
    />
    <x-kpi
        :value="$kpis['en_attente']"
        label="En attente"
        color="orange"
        icon="alert-circle"
    />
    <x-kpi
        :value="$kpis['validees']"
        label="Validées"
        color="green"
        icon="check-circle"
    />
    <x-kpi
        :value="$kpis['cloturees']"
        label="Clôturées"
        color="gray"
        icon="archive"
    />
</div>