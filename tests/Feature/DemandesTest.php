<?php

namespace Tests\Feature;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\Departement;
use App\Models\Ressource;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 2 du module Demandes : brouillons, soumission et circuit (SFD §6, §7).
 */
class DemandesTest extends TestCase
{
    use RefreshDatabase;

    private Role $demandeurRole;

    private Role $receveurRole;

    private Service $service;          // service du demandeur

    private User $agent;               // demandeur « classique »

    private User $chef;                // N+1

    private User $responsable;         // N+2

    private Service $serviceDestinataire;

    private User $receveur;

    private Ressource $ressource;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->demandeurRole = Role::create(['libelle' => Role::DEMANDEUR]);
        $this->receveurRole = Role::create(['libelle' => Role::RECEVEUR]);

        $departement = Departement::factory()->create();
        $this->service = Service::factory()->create(['departement_id' => $departement->id, 'libelle' => 'Dev IT']);
        $this->agent = $this->membre($this->service, 'Agent', 'Kouassi');
        $this->chef = $this->membre($this->service, 'Chef', 'Aya');
        $this->responsable = $this->membre($this->service, 'Responsable', 'Konan');
        $this->service->forceFill(['chef_id' => $this->chef->id])->save();
        $departement->forceFill(['responsable_id' => $this->responsable->id])->save();

        $this->serviceDestinataire = Service::factory()->create(['libelle' => 'Support']);
        $this->receveur = $this->membre($this->serviceDestinataire, 'Receveur', 'Issa');
        $this->receveur->roles()->attach($this->receveurRole);

