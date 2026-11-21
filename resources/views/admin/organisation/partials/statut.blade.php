{{-- Bouton activer / désactiver. Paramètres : $action (URL), $actif (bool), $nom (libellé de l'entité). --}}
<form method="POST" action="{{ $action }}" class="org-inline"
      @if($actif) onsubmit="return confirm(@js('Désactiver « '.$nom.' » ?'))" @endif>
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $actif ? 0 : 1 }}">
    <button type="submit" class="adm-icon-btn {{ $actif ? 'org-btn-off' : 'org-btn-on' }}"
            title="{{ $actif ? 'Désactiver' : 'Réactiver' }} « {{ $nom }} »">
        <x-icon :name="$actif ? 'x-circle' : 'refresh'" size="sm" />
    </button>
</form>
