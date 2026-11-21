<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RegleMetierException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionFormRequest;
use App\Models\Permission;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administration du catalogue des permissions.
 *
 * Les permissions « système » (Permission::SYSTEME) sont identifiées par leur slug
 * dans le code et le seeder : slug figé, suppression interdite.
 */
class PermissionController extends Controller
{
    public function __construct(private IPermissionService $permissions) {}

    public function create(): View
    {
        return view('admin.roles.permission-form', ['permission' => null]);
    }

    public function store(PermissionFormRequest $request): RedirectResponse
    {
        $permission = $this->permissions->createPermission($request->validated() + ['is_active' => true]);

        return redirect()->route('admin.roles.index', ['onglet' => 'permissions'])
            ->with('success', "Permission « {$permission->libelle} » créée.");
    }

    public function edit(Permission $permission): View
    {
        return view('admin.roles.permission-form', [
            'permission' => $permission->load(['roles' => fn ($q) => $q->orderBy('libelle')]),
        ]);
    }

    public function update(PermissionFormRequest $request, Permission $permission): RedirectResponse
    {
        $data = $request->validated();

        if ($permission->estSysteme() && $data['slug'] !== $permission->slug) {
            throw new RegleMetierException("La permission « {$permission->slug} » est utilisée par l'application : son slug ne peut pas changer.");
        }

        $this->permissions->updatePermission($permission->id, $data);

        return redirect()->route('admin.roles.index', ['onglet' => 'permissions'])
            ->with('success', 'Permission mise à jour.');
    }

    public function changerStatut(Request $request, Permission $permission): RedirectResponse
    {
        $actif = $request->boolean('is_active');
        $this->permissions->togglePermissionStatus($permission->id, $actif);

        return back()->with('success', $actif ? 'Permission activée.' : 'Permission désactivée.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if ($permission->estSysteme()) {
            throw new RegleMetierException("La permission « {$permission->slug} » est utilisée par l'application : elle ne peut pas être supprimée.");
        }

        // Retire la permission des rôles avant la suppression (logique) pour ne pas laisser de lien fantôme.
        $permission->roles()->detach();
        $this->permissions->deletePermission($permission->id);

        return redirect()->route('admin.roles.index', ['onglet' => 'permissions'])
            ->with('success', "Permission « {$permission->libelle} » supprimée.");
    }
}