        $this->ressource = Ressource::factory()->create(['designation' => 'Ordinateur portable']);
    }

    private function membre(Service $service, string $nom, string $prenom): User
    {
        $user = User::factory()->create(['service_id' => $service->id, 'nom' => $nom, 'prenom' => $prenom]);
        $user->roles()->attach($this->demandeurRole);

        return $user;
    }

    private function donnees(array $surcharge = []): array
    {
        return array_replace([
            'action' => 'brouillon',
            'objet' => 'Poste pour un nouvel arrivant',
            'description' => 'Arrivée prévue lundi.',
            'destination_type' => 'SERVICE',
            'destination_service_id' => $this->serviceDestinataire->id,
            'lignes' => [['ressource_id' => $this->ressource->id, 'quantite' => 2]],
        ], $surcharge);
    }

    private function derniereDemande(): Demande
    {
        return Demande::latest('id')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Brouillons
    // ------------------------------------------------------------------

    public function test_page_de_creation_affiche_le_circuit(): void
    {
        $this->actingAs($this->agent)->get('/demandes/nouvelle')
            ->assertOk()
            ->assertSee('Aya Chef')          // N+1
            ->assertSee('Konan Responsable') // N+2
            ->assertSee('Ordinateur portable');
    }

    public function test_creation_d_un_brouillon(): void
    {
        $this->actingAs($this->agent)->post('/demandes', $this->donnees())->assertRedirect();

        $demande = $this->derniereDemande();
        $this->assertSame(DemandeStatut::BROUILLON, $demande->statut);
        $this->assertSame($this->agent->id, $demande->demandeur_id);
        $this->assertMatchesRegularExpression('/^DEM-\d{4}-00001$/', $demande->reference);
        $this->assertSame(2, $demande->lignes()->first()->quantite_demandee);
        $this->assertSame(ActionDemande::CREATION, $demande->evenements()->first()->action);
    }

    public function test_references_successives(): void
    {
        $this->actingAs($this->agent)->post('/demandes', $this->donnees());
        $this->actingAs($this->agent)->post('/demandes', $this->donnees());

        $this->assertStringEndsWith('-00002', $this->derniereDemande()->reference);
    }

    public function test_brouillon_minimal_accepte(): void
    {
        $this->actingAs($this->agent)
            ->post('/demandes', ['action' => 'brouillon', 'objet' => 'À compléter', 'lignes' => [['ressource_id' => '', 'quantite' => 1]]])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $this->derniereDemande()->lignes()->count());
    }

    public function test_ressource_en_double_refusee(): void
    {
        $this->actingAs($this->agent)->from('/demandes/nouvelle')
            ->post('/demandes', $this->donnees(['lignes' => [
                ['ressource_id' => $this->ressource->id, 'quantite' => 1],
                ['ressource_id' => $this->ressource->id, 'quantite' => 1],
            ]]))
            ->assertSessionHasErrors('lignes.1.ressource_id');
    }

    public function test_modification_et_suppression_d_un_brouillon(): void
    {
        $this->actingAs($this->agent)->post('/demandes', $this->donnees());
        $demande = $this->derniereDemande();

        $this->actingAs($this->agent)->get("/demandes/{$demande->id}/modifier")->assertOk();
        $this->actingAs($this->agent)->put("/demandes/{$demande->id}", $this->donnees(['objet' => 'Objet corrigé']))
            ->assertRedirect("/demandes/{$demande->id}");
        $this->assertSame('Objet corrigé', $demande->refresh()->objet);
        $this->assertTrue($demande->evenements()->where('action', ActionDemande::MODIFICATION)->exists());

        $this->actingAs($this->agent)->delete("/demandes/{$demande->id}")->assertRedirect('/demandes/mes-demandes');
        $this->assertModelMissing($demande);
    }

    // ------------------------------------------------------------------
    // Soumission et circuit (SFD §7.4)
    // ------------------------------------------------------------------

    public function test_agent_classique_passe_par_le_n1(): void
    {
        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre']));

        $demande = $this->derniereDemande();
        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->statut);
        $this->assertSame($this->service->id, $demande->service_demandeur_id);
        $this->assertNotNull($demande->soumise_le);
        $this->assertTrue($demande->evenements()->where('action', ActionDemande::SOUMISSION)->exists());
    }

    public function test_chef_de_service_passe_directement_au_n2(): void
    {
        $this->actingAs($this->chef)->post('/demandes', $this->donnees(['action' => 'soumettre']));

        $this->assertSame(DemandeStatut::EN_ATTENTE_N2, $this->derniereDemande()->statut);
    }

    public function test_responsable_de_departement_transmet_directement(): void
    {
        $this->actingAs($this->responsable)->post('/demandes', $this->donnees(['action' => 'soumettre']));

        $demande = $this->derniereDemande();
        $this->assertSame(DemandeStatut::EN_ATTENTE_TRAITEMENT, $demande->statut);
        $this->assertTrue($demande->evenements()->where('action', ActionDemande::TRANSMISSION)->exists());
    }

    public function test_cumul_n1_n2_une_seule_validation_prevue(): void
    {
        $this->service->departement->forceFill(['responsable_id' => $this->chef->id])->save();

        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre']));
        $demande = $this->derniereDemande();

        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->statut);
        $this->actingAs($this->agent)->get("/demandes/{$demande->id}")
            ->assertOk()->assertSee('même personne que le N+1');
    }

    public function test_soumission_bloquee_sans_chef_de_service(): void
    {
        $this->service->forceFill(['chef_id' => null])->save();

        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre']))
            ->assertRedirect()->assertSessionHasErrors('regle_metier');

        $this->assertSame(DemandeStatut::BROUILLON, $this->derniereDemande()->statut);
    }

    public function test_soumission_bloquee_sans_responsable_de_departement(): void
    {
        $this->service->departement->forceFill(['responsable_id' => null])->save();

        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre']))
            ->assertSessionHasErrors('regle_metier');

        $this->assertSame(DemandeStatut::BROUILLON, $this->derniereDemande()->statut);
    }

    public function test_soumission_bloquee_si_incomplete(): void
    {
        $this->actingAs($this->agent)->post('/demandes', [
            'action' => 'soumettre', 'objet' => 'Incomplète', 'lignes' => [],
        ])->assertSessionHasErrors('regle_metier');

        $this->assertSame(DemandeStatut::BROUILLON, $this->derniereDemande()->statut);
        $this->assertStringContainsString('au moins une ressource', session('errors')->first('regle_metier'));
    }

    public function test_soumission_bloquee_si_destination_sans_receveur(): void
    {
        $vide = Service::factory()->create();

        $this->actingAs($this->agent)->post('/demandes', $this->donnees([
            'action' => 'soumettre', 'destination_service_id' => $vide->id,
        ]))->assertSessionHasErrors('regle_metier');

        $this->assertSame(DemandeStatut::BROUILLON, $this->derniereDemande()->statut);
    }

    public function test_destinataire_utilisateur_doit_etre_receveur(): void
    {
        $this->actingAs($this->agent)->from('/demandes/nouvelle')->post('/demandes', $this->donnees([
            'destination_type' => 'UTILISATEUR', 'destination_utilisateur_id' => $this->chef->id,
        ]))->assertSessionHasErrors('regle_metier');

        $this->actingAs($this->agent)->post('/demandes', $this->donnees([
            'action' => 'soumettre', 'destination_type' => 'UTILISATEUR', 'destination_utilisateur_id' => $this->receveur->id,
        ]));
        $demande = $this->derniereDemande();
        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->statut);
        $this->assertNull($demande->destination_service_id); // une seule destination (RG-14)
    }

    // ------------------------------------------------------------------
    // Après soumission et droits d'accès (RG-34)
    // ------------------------------------------------------------------

    public function test_demande_soumise_n_est_plus_modifiable(): void
    {
        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre']));
        $demande = $this->derniereDemande();

        $this->actingAs($this->agent)->get("/demandes/{$demande->id}/modifier")->assertForbidden();
        $this->actingAs($this->agent)->put("/demandes/{$demande->id}", $this->donnees())->assertForbidden();
        $this->actingAs($this->agent)->delete("/demandes/{$demande->id}")->assertForbidden();
        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/soumettre")->assertForbidden();
    }

    public function test_consultation_reservee_au_demandeur_et_a_l_admin(): void
    {
        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre']));
        $demande = $this->derniereDemande();

        $this->actingAs($this->agent)->get("/demandes/{$demande->id}")
            ->assertOk()->assertSee($demande->reference)->assertSee('Ordinateur portable')->assertSee('Soumission');

        $autre = $this->membre(Service::factory()->create(), 'Autre', 'Agent');
        $this->actingAs($autre)->get("/demandes/{$demande->id}")->assertForbidden();

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['libelle' => Role::ADMINISTRATEUR]));
        $this->actingAs($admin)->get("/demandes/{$demande->id}")->assertOk();
    }

    public function test_mes_demandes_ne_liste_que_les_siennes(): void
    {
        Demande::factory()->pour($this->agent)->create(['objet' => 'Ma demande']);
        Demande::factory()->pour($this->chef)->create(['objet' => 'Demande du chef']);

        $this->actingAs($this->agent)->get('/demandes/mes-demandes')
            ->assertOk()->assertSee('Ma demande')->assertDontSee('Demande du chef');
    }

    public function test_filtre_par_statut(): void
    {
        Demande::factory()->pour($this->agent)->create(['objet' => 'Brouillon A']);
        $this->actingAs($this->agent)->post('/demandes', $this->donnees(['action' => 'soumettre', 'objet' => 'Soumise B']));

        $this->actingAs($this->agent)->get('/demandes/mes-demandes?statut=EN_ATTENTE_N1')
            ->assertOk()->assertSee('Soumise B')->assertDontSee('Brouillon A');
    }

    public function test_lien_nouvelle_demande_dans_la_sidebar(): void
    {
        $this->actingAs($this->agent)->get('/demandes/mes-demandes')->assertSee(route('demandes.create'));
    }
}
