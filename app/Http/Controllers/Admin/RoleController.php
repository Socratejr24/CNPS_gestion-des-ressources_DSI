<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RegleMetierException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleFormRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Interfaces\IRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administration des rôles fonctionnels (SFD §3.3) et de leurs permissions.
 *
 * Les rôles « système » (Role::SYSTEME) sont référencés par le code :
 * ils ne peuvent être ni renommés, ni désactivés, ni supprimés.
 */
class RoleController extends Controller
{
    public function __construct(private IRoleService $roles) {}

    public function index(Request $request): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount(['utilisateurs', 'permissions'])->orderBy('libelle')->get(),
            'permissions' => Permission::withCount('roles')->orderBy('slug')->get(),
            'onglet' => $request->query('onglet') === 'permissions' ? 'permissions' : 'roles',
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.role-form', ['role' => null, 'permissions' => collect()]);
    }

    public function store(RoleFormRequest $request): RedirectResponse
    {
        $role = $this->roles->createRole($request->validated() + ['is_active' => true]);

        return redirect()->route('admin.roles.edit', $role)
            ->with('success', "Rôle « {$role->libelle} » créé. Choisissez maintenant ses permissions.");
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.role-form', [
            'role' => $role->load('permissions')->loadCount('utilisateurs'),
            'permissions' => Permission::orderBy('slug')->get(),
        ]);
    }

    public function update(RoleFormRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        if ($role->estSysteme() && $data['libelle'] !== $role->libelle) {
            throw new RegleMetierException("Le rôle « {$role->libelle} » est utilisé par l'application : il ne peut pas être renommé.");
        }

        $this->roles->updateRole($role->id, $data);

        return back()->with('success', 'Rôle mis à jour.');
    }

    public function permissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'distinct'],
        ]);

        $this->roles->syncRolePermissions($role->id, $validated['permission_ids'] ?? []);

        return back()->with('success', 'Permissions du rôle mises à jour.');
    }

    public function changerStatut(Request $request, Role $role): RedirectResponse
    {
        $actif = $request->boolean('is_active');

        if (! $actif && $role->estSysteme()) {
            throw new RegleMetierException("Le rôle « {$role->libelle} » est utilisé par l'application : il ne peut pas être désactivé.");
        }

        $this->roles->toggleRoleStatus($role->id, $actif);

        return back()->with('success', $actif ? 'Rôle activé.' : 'Rôle désactivé.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->estSysteme()) {
            throw new RegleMetierException("Le rôle « {$role->libelle} » est utilisé par l'application : il ne peut pas être supprimé.");
        }

        $nombre = $role->utilisateurs()->count();
        if ($nombre > 0) {
            throw new RegleMetierException(
                "Impossible de supprimer ce rôle : il est attribué à {$nombre} utilisateur(s). Retirez-le d'abord ou désactivez-le."
            );
        }

        $this->roles->deleteRole($role->id);

        return redirect()->route('admin.roles.index')->with('success', "Rôle « {$role->libelle} » supprimé.");
    }
}
