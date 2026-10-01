@props([
    'name'     => '',
    'label'    => '',
    'value'    => '1',
    'checked'  => false,
    'disabled' => false,
])

@php
    $id = $name ?: 'checkbox-' . uniqid();
    $isChecked = old($name) ? true : $checked;
@endphp

<label for="{{ $id }}" class="form-check">
    <input type="checkbox"
           id="{{ $id }}"
           name="{{ $name }}"
           value="{{ $value }}"
           @if($isChecked) checked @endif
           @if($disabled) disabled @endif
           {{ $attributes }}>
    <span>{{ $label }}</span>
</label>