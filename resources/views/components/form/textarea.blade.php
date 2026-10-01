@props([
    'name'        => '',
    'label'       => null,
    'value'       => '',
    'placeholder' => '',
    'rows'        => 4,
    'hint'        => null,
    'error'       => null,
    'required'    => false,
    'disabled'    => false,
])

@php
    $id = $name ?: 'textarea-' . uniqid();
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

    <textarea id="{{ $id }}"
              name="{{ $name }}"
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              class="{{ $controlClass }}"
              @if($required) required @endif
              @if($disabled) disabled @endif
              {{ $attributes }}>{{ old($name, $value) }}</textarea>

    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>