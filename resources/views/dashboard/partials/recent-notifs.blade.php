<div class="dash-block">
    {{-- Header --}}
    <div class="dash-block__header">
        <h3 class="dash-block__title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
            Notifications
        </h3>
    </div>

    {{-- Body --}}
    <div class="dash-block__body">
        @if($recentesNotifs->isEmpty())
            <div class="dash-empty">
                <p class="dash-empty__text">Aucune notification.</p>
            </div>
        @else
            @foreach($recentesNotifs as $notif)
                <a href="{{ url($notif->url) }}" class="dash-item">
                    <div class="dash-item__icon dash-item__icon--{{ $notif->type }}">
                        @if($notif->icone === 'check-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline stroke-linecap="round" stroke-linejoin="round" points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        @elseif($notif->icone === 'alert-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <line stroke-linecap="round" x1="12" y1="8" x2="12" y2="12"/>
                                <line stroke-linecap="round" x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <line stroke-linecap="round" x1="12" y1="16" x2="12" y2="12"/>
                                <line stroke-linecap="round" x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                        @endif
                    </div>
                    <div class="dash-item__content">
                        <p class="dash-item__title">{{ $notif->titre }}</p>
                        <span class="dash-item__time">{{ $notif->temps }}</span>
                    </div>
                </a>
            @endforeach
        @endif
    </div>

    {{-- Footer --}}
    <div class="dash-block__footer">
        <a href="{{ url('/notifications') }}" class="dash-block__view-all">
            Voir toutes les notifications
        </a>
    </div>
</div>