@props([
    'type'    => 'info',
    'title'   => null,
    'closable'=> false,
])

@php
    $icons = [
        'success' => 'check-circle',
        'error'   => 'x-circle',
        'warning' => 'alert-triangle',
        'info'    => 'info',
    ];
    $iconName = $icons[$type] ?? 'info';
@endphp

<div class="alert alert--{{ $type }}" role="alert" {{ $attributes }}>
    <x-icon :name="$iconName" size="md" class="alert__icon" />

    <div class="alert__content">
        @if($title)
            <span class="alert__title">{{ $title }}</span>
        @endif
        <p class="alert__text">{{ $slot }}</p>
    </div>

    @if($closable)
        <button type="button" class="alert__close" onclick="this.parentElement.remove()" aria-label="Fermer">
            <x-icon name="x" size="sm" />
        </button>
    @endif
</div>