<?php

namespace Tests\Feature;

use App\Enums\CategorieUtilisateur;
use App\Models\Application;
use App\Models\Ressource;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ReferentielDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationReferentielsTest extends TestCase
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

    public function test_reserve_a_l_administrateur(): void
    {
        $this->get('/admin/applications')->assertRedirect('/login');

        $agent = User::factory()->create(['service_id' => Service::factory()->create()->id]);
        $this->actingAs($agent)->get('/admin/applications')->assertForbidden();
        $this->actingAs($agent)->get('/admin/ressources')->assertForbidden();
        $this->actingAs($agent)->post('/admin/ressources', ['designation' => 'X'])->assertForbidden();
    }

    public function test_liens_dans_la_sidebar(): void
    {
        $this->admin()->get('/admin/utilisateurs')->assertOk()
            ->assertSee(route('admin.applications.index'))
            ->assertSee(route('admin.ressources.index'));
    }

    // ------------------------------------------------------------------
    // Applications
    // ------------------------------------------------------------------

    public function test_creation_et_modification_d_une_application(): void
    {
        $this->admin()->get('/admin/applications/nouvelle')->assertOk();

        $this->admin()->post('/admin/applications', ['reference' => ' sigrh ', 'nom' => 'SIGRH', 'description' => 'RH'])
            ->assertRedirect('/admin/applications')->assertSessionHas('success');

        $application = Application::where('reference', 'SIGRH')->firstOrFail();
        $this->assertTrue($application->is_active);
        $this->assertSame($this->admin->id, $application->created_by);

        $this->admin()->get("/admin/applications/{$application->id}/modifier")->assertOk();
        $this->admin()->put("/admin/applications/{$application->id}", ['reference' => 'SIGRH', 'nom' => 'SIGRH v2'])
            ->assertSessionHasNoErrors();
        $this->assertSame('SIGRH v2', $application->refresh()->nom);
    }

    public function test_reference_d_application_unique(): void
    {
        Application::factory()->create(['reference' => 'GED']);

        $this->admin()->from('/admin/applications/nouvelle')
            ->post('/admin/applications', ['reference' => 'ged', 'nom' => 'Doublon'])
            ->assertSessionHasErrors('reference');
    }

    public function test_liste_recherche_et_filtre_d_applications(): void
    {
        Application::factory()->create(['nom' => 'Portail assurés']);
        Application::factory()->inactive()->create(['nom' => 'Ancien intranet']);

        $this->admin()->get('/admin/applications')->assertOk()->assertSee('Portail assurés')->assertSee('Ancien intranet');
        $this->admin()->get('/admin/applications?q=Portail')->assertSee('Portail assurés')->assertDontSee('Ancien intranet');
        $this->admin()->get('/admin/applications?statut=inactif')->assertSee('Ancien intranet')->assertDontSee('Portail assurés');
    }

    public function test_desactivation_et_reactivation_d_une_application(): void
    {
        $application = Application::factory()->create();

        $this->admin()->from('/admin/applications')
            ->patch("/admin/applications/{$application->id}/statut", ['is_active' => 0])->assertSessionHas('success');
        $this->assertFalse($application->refresh()->is_active);

        $this->admin()->from('/admin/applications')
            ->patch("/admin/applications/{$application->id}/statut", ['is_active' => 1]);
        $this->assertTrue($application->refresh()->is_active);
    }

    // ------------------------------------------------------------------
    // Ressources
    // ------------------------------------------------------------------

    public function test_creation_et_modification_d_une_ressource(): void
    {
        $this->admin()->get('/admin/ressources/nouvelle')->assertOk();

        $this->admin()->post('/admin/ressources', [
            'reference' => 'mat-pc', 'designation' => 'Ordinateur portable', 'categorie' => 'matériel',
            'informations_complementaires' => 'Livré avec sacoche',
        ])->assertRedirect('/admin/ressources');

        $ressource = Ressource::where('reference', 'MAT-PC')->firstOrFail();
        $this->assertSame('Matériel', $ressource->categorie); // première lettre en majuscule

        $this->admin()->get("/admin/ressources/{$ressource->id}/modifier")->assertOk()->assertSee('Matériel');
        $this->admin()->put("/admin/ressources/{$ressource->id}", [
            'reference' => 'MAT-PC', 'designation' => 'Ordinateur portable 14"', 'categorie' => 'Matériel',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Ordinateur portable 14"', $ressource->refresh()->designation);
    }

    public function test_champs_obligatoires_d_une_ressource(): void
    {
        $this->admin()->from('/admin/ressources/nouvelle')
            ->post('/admin/ressources', [])
            ->assertSessionHasErrors(['reference', 'designation', 'categorie']);
    }

    public function test_filtre_par_categorie(): void
    {
        Ressource::factory()->create(['designation' => 'Écran 24', 'categorie' => 'Matériel']);
        Ressource::factory()->create(['designation' => 'Accès VPN', 'categorie' => 'Accès']);

        $this->admin()->get('/admin/ressources?categorie=Accès')
            ->assertOk()->assertSee('Accès VPN')->assertDontSee('Écran 24');
    }

    public function test_desactivation_d_une_ressource(): void
    {
        $ressource = Ressource::factory()->create();

        $this->admin()->from('/admin/ressources')
            ->patch("/admin/ressources/{$ressource->id}/statut", ['is_active' => 0])->assertSessionHas('success');

        $this->assertFalse($ressource->refresh()->is_active);
    }

    public function test_pas_de_suppression(): void
    {
        $ressource = Ressource::factory()->create();

        $this->admin()->delete("/admin/ressources/{$ressource->id}")->assertStatus(405);
    }

    public function test_seeder_de_demo_idempotent(): void
    {
        $this->seed(ReferentielDemoSeeder::class);
        $this->seed(ReferentielDemoSeeder::class);

        $this->assertSame(4, Application::count());
        $this->assertSame(6, Ressource::count());
    }
}
