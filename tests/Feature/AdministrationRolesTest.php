<?php

namespace Tests\Feature;

use App\Enums\CategorieUtilisateur;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationRolesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Role $roleAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->roleAdmin = Role::create(['libelle' => Role::ADMINISTRATEUR]);
        $this->admin = User::factory()->create(['categorie' => CategorieUtilisateur::PRESTATAIRE]);
        $this->admin->roles()->attach($this->roleAdmin);
    }

    private function admin()
    {
        return $this->actingAs($this->admin);
    }

    // ------------------------------------------------------------------
    // Accès et liste
    // ------------------------------------------------------------------

    public function test_reserve_a_l_administrateur(): void
    {
        $this->get('/admin/roles')->assertRedirect('/login');

        $agent = User::factory()->create(['service_id' => Service::factory()->create()->id]);
        $this->actingAs($agent)->get('/admin/roles')->assertForbidden();
        $this->actingAs($agent)->post('/admin/roles', ['libelle' => 'Pirate'])->assertForbidden();
    }

    public function test_liste_des_roles_et_des_permissions(): void
    {
        Permission::create(['libelle' => 'Voir le traitement', 'slug' => 'voir.traitement']);

        $this->admin()->get('/admin/roles')
            ->assertOk()
            ->assertSee(Role::ADMINISTRATEUR)
            ->assertSee('Système')
            ->assertSee('voir.traitement');

        $this->admin()->get('/admin/roles?onglet=permissions')->assertOk();
    }

    public function test_lien_dans_la_sidebar(): void
    {
        $this->admin()->get('/admin/utilisateurs')->assertOk()->assertSee(route('admin.roles.index'));
    }

    // ------------------------------------------------------------------
    // Rôles
    // ------------------------------------------------------------------

    public function test_creation_modification_et_permissions_d_un_role(): void
    {
        $this->admin()->get('/admin/roles/nouveau')->assertOk();

        $this->admin()->post('/admin/roles', ['libelle' => 'Gestionnaire de parc', 'description' => 'Parc matériel']);
        $role = Role::where('libelle', 'Gestionnaire de parc')->firstOrFail();
        $this->assertTrue($role->is_active);

        $this->admin()->get("/admin/roles/{$role->id}/modifier")->assertOk();

        $this->admin()->from("/admin/roles/{$role->id}/modifier")
            ->put("/admin/roles/{$role->id}", ['libelle' => 'Gestionnaire du parc', 'description' => null])
            ->assertSessionHasNoErrors();
        $this->assertSame('Gestionnaire du parc', $role->refresh()->libelle);

        $p1 = Permission::create(['libelle' => 'A', 'slug' => 'a.voir']);
        $p2 = Permission::create(['libelle' => 'B', 'slug' => 'b.voir']);

        $this->admin()->from("/admin/roles/{$role->id}/modifier")
            ->put("/admin/roles/{$role->id}/permissions", ['permission_ids' => [$p1->id, $p2->id]])
            ->assertSessionHas('success');
        $this->assertEqualsCanonicalizing([$p1->id, $p2->id], $role->permissions()->pluck('permissions.id')->all());

        // La synchronisation remplace : on ne garde que p2.
        $this->admin()->from("/admin/roles/{$role->id}/modifier")
            ->put("/admin/roles/{$role->id}/permissions", ['permission_ids' => [$p2->id]]);
        $this->assertSame([$p2->id], $role->permissions()->pluck('permissions.id')->all());

        // Aucune case cochée : plus aucune permission.
        $this->admin()->from("/admin/roles/{$role->id}/modifier")->put("/admin/roles/{$role->id}/permissions", []);
        $this->assertCount(0, $role->permissions()->get());
    }

    public function test_libelle_unique(): void
    {
        $this->admin()->from('/admin/roles/nouveau')
            ->post('/admin/roles', ['libelle' => Role::ADMINISTRATEUR])
            ->assertSessionHasErrors('libelle');
    }

    public function test_role_systeme_protege(): void
    {
        $id = $this->roleAdmin->id;

        $this->admin()->from("/admin/roles/{$id}/modifier")
            ->put("/admin/roles/{$id}", ['libelle' => 'Super admin'])
            ->assertSessionHasErrors('regle_metier');

        $this->admin()->from("/admin/roles/{$id}/modifier")
            ->patch("/admin/roles/{$id}/statut", ['is_active' => 0])
            ->assertSessionHasErrors('regle_metier');

        $this->admin()->from("/admin/roles/{$id}/modifier")
            ->delete("/admin/roles/{$id}")
            ->assertSessionHasErrors('regle_metier');

        $role = $this->roleAdmin->refresh();
        $this->assertSame(Role::ADMINISTRATEUR, $role->libelle);
        $this->assertTrue($role->is_active);
        $this->assertFalse($role->trashed());

        // La description reste modifiable.
        $this->admin()->from("/admin/roles/{$id}/modifier")
            ->put("/admin/roles/{$id}", ['libelle' => Role::ADMINISTRATEUR, 'description' => 'Tout gérer'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Tout gérer', $role->refresh()->description);
    }

    public function test_desactivation_et_suppression_d_un_role_libre(): void
    {
        $role = Role::create(['libelle' => 'Temporaire']);

        $this->admin()->from("/admin/roles/{$role->id}/modifier")
            ->patch("/admin/roles/{$role->id}/statut", ['is_active' => 0])
            ->assertSessionHas('success');
        $this->assertFalse($role->refresh()->is_active);

        $this->admin()->delete("/admin/roles/{$role->id}")->assertRedirect('/admin/roles');
        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    public function test_suppression_refusee_si_le_role_est_attribue(): void
    {
        $role = Role::create(['libelle' => 'Occupé']);
        User::factory()->create()->roles()->attach($role);

        $this->admin()->from("/admin/roles/{$role->id}/modifier")
            ->delete("/admin/roles/{$role->id}")
            ->assertSessionHasErrors('regle_metier');

        $this->assertNotSoftDeleted('roles', ['id' => $role->id]);
    }

    // ------------------------------------------------------------------
    // Permissions
    // ------------------------------------------------------------------

    public function test_creation_et_modification_d_une_permission(): void
    {
        $this->admin()->get('/admin/roles/permissions/nouvelle')->assertOk();

        $this->admin()->post('/admin/roles/permissions', ['libelle' => 'Valider', 'slug' => '  Demandes.Valider '])
            ->assertRedirect('/admin/roles?onglet=permissions');
        $permission = Permission::where('slug', 'demandes.valider')->firstOrFail();

        $this->admin()->get("/admin/roles/permissions/{$permission->id}/modifier")->assertOk();
        $this->admin()->put("/admin/roles/permissions/{$permission->id}", ['libelle' => 'Valider une demande', 'slug' => 'demandes.valider'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Valider une demande', $permission->refresh()->libelle);
    }

    public function test_slug_invalide_ou_en_double(): void
    {
        Permission::create(['libelle' => 'X', 'slug' => 'deja.pris']);

        $this->admin()->from('/admin/roles/permissions/nouvelle')
            ->post('/admin/roles/permissions', ['libelle' => 'Y', 'slug' => 'avec espace'])
            ->assertSessionHasErrors('slug');

        $this->admin()->from('/admin/roles/permissions/nouvelle')
            ->post('/admin/roles/permissions', ['libelle' => 'Y', 'slug' => 'deja.pris'])
            ->assertSessionHasErrors('slug');
    }

    public function test_permission_systeme_protegee(): void
    {
        $permission = Permission::create(['libelle' => 'Admin', 'slug' => 'voir.administration']);

        $this->admin()->from("/admin/roles/permissions/{$permission->id}/modifier")
            ->put("/admin/roles/permissions/{$permission->id}", ['libelle' => 'Admin', 'slug' => 'autre.slug'])
            ->assertSessionHasErrors('regle_metier');

        $this->admin()->from("/admin/roles/permissions/{$permission->id}/modifier")
            ->delete("/admin/roles/permissions/{$permission->id}")
            ->assertSessionHasErrors('regle_metier');

        $this->assertSame('voir.administration', $permission->refresh()->slug);
        $this->assertNotSoftDeleted('permissions', ['id' => $permission->id]);
    }

    public function test_suppression_d_une_permission_la_retire_des_roles(): void
    {
        $permission = Permission::create(['libelle' => 'Jetable', 'slug' => 'jetable']);
        $this->roleAdmin->permissions()->attach($permission);

        $this->admin()->delete("/admin/roles/permissions/{$permission->id}")
            ->assertRedirect('/admin/roles?onglet=permissions');

        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);
        $this->assertDatabaseMissing('roles_permissions', ['permission_id' => $permission->id]);
    }

    public function test_desactivation_d_une_permission(): void
    {
        $permission = Permission::create(['libelle' => 'P', 'slug' => 'p']);

        $this->admin()->from('/admin/roles?onglet=permissions')
            ->patch("/admin/roles/permissions/{$permission->id}/statut", ['is_active' => 0])
            ->assertSessionHas('success');

        $this->assertFalse($permission->refresh()->is_active);
    }
}
