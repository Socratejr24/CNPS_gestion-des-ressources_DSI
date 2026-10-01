@props([
    'value'  => '0',
    'label'  => '',
    'color'  => 'blue',
    'icon'   => null,
    'trend'  => null,
    'trendDirection' => 'up',
])

<div class="kpi kpi--{{ $color }}" {{ $attributes }}>
    <div class="kpi__header">
        <p class="kpi__label">
            <span class="kpi__label-dot"></span>
            {{ $label }}
        </p>

        @if($icon)
            <div class="kpi__icon">
                <x-icon :name="$icon" size="md" />
            </div>
        @endif
    </div>

    <div class="kpi__value">{{ $value }}</div>

    @if($trend)
        <div class="kpi__trend kpi__trend--{{ $trendDirection }}">
            {{ $trendDirection === 'up' ? '↑' : '↓' }}
            {{ $trend }}
        </div>
    @endif
</div>