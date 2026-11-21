<div class="dash-block">
    <div class="dash-block__header">
        <h3 class="dash-block__title">
            <x-icon name="bell" size="sm" />
            Notifications récentes
        </h3>
    </div>

    <div class="dash-block__body">
        @forelse($recentesNotifs as $notif)
            <div class="dash-item">
                <div class="dash-item__icon dash-item__icon--{{ $notif->type }}">
                    <x-icon :name="$notif->icone" size="sm" />
                </div>
                <div class="dash-item__content">
                    <p class="dash-item__title">{{ $notif->titre }}</p>
                    <span class="dash-item__time">{{ $notif->temps }}</span>
                </div>
            </div>
        @empty
            <div class="dash-empty">
                <p class="dash-empty__text">Aucune notification.</p>
            </div>
        @endforelse
    </div>
</div>
