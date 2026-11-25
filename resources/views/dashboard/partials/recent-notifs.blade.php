<div class="dash-block">
    <div class="dash-block__header">
        <h3 class="dash-block__title">
            <x-icon name="bell" size="sm" />
            Notifications récentes
        </h3>
        <a href="{{ route('notifications.index') }}" class="dash-block__view-all">Tout voir</a>
    </div>

    <div class="dash-block__body dash-notifs">
        @forelse($recentesNotifs as $notification)
            @include('notifications.partials.item')
        @empty
            <div class="dash-empty">
                <p class="dash-empty__text">Aucune notification.</p>
            </div>
        @endforelse
    </div>
</div>
