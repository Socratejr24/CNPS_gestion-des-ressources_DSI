@props([
    'name'     => '',
    'label'    => null,
    'options'  => [],
    'selected' => null,
    'placeholder' => null,
    'hint'     => null,
    'error'    => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $name ?: 'select-' . uniqid();
    $controlClass = 'form-control';
    if ($error) $controlClass .= ' form-control--error';
    $current = old($name, $selected);
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}"
               class="form-label {{ $required ? 'form-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <select id="{{ $id }}"
            name="{{ $name }}"
            class="{{ $controlClass }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes }}>

        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $key => $val)
            <option value="{{ $key }}" @if($current == $key) selected @endif>{{ $val }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>