@php
    $user = current_user();
@endphp

<template x-if="profileDrawerOpen">
    <div>
        <div class="app-drawer-overlay" @click="closeAllPanels()"></div>

        <aside class="app-drawer" role="dialog" aria-label="Mon profil">

            <header class="app-drawer__header">
                <h2 class="app-drawer__title">Mon profil</h2>
                <button type="button" class="app-drawer__close" @click="closeAllPanels()" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </header>

            <div class="app-drawer__body">

                {{-- Hero --}}
                <div class="profile-hero">
                    <div class="profile-hero__avatar">
                        {{-- Si photo : <img src="..."> --}}
                        {{ $user->initiales }}
                    </div>
                    <h3 class="profile-hero__name">{{ $user->prenom }} {{ $user->nom }}</h3>
                    <div class="profile-hero__roles">
                        @foreach($user->roles as $role)
                            <span class="profile-hero__role-badge">{{ $role }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Informations --}}
                <div class="profile-section">
                    <h4 class="profile-section__title">Informations</h4>

                    <div class="profile-info-row">
                        <span class="profile-info-row__label">Matricule</span>
                        <span class="profile-info-row__value">{{ $user->matricule }}</span>
                    </div>

                    <div class="profile-info-row">
                        <span class="profile-info-row__label">Email</span>
                        <span class="profile-info-row__value">{{ $user->email }}</span>
                    </div>

                    <div class="profile-info-row">
                        <span class="profile-info-row__label">Fonction</span>
                        <span class="profile-info-row__value">Développeur</span>
                    </div>

                    <div class="profile-info-row">
                        <span class="profile-info-row__label">Structure</span>
                        <span class="profile-info-row__value">DSI</span>
                    </div>


<div class="profile-info-row">
                        <span class="profile-info-row__label">Département</span>
                        <span class="profile-info-row__value">DEV</span>
                    </div>


                    <div class="profile-info-row">
                        <span class="profile-info-row__label">Service</span>
                        <span class="profile-info-row__value">Développement IT</span>
                    </div>

                </div>

                {{-- Rôles --}}
                <div class="profile-section">
                    <h4 class="profile-section__title">Rôles</h4>

                    <div class="profile-info-row">
                        <span class="profile-info-row__label">Rôles applicatifs</span>
                        <span class="profile-info-row__value">{{ implode(', ', $user->roles) }}</span>
                    </div>
                </div>

            </div>

        </aside>
    </div>
</template>