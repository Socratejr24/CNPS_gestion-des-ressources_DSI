<?php

namespace Tests\Feature;

use App\Enums\CategorieUtilisateur;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministrationUtilisateursTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Role $roleAdmin;

    private Role $roleDemandeur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->roleAdmin = Role::create(['libelle' => Role::ADMINISTRATEUR]);
        $this->roleDemandeur = Role::create(['libelle' => Role::DEMANDEUR]);

        $this->admin = User::factory()->create(['categorie' => CategorieUtilisateur::PRESTATAIRE]);
        $this->admin->roles()->attach($this->roleAdmin);
    }

    private function agent(array $attributs = []): User
    {
        return User::factory()->create($attributs + ['categorie' => CategorieUtilisateur::AGENT]);
    }

    private function donneesPrestataire(array $surcharge = []): array
    {
        return $surcharge + [
            'nom' => 'Kouamé',
            'prenom' => 'Ange',
            'email' => 'ange.kouame@presta.ci',
            'identifiant' => 'akouame',
            'fonction' => 'Consultant',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ];
    }

    // ------------------------------------------------------------------
    // Accès
    // ------------------------------------------------------------------

    public function test_invite_redirige_vers_la_connexion(): void
    {
        $this->get('/admin/utilisateurs')->assertRedirect('/login');
    }

    public function test_non_administrateur_refuse(): void
    {
        $demandeur = $this->agent(['service_id' => Service::factory()->create()->id]);
        $demandeur->roles()->attach($this->roleDemandeur);

        $this->actingAs($demandeur)->get('/admin/utilisateurs')->assertForbidden();
        $this->actingAs($demandeur)->post('/admin/utilisateurs', $this->donneesPrestataire())->assertForbidden();
    }

    public function test_lien_administration_dans_la_sidebar_pour_l_admin(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')
            ->assertOk()->assertSee(route('admin.utilisateurs.index'));
    }

    // ------------------------------------------------------------------
    // Liste
    // ------------------------------------------------------------------

    public function test_liste_et_recherche(): void
    {
        $this->agent(['nom' => 'Yao', 'prenom' => 'Konan']);
        $this->agent(['nom' => 'Bamba', 'prenom' => 'Awa']);

        $this->actingAs($this->admin)->get('/admin/utilisateurs')
            ->assertOk()->assertSee('Konan Yao')->assertSee('Awa Bamba');

        $this->actingAs($this->admin)->get('/admin/utilisateurs?q=Bamba')
            ->assertOk()->assertSee('Awa Bamba')->assertDontSee('Konan Yao');
    }

    public function test_filtre_sans_affectation(): void
    {
        $this->agent(['nom' => 'Affecte', 'service_id' => Service::factory()->create()->id]);
        $this->agent(['nom' => 'Orphelin']);

        $this->actingAs($this->admin)->get('/admin/utilisateurs?affectation=sans')
            ->assertOk()->assertSee('Orphelin')->assertDontSee('Affecte');
    }

    // ------------------------------------------------------------------
    // Création / modification
    // ------------------------------------------------------------------

    public function test_creation_d_un_prestataire(): void
    {
        $this->actingAs($this->admin)->get('/admin/utilisateurs/nouveau')->assertOk();

        $response = $this->actingAs($this->admin)->post('/admin/utilisateurs', $this->donneesPrestataire());

        $cree = User::where('identifiant', 'akouame')->firstOrFail();
        $response->assertRedirect(route('admin.utilisateurs.show', $cree))->assertSessionHas('success');
        $this->assertSame(CategorieUtilisateur::PRESTATAIRE, $cree->categorie);
        $this->assertTrue(Hash::check('Secret123', $cree->password));
    }

    public function test_creation_exige_la_confirmation_du_mot_de_passe(): void
    {
        $this->actingAs($this->admin)->from('/admin/utilisateurs/nouveau')
            ->post('/admin/utilisateurs', $this->donneesPrestataire(['password_confirmation' => 'Autre1234']))
            ->assertRedirect('/admin/utilisateurs/nouveau')
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('utilisateurs', ['identifiant' => 'akouame']);
    }

    public function test_modification_d_un_prestataire_sans_changer_le_mot_de_passe(): void
    {
        $presta = User::factory()->create(['categorie' => CategorieUtilisateur::PRESTATAIRE]);
        $ancienHash = $presta->password;

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$presta->id}")
            ->put("/admin/utilisateurs/{$presta->id}", [
                'nom' => 'Nouveau', 'prenom' => $presta->prenom, 'email' => $presta->email,
                'identifiant' => $presta->identifiant, 'password' => '', 'password_confirmation' => '',
            ])
            ->assertRedirect("/admin/utilisateurs/{$presta->id}")
            ->assertSessionHasNoErrors();

        $presta->refresh();
        $this->assertSame('Nouveau', $presta->nom);
        $this->assertSame($ancienHash, $presta->password);
    }

    public function test_informations_d_un_agent_non_modifiables(): void
    {
        $agent = $this->agent(['nom' => 'Ldap']);

        $this->actingAs($this->admin)->get("/admin/utilisateurs/{$agent->id}")
            ->assertOk()->assertSee('annuaire LDAP');

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->put("/admin/utilisateurs/{$agent->id}", ['nom' => 'Pirate'])
            ->assertSessionHasErrors('regle_metier');

        $this->assertSame('Ldap', $agent->refresh()->nom);
    }

    // ------------------------------------------------------------------
    // Affectation, rôles, statut
    // ------------------------------------------------------------------

    public function test_affectation_et_desaffectation(): void
    {
        $agent = $this->agent();
        $service = Service::factory()->create();

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->put("/admin/utilisateurs/{$agent->id}/affectation", ['service_id' => $service->id])
            ->assertSessionHas('success');
        $this->assertSame($service->id, $agent->refresh()->service_id);

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->put("/admin/utilisateurs/{$agent->id}/affectation", ['service_id' => ''])
            ->assertSessionHas('success');
        $this->assertNull($agent->refresh()->service_id);
    }

    public function test_attribution_des_roles(): void
    {
        $agent = $this->agent();
        $agent->roles()->attach($this->roleDemandeur);
        $receveur = Role::create(['libelle' => Role::RECEVEUR]);

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->put("/admin/utilisateurs/{$agent->id}/roles", ['role_ids' => [$receveur->id]])
            ->assertSessionHas('success');

        $this->assertEqualsCanonicalizing([$receveur->id], $agent->roles()->pluck('roles.id')->all());

        // Aucune case cochée : tous les rôles sont retirés.
        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->put("/admin/utilisateurs/{$agent->id}/roles", [])
            ->assertSessionHas('success');
        $this->assertCount(0, $agent->roles()->get());
    }

    public function test_l_admin_ne_peut_pas_se_retirer_son_role(): void
    {
        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$this->admin->id}")
            ->put("/admin/utilisateurs/{$this->admin->id}/roles", ['role_ids' => [$this->roleDemandeur->id]])
            ->assertSessionHasErrors('regle_metier');

        $this->assertTrue($this->admin->refresh()->estAdministrateur());
    }

    public function test_desactivation_et_reactivation(): void
    {
        $agent = $this->agent();

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->patch("/admin/utilisateurs/{$agent->id}/statut", ['is_active' => '0'])
            ->assertSessionHas('success');
        $this->assertFalse($agent->refresh()->is_active);

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$agent->id}")
            ->patch("/admin/utilisateurs/{$agent->id}/statut", ['is_active' => '1'])
            ->assertSessionHas('success');
        $this->assertTrue($agent->refresh()->is_active);
    }

    public function test_l_admin_ne_peut_pas_se_desactiver(): void
    {
        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$this->admin->id}")
            ->patch("/admin/utilisateurs/{$this->admin->id}/statut", ['is_active' => '0'])
            ->assertSessionHasErrors('regle_metier');

        $this->assertTrue($this->admin->refresh()->is_active);
    }

    public function test_desactivation_d_un_chef_de_service_refusee_avec_message(): void
    {
        $service = Service::factory()->create(['libelle' => 'Réseaux']);
        $chef = $this->agent(['service_id' => $service->id]);
        $service->forceFill(['chef_id' => $chef->id])->save();

        $this->actingAs($this->admin)->get("/admin/utilisateurs/{$chef->id}")
            ->assertOk()->assertSee('Chef du service');

        $this->actingAs($this->admin)->from("/admin/utilisateurs/{$chef->id}")
            ->patch("/admin/utilisateurs/{$chef->id}/statut", ['is_active' => '0'])
            ->assertSessionHasErrors('regle_metier');

        $this->assertTrue($chef->refresh()->is_active);
    }
}
