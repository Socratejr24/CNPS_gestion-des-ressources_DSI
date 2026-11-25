@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page adm-page--narrow">

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Notifications</h1>
            <p class="adm-subtitle">
                Événements concernant vos demandes et celles dont vous avez la charge.
                Cliquez sur une notification pour ouvrir la demande ; elle sera marquée comme lue.
            </p>
        </div>
        @if($nombreNonLues > 0)
            <form method="POST" action="{{ route('notifications.tout-lire') }}">
                @csrf
                <x-button type="submit" variant="outline" icon="check">Tout marquer comme lu</x-button>
            </form>
        @endif
    </div>

    <div class="adm-tabs" role="tablist">
        <a href="{{ route('notifications.index') }}" role="tab" class="adm-tab {{ $nonLues ? '' : 'adm-tab--active' }}">Toutes</a>
        <a href="{{ route('notifications.index', ['non_lues' => 1]) }}" role="tab" class="adm-tab {{ $nonLues ? 'adm-tab--active' : '' }}">
            Non lues <span class="adm-tab__count">{{ $nombreNonLues }}</span>
        </a>
    </div>

    <div class="adm-card notif-page">
        @forelse($notifications as $notification)
            @include('notifications.partials.item')
        @empty
            <p class="app-drawer__empty">{{ $nonLues ? 'Aucune notification non lue.' : 'Aucune notification pour le moment.' }}</p>
        @endforelse
        @include('admin.partials.pagination', ['pages' => $notifications])
    </div>
</div>
@endsection
