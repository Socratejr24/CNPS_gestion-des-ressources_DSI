@props([
    'headers' => [],
    'empty'   => false,
    'emptyTitle' => 'Aucun résultat',
    'emptyText'  => "Aucune donnée à afficher pour l'instant.",
])

<div class="table-wrapper" {{ $attributes }}>
    <table class="table">
        @if(count($headers))
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @if($empty)
                <tr>
                    <td colspan="{{ count($headers) ?: 1 }}">
                        <div class="table__empty">
                            <div class="table__empty-icon">
                                <x-icon name="inbox" size="xl" />
                            </div>
                            <h3 class="table__empty-title">{{ $emptyTitle }}</h3>
                            <p class="table__empty-text">{{ $emptyText }}</p>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>

    @if(isset($footer))
        {{ $footer }}
    @endif
</div>