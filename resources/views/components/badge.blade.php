@props([
    'state' => 'info',
    'size'  => 'md',
    'dot'   => true,
])

@php
    $classes = 'badge badge--' . $state;
    if ($size !== 'md') $classes .= ' badge--' . $size;
@endphp

<span class="{{ $classes }}" {{ $attributes }}>
    @if($dot)<span class="badge__dot"></span>@endif
    {{ $slot }}
</span>