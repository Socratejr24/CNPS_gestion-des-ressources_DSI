<?php

namespace Tests\Feature;

use App\Enums\CategorieUtilisateur;
use App\Models\Departement;
use App\Models\Role;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationOrganisationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->create(['categorie' => CategorieUtilisateur::PRESTATAIRE]);
        $this->admin->roles()->attach(Role::create(['libelle' => Role::ADMINISTRATEUR]));
    }

    private function admin()
    {
        return $this->actingAs($this->admin);
    }

    // ------------------------------------------------------------------
    // Accès et vue d'ensemble
    // ------------------------------------------------------------------

    public function test_reserve_a_l_administrateur(): void
    {
        $this->get('/admin/organisation')->assertRedirect('/login');

        $agent = User::factory()->create(['service_id' => Service::factory()->create()->id]);
        $this->actingAs($agent)->get('/admin/organisation')->assertForbidden();
        $this->actingAs($agent)->post('/admin/organisation/structures', ['code' => 'X', 'libelle' => 'X'])->assertForbidden();
    }

    public function test_vue_d_ensemble_affiche_l_arborescence_et_les_manques(): void
    {
        $service = Service::factory()->create(['libelle' => 'Infrastructure']);
        $departement = $service->departement;
        $chef = User::factory()->create(['service_id' => $service->id, 'nom' => 'Chef', 'prenom' => 'Paul']);
        $service->forceFill(['chef_id' => $chef->id])->save();

        $this->admin()->get('/admin/organisation')
            ->assertOk()
            ->assertSee($departement->structure->libelle)
            ->assertSee($departement->libelle)
            ->assertSee('Infrastructure')
            ->assertSee('Paul Chef')
            ->assertSee('aucun responsable désigné');
    }

    public function test_vue_vide(): void
    {
        $this->admin()->get('/admin/organisation')->assertOk()->assertSee('Aucune structure');
    }

    public function test_lien_organisation_dans_la_sidebar(): void
    {
        $this->admin()->get('/admin/utilisateurs')->assertOk()->assertSee(route('admin.organisation.index'));
    }

    // ------------------------------------------------------------------
    // Création / modification
    // ------------------------------------------------------------------

    public function test_creation_de_la_hierarchie_complete(): void
    {
        $this->admin()->get('/admin/organisation/structures/nouvelle')->assertOk();
        $this->admin()->post('/admin/organisation/structures', ['code' => 'DSI', 'libelle' => 'Direction des SI'])
            ->assertRedirect('/admin/organisation')->assertSessionHas('success');
        $structure = Structure::where('code', 'DSI')->firstOrFail();

        $this->admin()->get("/admin/organisation/departements/nouveau?structure_id={$structure->id}")->assertOk();
        $this->admin()->post('/admin/organisation/departements', [
            'structure_id' => $structure->id, 'code' => 'DEV', 'libelle' => 'Développement',
        ])->assertRedirect('/admin/organisation');
        $departement = Departement::where('code', 'DEV')->firstOrFail();

        $this->admin()->get("/admin/organisation/services/nouveau?departement_id={$departement->id}")->assertOk();
        $this->admin()->post('/admin/organisation/services', [
            'departement_id' => $departement->id, 'code' => 'DEV-IT', 'libelle' => 'Dev IT',
        ])->assertRedirect('/admin/organisation');

        $this->assertDatabaseHas('services', ['code' => 'DEV-IT', 'departement_id' => $departement->id]);
    }

    public function test_code_unique(): void
    {
        Structure::factory()->create(['code' => 'DSI']);

        $this->admin()->from('/admin/organisation/structures/nouvelle')
            ->post('/admin/organisation/structures', ['code' => 'DSI', 'libelle' => 'Doublon'])
            ->assertRedirect('/admin/organisation/structures/nouvelle')
            ->assertSessionHasErrors('code');
    }

    public function test_creation_refusee_sous_un_parent_desactive(): void
    {
        $structure = Structure::factory()->create(['is_active' => false]);

        $this->admin()->from('/admin/organisation/departements/nouveau')
            ->post('/admin/organisation/departements', ['structure_id' => $structure->id, 'code' => 'D', 'libelle' => 'D'])
            ->assertSessionHasErrors('regle_metier');
    }

    public function test_modification_en_conservant_son_code(): void
    {
        $service = Service::factory()->create(['code' => 'RES']);

        $this->admin()->get("/admin/organisation/services/{$service->id}/modifier")->assertOk();
        $this->admin()->from("/admin/organisation/services/{$service->id}/modifier")
            ->put("/admin/organisation/services/{$service->id}", [
                'departement_id' => $service->departement_id, 'code' => 'RES', 'libelle' => 'Réseaux et sécurité',
            ])
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('Réseaux et sécurité', $service->refresh()->libelle);
    }

    public function test_pages_de_modification_structure_et_departement(): void
    {
        $departement = Departement::factory()->create();

        $this->admin()->get("/admin/organisation/structures/{$departement->structure_id}/modifier")->assertOk();
        $this->admin()->get("/admin/organisation/departements/{$departement->id}/modifier")->assertOk()
            ->assertSee('Aucun responsable');
    }

    // ------------------------------------------------------------------
    // Statuts
    // ------------------------------------------------------------------

    public function test_desactivation_refusee_si_enfants_actifs(): void
    {
        $service = Service::factory()->create();
        $structure = $service->departement->structure;

        $this->admin()->from('/admin/organisation')
            ->patch("/admin/organisation/structures/{$structure->id}/statut", ['is_active' => 0])
            ->assertRedirect('/admin/organisation')
            ->assertSessionHasErrors('regle_metier');

        $this->assertTrue($structure->refresh()->is_active);
    }

    public function test_desactivation_puis_reactivation_d_un_service(): void
    {
        $service = Service::factory()->create();

        $this->admin()->from('/admin/organisation')
            ->patch("/admin/organisation/services/{$service->id}/statut", ['is_active' => 0])
            ->assertSessionHas('success');
        $this->assertFalse($service->refresh()->is_active);

        $this->admin()->from('/admin/organisation')
            ->patch("/admin/organisation/services/{$service->id}/statut", ['is_active' => 1])
            ->assertSessionHas('success');
        $this->assertTrue($service->refresh()->is_active);
    }

    // ------------------------------------------------------------------
    // Désignation N+1 / N+2
    // ------------------------------------------------------------------

    public function test_designation_du_chef_de_service(): void
    {
        $service = Service::factory()->create();
        $membre = User::factory()->create(['service_id' => $service->id, 'nom' => 'Membre']);

        $this->admin()->get("/admin/organisation/services/{$service->id}/modifier")
            ->assertOk()->assertSee($membre->nomComplet());

        $this->admin()->from("/admin/organisation/services/{$service->id}/modifier")
            ->put("/admin/organisation/services/{$service->id}/chef", ['utilisateur_id' => $membre->id])
            ->assertSessionHas('success');
        $this->assertSame($membre->id, $service->refresh()->chef_id);

        $this->admin()->from("/admin/organisation/services/{$service->id}/modifier")
            ->put("/admin/organisation/services/{$service->id}/chef", ['utilisateur_id' => ''])
            ->assertSessionHas('success');
        $this->assertNull($service->refresh()->chef_id);
    }

    public function test_chef_hors_du_service_refuse(): void
    {
        $service = Service::factory()->create();
        $externe = User::factory()->create(['service_id' => Service::factory()->create()->id]);

        $this->admin()->from("/admin/organisation/services/{$service->id}/modifier")
            ->put("/admin/organisation/services/{$service->id}/chef", ['utilisateur_id' => $externe->id])
            ->assertSessionHasErrors('regle_metier');

        $this->assertNull($service->refresh()->chef_id);
    }

    public function test_designation_du_responsable_de_departement(): void
    {
        $service = Service::factory()->create();
        $departement = $service->departement;
        $agent = User::factory()->create(['service_id' => $service->id]);

        $this->admin()->get("/admin/organisation/departements/{$departement->id}/modifier")
            ->assertOk()->assertSee($agent->nomComplet());

        $this->admin()->from("/admin/organisation/departements/{$departement->id}/modifier")
            ->put("/admin/organisation/departements/{$departement->id}/responsable", ['utilisateur_id' => $agent->id])
            ->assertSessionHas('success');

        $this->assertSame($agent->id, $departement->refresh()->responsable_id);
    }
}
