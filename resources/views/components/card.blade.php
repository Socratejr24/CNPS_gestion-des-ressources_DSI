@props([
    'title'    => null,
    'subtitle' => null,
    'icon'     => null,
    'flush'    => false,
    'footer'   => null,
])

<div class="card" {{ $attributes }}>
    @if($title)
        <div class="card__header">
            <div>
                <h3 class="card__title">
                    @if($icon)<x-icon :name="$icon" size="sm" color="primary" />@endif
                    {{ $title }}
                </h3>
                @if($subtitle)
                    <p class="card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if(isset($actions))
                <div class="card__actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="card__body {{ $flush ? 'card__body--flush' : '' }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="card__footer">
            {{ $footer }}
        </div>
    @endif
</div>