import ApexCharts from 'apexcharts';

/**
 * Initialise les 3 graphiques du Dashboard
 * ⚠️ Les données sont injectées depuis le contrôleur via window.dashboardData
 */
export function initDashboardCharts() {
    const data = window.dashboardData || {};
    if (!data.statuts) return;

    const fontFamily = "'Inter', system-ui, sans-serif";

    // ============================================
    // 1. Donut : répartition par statut
    // ============================================
    const donutEl = document.querySelector('#chart-statuts');
    if (donutEl) {
        new ApexCharts(donutEl, {
            chart: {
                type: 'donut',
                height: 260,
                fontFamily,
                toolbar: { show: false },
            },
            labels: data.statuts.labels,
            series: data.statuts.series,
            colors: data.statuts.colors,
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: { width: 8, height: 8, radius: 4 },
                itemMargin: { horizontal: 6, vertical: 4 },
            },
            dataLabels: {
                enabled: true,
                formatter: (val) => `${Math.round(val)}%`,
                style: { fontSize: '11px', fontWeight: 600 },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total',
                                fontSize: '12px',
                                color: '#6b7190',
                                formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                            },
                            value: {
                                fontSize: '22px',
                                fontWeight: 700,
                                color: '#1b2140',
                            },
                        },
                    },
                },
            },
            tooltip: {
                y: { formatter: (val) => `${val} demande(s)` },
            },
        }).render();
    }

    // ============================================
    // 2. Ligne : évolution sur 6 mois
    // ============================================
    const lineEl = document.querySelector('#chart-evolution');
    if (lineEl) {
        new ApexCharts(lineEl, {
            chart: {
                type: 'area',
                height: 260,
                fontFamily,
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            series: [data.evolution.series],
            xaxis: {
                categories: data.evolution.labels,
                labels: {
                    style: { fontSize: '11px', colors: '#6b7190' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#6b7190' },
                },
            },
            colors: ['#27357e'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.02,
                    stops: [0, 100],
                },
            },
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            markers: {
                size: 5,
                colors: ['#ffffff'],
                strokeColors: '#27357e',
                strokeWidth: 2,
                hover: { size: 7 },
            },
            grid: {
                borderColor: '#eef0f6',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
            },
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: (val) => `${val} demande(s)` },
            },
        }).render();
    }

    // ============================================
    // 3. Barres horizontales : top 5 ressources
    // ============================================
    const barEl = document.querySelector('#chart-ressources');
    if (barEl) {
        new ApexCharts(barEl, {
            chart: {
                type: 'bar',
                height: 260,
                fontFamily,
                toolbar: { show: false },
            },
            series: [data.topRessources.series],
            xaxis: {
                categories: data.topRessources.labels,
                labels: {
                    style: { fontSize: '11px', colors: '#6b7190' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#6b7190' },
                },
            },
            colors: ['#de7c0f'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    barHeight: '60%',
                    distributed: false,
                },
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: '#eef0f6',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
            tooltip: {
                y: { formatter: (val) => `${val} demande(s)` },
            },
        }).render();
    }
}

// ============================================
// Auto-init au chargement du DOM (EN DEHORS de la fonction)
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    if (window.dashboardData) {
        initDashboardCharts();
    }
});