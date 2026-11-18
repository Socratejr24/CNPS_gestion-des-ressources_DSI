<?php

namespace Tests\Feature;

use App\Models\Departement;
use App\Models\Role;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OrganisationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateurDans(Service $service, array $attributs = []): User
    {
        return User::factory()->create($attributs + ['service_id' => $service->id]);
    }

    // ------------------------------------------------------------------
    // CRUD de base
    // ------------------------------------------------------------------

    public function test_creation_hierarchie_complete(): void
    {
        $structureId = $this->postJson('/test-api/structures', ['code' => 'DSI', 'libelle' => 'DSI'])
            ->assertCreated()->assertJsonPath('data.is_active', true)->json('data.id');

        $departementId = $this->postJson('/test-api/departements', [
            'structure_id' => $structureId, 'code' => 'DEV', 'libelle' => 'Développement',
        ])->assertCreated()->assertJsonPath('data.structure.code', 'DSI')->json('data.id');

        $this->postJson('/test-api/services', [
            'departement_id' => $departementId, 'code' => 'DEV-IT', 'libelle' => 'Dev IT',
        ])->assertCreated()->assertJsonPath('data.departement.structure.code', 'DSI');
    }

    public function test_code_unique(): void
    {
        Structure::factory()->create(['code' => 'DSI']);

        $this->postJson('/test-api/structures', ['code' => 'DSI', 'libelle' => 'Doublon'])
            ->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_entite_inexistante_renvoie_404_json(): void
    {
        $this->get('/test-api/services/999')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_pas_de_suppression_des_entites_organisationnelles(): void
    {
        $service = Service::factory()->create();

        $this->deleteJson("/test-api/services/{$service->id}")->assertStatus(405);
    }

    // ------------------------------------------------------------------
    // Désactivation (SFD §2.2)
    // ------------------------------------------------------------------

    public function test_impossible_de_creer_sous_une_entite_desactivee(): void
    {
        $departement = Departement::factory()->inactive()->create();

        $this->postJson('/test-api/services', [
            'departement_id' => $departement->id, 'code' => 'X', 'libelle' => 'X',
        ])->assertStatus(409);
    }

    public function test_desactivation_refusee_si_enfants_actifs(): void
    {
        $service = Service::factory()->create();

        $this->patchJson("/test-api/departements/{$service->departement_id}/toggle-status", ['is_active' => false])
            ->assertStatus(409);

        $this->patchJson("/test-api/services/{$service->id}/toggle-status", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);

        $this->patchJson("/test-api/departements/{$service->departement_id}/toggle-status", ['is_active' => false])
            ->assertOk();
    }

    public function test_desactivation_service_refusee_si_utilisateurs_actifs(): void
    {
        $service = Service::factory()->create();
        $this->utilisateurDans($service);

        $this->patchJson("/test-api/services/{$service->id}/toggle-status", ['is_active' => false])
            ->assertStatus(409);
    }

    public function test_reactivation_refusee_si_parent_inactif(): void
    {
        $departement = Departement::factory()->inactive()->create();
        $service = Service::factory()->inactive()->create(['departement_id' => $departement->id]);

        $this->patchJson("/test-api/services/{$service->id}/toggle-status", ['is_active' => true])
            ->assertStatus(409);
    }

    // ------------------------------------------------------------------
    // Responsables hiérarchiques (RG-06, RG-15, RG-16)
    // ------------------------------------------------------------------

    public function test_designation_chef_de_service(): void
    {
        $service = Service::factory()->create();
        $chef = $this->utilisateurDans($service);

        $this->putJson("/test-api/services/{$service->id}/chef", ['utilisateur_id' => $chef->id])
            ->assertOk()->assertJsonPath('data.chef.id', $chef->id);

        $this->assertTrue($chef->fresh()->estChefDeSonService());
    }

    public function test_chef_doit_appartenir_au_service(): void
    {
        $service = Service::factory()->create();
        $autre = $this->utilisateurDans(Service::factory()->create());

        $this->putJson("/test-api/services/{$service->id}/chef", ['utilisateur_id' => $autre->id])
            ->assertStatus(409);
    }

    public function test_chef_doit_etre_actif(): void
    {
        $service = Service::factory()->create();
        $inactif = $this->utilisateurDans($service, ['is_active' => false]);

        $this->putJson("/test-api/services/{$service->id}/chef", ['utilisateur_id' => $inactif->id])
            ->assertStatus(409);
    }

    public function test_retrait_du_chef(): void
    {
        $service = Service::factory()->create();
        $service->forceFill(['chef_id' => $this->utilisateurDans($service)->id])->save();

        $this->putJson("/test-api/services/{$service->id}/chef", ['utilisateur_id' => null])
            ->assertOk()->assertJsonPath('data.chef', null);
    }

    public function test_responsable_doit_appartenir_a_un_service_du_departement(): void
    {
        $service = Service::factory()->create();
        $membre = $this->utilisateurDans($service);
        $externe = $this->utilisateurDans(Service::factory()->create());

        $this->putJson("/test-api/departements/{$service->departement_id}/responsable", ['utilisateur_id' => $externe->id])
            ->assertStatus(409);

        $this->putJson("/test-api/departements/{$service->departement_id}/responsable", ['utilisateur_id' => $membre->id])
            ->assertOk()->assertJsonPath('data.responsable.id', $membre->id);

        $this->assertTrue($membre->fresh()->estResponsableDeSonDepartement());
    }

    public function test_n1_et_n2_du_demandeur_sont_deduits_de_son_service(): void
    {
        $service = Service::factory()->create();
        $chef = $this->utilisateurDans($service);
        $responsable = $this->utilisateurDans($service);
        $agent = $this->utilisateurDans($service);
        $service->forceFill(['chef_id' => $chef->id])->save();
        $service->departement->forceFill(['responsable_id' => $responsable->id])->save();

        $agent->refresh();
        $this->assertTrue($agent->chefDeService()->is($chef));
        $this->assertTrue($agent->responsableDepartement()->is($responsable));
        $this->assertTrue($agent->structure()->is($service->departement->structure));
    }

    // ------------------------------------------------------------------
    // Affectations (RG-03, RG-11)
    // ------------------------------------------------------------------

    public function test_affectation_utilisateur(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->getJson("/test-api/utilisateurs/{$user->id}")->assertJsonPath('data.affectation_en_attente', true);

        $this->putJson("/test-api/utilisateurs/{$user->id}/affectation", ['service_id' => $service->id])
            ->assertOk()
            ->assertJsonPath('data.affectation_en_attente', false)
            ->assertJsonPath('data.service.id', $service->id)
            ->assertJsonPath('data.departement.id', $service->departement_id);
    }

    public function test_service_id_non_assignable_via_modification(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->putJson("/test-api/utilisateurs/{$user->id}", ['service_id' => $service->id, 'nom' => 'X'])->assertOk();

        $this->assertNull($user->fresh()->service_id);
    }

    public function test_affectation_a_un_service_inactif_refusee(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->inactive()->create();

        $this->putJson("/test-api/utilisateurs/{$user->id}/affectation", ['service_id' => $service->id])
            ->assertStatus(409);
    }

    public function test_changement_de_service_refuse_pour_un_chef(): void
    {
        $service = Service::factory()->create();
        $chef = $this->utilisateurDans($service);
        $service->forceFill(['chef_id' => $chef->id])->save();

        $this->putJson("/test-api/utilisateurs/{$chef->id}/affectation", ['service_id' => Service::factory()->create()->id])
            ->assertStatus(409);
    }

    public function test_responsable_peut_changer_de_service_dans_son_departement_seulement(): void
    {
        $service = Service::factory()->create();
        $memeDepartement = Service::factory()->create(['departement_id' => $service->departement_id]);
        $responsable = $this->utilisateurDans($service);
        $service->departement->forceFill(['responsable_id' => $responsable->id])->save();

        $this->putJson("/test-api/utilisateurs/{$responsable->id}/affectation", ['service_id' => $memeDepartement->id])
            ->assertOk();

        $this->putJson("/test-api/utilisateurs/{$responsable->id}/affectation", ['service_id' => Service::factory()->create()->id])
            ->assertStatus(409);
    }

    // ------------------------------------------------------------------
    // Utilisateurs & rôles (SFD §3)
    // ------------------------------------------------------------------

    public function test_creation_prestataire(): void
    {
        $this->postJson('/test-api/utilisateurs', [
            'nom' => 'Issa', 'prenom' => 'Bamba', 'email' => 'b@ex.ci',
            'identifiant' => 'bamba', 'password' => 'secret123',
        ])->assertCreated()
            ->assertJsonPath('data.categorie', 'PRESTATAIRE')
            ->assertJsonMissingPath('data.password');
    }

    public function test_desactivation_refusee_pour_un_responsable(): void
    {
        $service = Service::factory()->create();
        $chef = $this->utilisateurDans($service);
        $service->forceFill(['chef_id' => $chef->id])->save();

        $this->patchJson("/test-api/utilisateurs/{$chef->id}/toggle-status", ['is_active' => false])
            ->assertStatus(409);
    }

    public function test_attribution_des_roles(): void
    {
        $user = User::factory()->create();
        $demandeur = Role::create(['libelle' => Role::DEMANDEUR]);
        $receveur = Role::create(['libelle' => Role::RECEVEUR]);
        $inactif = Role::create(['libelle' => 'Ancien', 'is_active' => false]);

        $this->putJson("/test-api/utilisateurs/{$user->id}/roles", ['role_ids' => [$demandeur->id, $receveur->id]])
            ->assertOk()->assertJsonCount(2, 'data.roles');

        $this->assertTrue($user->fresh()->hasRole(Role::RECEVEUR));

        $this->putJson("/test-api/utilisateurs/{$user->id}/roles", ['role_ids' => [$inactif->id]])
            ->assertStatus(409);

        $this->putJson("/test-api/utilisateurs/{$user->id}/roles", ['role_ids' => [$demandeur->id]])
            ->assertOk()->assertJsonCount(1, 'data.roles');
    }

    public function test_seeder_de_demo(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(OrganisationDemoSeeder::class);
        $this->seed(OrganisationDemoSeeder::class); // idempotent

        $kouassi = User::where('email', 'kouassi.adou@cnps.ci')->first();
        $this->assertSame('koffi.aya@cnps.ci', $kouassi->chefDeService()->email);
        $this->assertSame('konan.yao@cnps.ci', $kouassi->responsableDepartement()->email);
        $this->assertTrue(User::where('email', 'admin@cnps.ci')->first()->estAdministrateur());
    }
}
