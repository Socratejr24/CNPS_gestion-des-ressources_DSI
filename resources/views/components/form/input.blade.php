@props([
    'name'        => '',
    'label'       => null,
    'type'        => 'text',
    'value'       => '',
    'placeholder' => '',
    'hint'        => null,
    'error'       => null,
    'required'    => false,
    'disabled'    => false,
])

@php
    $id = $name ?: 'input-' . uniqid();
    $controlClass = 'form-control';
    if ($error) $controlClass .= ' form-control--error';
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}"
               class="form-label {{ $required ? 'form-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}"
           id="{{ $id }}"
           name="{{ $name }}"
           value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}"
           class="{{ $controlClass }}"
           @if($required) required @endif
           @if($disabled) disabled @endif
           {{ $attributes }}>

    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>