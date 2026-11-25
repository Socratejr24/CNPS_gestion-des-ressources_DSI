{{-- Une notification (tiroir, tableau de bord, page). Paramètre : $notification (DatabaseNotification). --}}
@php($d = $notification->data)
<a href="{{ route('notifications.lire', $notification->id) }}" class="notif-item {{ $notification->read_at ? '' : 'notif-item--unread' }}">
    <div class="notif-item__icon notif-item__icon--{{ $d['couleur'] ?? 'blue' }}">
        <x-icon :name="$d['icone'] ?? 'info'" size="sm" />
    </div>
    <div class="notif-item__content">
        <p class="notif-item__title">{{ $d['titre'] ?? 'Notification' }}</p>
        @if(! empty($d['message']))
            <p class="notif-item__message">{{ \Illuminate\Support\Str::limit($d['message'], 140) }}</p>
        @endif
        <span class="notif-item__time">{{ $notification->created_at->diffForHumans() }}</span>
    </div>
</a>
