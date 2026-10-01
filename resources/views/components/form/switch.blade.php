@props([
    'name'     => '',
    'label'    => '',
    'checked'  => false,
    'disabled' => false,
])

@php
    $id = $name ?: 'switch-' . uniqid();
    $isChecked = old($name) ? true : $checked;
@endphp

<label for="{{ $id }}" class="form-switch">
    <input type="checkbox"
           id="{{ $id }}"
           name="{{ $name }}"
           class="form-switch__input"
           @if($isChecked) checked @endif
           @if($disabled) disabled @endif
           {{ $attributes }}>
    <span class="form-switch__slider"></span>
    @if($label)
        <span class="form-switch__label">{{ $label }}</span>
    @endif
</label>