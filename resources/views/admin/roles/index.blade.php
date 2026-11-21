@extends('layouts.app')

@section('title', 'Rôles et permissions')
@section('page-title', 'Rôles et permissions')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page" x-data="{ onglet: @js($onglet) }">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="adm-header">
        <div>
            <h1 class="adm-title">Rôles et permissions</h1>
            <p class="adm-subtitle">
                Un utilisateur peut cumuler plusieurs rôles ; chaque rôle regroupe des permissions.
                Les responsabilités N+1 / N+2 ne sont pas des rôles : elles se désignent dans l'organisation.
            </p>
        </div>
        <div>
            <x-button :href="route('admin.roles.create')" icon="plus" x-show="onglet === 'roles'">Nouveau rôle</x-button>
            <x-button :href="route('admin.roles.permissions.create')" icon="plus" x-show="onglet === 'permissions'" x-cloak>Nouvelle permission</x-button>
        </div>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    {{-- ===== ONGLETS ===== --}}
    <div class="adm-tabs" role="tablist">
        <button type="button" role="tab" class="adm-tab" :class="onglet === 'roles' && 'adm-tab--active'"
                :aria-selected="onglet === 'roles'" @click="onglet = 'roles'">
            <x-icon name="shield" size="sm" /> Rôles <span class="adm-tab__count">{{ $roles->count() }}</span>
        </button>
        <button type="button" role="tab" class="adm-tab" :class="onglet === 'permissions' && 'adm-tab--active'"
                :aria-selected="onglet === 'permissions'" @click="onglet = 'permissions'">
            <x-icon name="lock" size="sm" /> Permissions <span class="adm-tab__count">{{ $permissions->count() }}</span>
        </button>
    </div>

    {{-- ===== RÔLES ===== --}}
    <div class="adm-card" x-show="onglet === 'roles'">
        <x-table :headers="['Rôle', 'Utilisateurs', 'Permissions', 'Statut', '']" :empty="$roles->isEmpty()" empty-title="Aucun rôle">
            @foreach($roles as $role)
                <tr>
                    <td>
                        <a href="{{ route('admin.roles.edit', $role) }}" class="adm-user__name">{{ $role->libelle }}</a>
                        @if($role->estSysteme())<span class="adm-role" title="Utilisé par l'application : ne peut être ni renommé, ni désactivé, ni supprimé">Système</span>@endif
                        @if($role->description)<span class="adm-cell-sub">{{ $role->description }}</span>@endif
                    </td>
                    <td>{{ $role->utilisateurs_count }}</td>
                    <td>{{ $role->permissions_count }}</td>
                    <td>
                        @if($role->is_active)
                            <x-badge state="validee" size="sm">Actif</x-badge>
                        @else
                            <x-badge state="rejetee" size="sm">Désactivé</x-badge>
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="adm-icon-btn" title="Gérer « {{ $role->libelle }} »">
                            <x-icon name="chevron-right" size="sm" />
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    {{-- ===== PERMISSIONS ===== --}}
    <div class="adm-card" x-show="onglet === 'permissions'" x-cloak>
        <x-table :headers="['Permission', 'Slug', 'Rôles', 'Statut', '']" :empty="$permissions->isEmpty()"
                 empty-title="Aucune permission" empty-text="Créez une permission pour l'attribuer ensuite à des rôles.">
            @foreach($permissions as $permission)
                <tr>
                    <td>
                        <a href="{{ route('admin.roles.permissions.edit', $permission) }}" class="adm-user__name">{{ $permission->libelle }}</a>
                        @if($permission->estSysteme())<span class="adm-role" title="Utilisée par l'application : slug figé, suppression impossible">Système</span>@endif
                        @if($permission->description)<span class="adm-cell-sub">{{ $permission->description }}</span>@endif
                    </td>
                    <td><span class="org-code">{{ $permission->slug }}</span></td>
                    <td>{{ $permission->roles_count }}</td>
                    <td>
                        @if($permission->is_active)
                            <x-badge state="validee" size="sm">Active</x-badge>
                        @else
                            <x-badge state="rejetee" size="sm">Désactivée</x-badge>
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <div class="org-actions">
                            <a href="{{ route('admin.roles.permissions.edit', $permission) }}" class="adm-icon-btn" title="Modifier « {{ $permission->libelle }} »">
                                <x-icon name="edit" size="sm" />
                            </a>
                            @include('admin.organisation.partials.statut', [
                                'action' => route('admin.roles.permissions.statut', $permission),
                                'actif' => $permission->is_active, 'nom' => $permission->libelle,
                            ])
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>
</div>
@endsection
