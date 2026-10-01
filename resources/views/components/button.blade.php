@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'button',
    'href'    => null,
    'icon'    => null,
    'loading' => false,
    'disabled'=> false,
    'block'   => false,
])

@php
    $classes = 'btn btn--' . $variant . ' btn--' . $size;
    if ($block)    $classes .= ' btn--block';
    if ($loading)  $classes .= ' btn--loading';
    if ($disabled) $classes .= ' btn--disabled';
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" {{ $attributes }}>
        @if($icon)<x-icon :name="$icon" size="sm" class="btn__icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
            class="{{ $classes }}"
            @if($disabled) disabled @endif
            {{ $attributes }}>
        @if($icon)<x-icon :name="$icon" size="sm" class="btn__icon" />@endif
        {{ $slot }}
    </button>
@endif