@props([
    'id'       => 'modal',
    'title'    => null,
    'size'     => 'md',    // sm | md | lg | xl
    'centered' => false,
])

<div id="{{ $id }}"
     class="modal-overlay"
     x-data="{ open: false }"
     x-show="open"
     x-on:open-{{ $id }}.window="open = true"
     x-on:close-{{ $id }}.window="open = false"
     x-on:keydown.escape.window="open = false"
     @click.self="open = false"
     x-cloak
     style="display: none;">

    <div class="modal modal--{{ $size }} {{ $centered ? 'modal--centered' : '' }}"
         @click.stop>

        @if(!$centered && $title)
            <div class="modal__header">
                <h3 class="modal__title">{{ $title }}</h3>
                <button type="button" class="modal__close" @click="open = false" aria-label="Fermer">
                    <x-icon name="x" size="md" />
                </button>
            </div>
        @endif

        <div class="modal__body">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="modal__footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>