<div>
    <h2 class="dash-section-title">Actions rapides</h2>
    <p class="dash-section-subtitle">Accès direct aux fonctionnalités principales</p>

    <div class="dash-actions">
        @foreach($quickActions as $action)
            <a href="{{ url($action['url']) }}" class="dash-action">
                <div class="dash-action__icon dash-action__icon--{{ $action['color'] }}">
                    <x-icon :name="$action['icon']" size="md" />
                </div>
                <div>
                    <p class="dash-action__label">{{ $action['label'] }}</p>
                    <p class="dash-action__desc">{{ $action['desc'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>