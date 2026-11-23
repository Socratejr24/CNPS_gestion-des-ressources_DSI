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
 * Étape 3 du module Demandes : validation N+1 / N+2 et compléments (SFD §7, §10).
 */
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private Role $demandeurRole;

    private Departement $departement;

    private Service $service;

    private User $agent;

    private User $chef;

    private User $responsable;

    private Service $serviceDestinataire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->demandeurRole = Role::create(['libelle' => Role::DEMANDEUR]);
        $receveurRole = Role::create(['libelle' => Role::RECEVEUR]);

        $this->departement = Departement::factory()->create();
        $this->service = Service::factory()->create(['departement_id' => $this->departement->id]);
        $this->agent = $this->membre($this->service, 'Agent');
        $this->chef = $this->membre($this->service, 'Chef');
        // Le N+2 est affecté à un autre service du même département.
        $this->responsable = $this->membre(Service::factory()->create(['departement_id' => $this->departement->id]), 'Responsable');
        $this->service->forceFill(['chef_id' => $this->chef->id])->save();
        $this->departement->forceFill(['responsable_id' => $this->responsable->id])->save();

        $this->serviceDestinataire = Service::factory()->create();
        $this->membre($this->serviceDestinataire, 'Receveur')->roles()->attach($receveurRole);
    }

    private function membre(Service $service, string $nom): User
    {
        $user = User::factory()->create(['service_id' => $service->id, 'nom' => $nom]);
        $user->roles()->attach($this->demandeurRole);

        return $user;
    }

    /** Demande soumise par l'agent (statut EN_ATTENTE_N1). */
    private function demandeSoumise(?User $demandeur = null): Demande
    {
        $this->actingAs($demandeur ?? $this->agent)->post('/demandes', [
            'action' => 'soumettre',
            'objet' => 'Écran supplémentaire',
            'description' => 'Double écran pour le développement.',
            'destination_type' => 'SERVICE',
            'destination_service_id' => $this->serviceDestinataire->id,
            'lignes' => [['ressource_id' => Ressource::factory()->create()->id, 'quantite' => 1]],
        ]);

        return Demande::latest('id')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Écran « Demandes à valider »
    // ------------------------------------------------------------------

    public function test_le_n1_voit_la_demande_a_valider_mais_pas_le_n2(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->get('/validations')->assertOk()->assertSee($demande->reference);
        $this->actingAs($this->responsable)->get('/validations')->assertOk()->assertDontSee($demande->reference);
        $this->actingAs($this->agent)->get('/validations')->assertOk()->assertDontSee($demande->reference);
    }

    public function test_entree_de_menu_reservee_aux_validateurs(): void
    {
        $this->actingAs($this->chef)->get('/dashboard')->assertSee(route('validations.index'));
        $this->actingAs($this->agent)->get('/dashboard')->assertDontSee(route('validations.index'));
    }

    public function test_le_valideur_peut_consulter_la_demande(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->get("/demandes/{$demande->id}")->assertOk()->assertSee('Votre décision');
        $this->actingAs($this->responsable)->get("/demandes/{$demande->id}")->assertForbidden(); // pas encore son tour
    }

    // ------------------------------------------------------------------
    // Validation (SFD §7.2, §7.3)
    // ------------------------------------------------------------------

    public function test_circuit_complet_n1_puis_n2_puis_transmission(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider", ['commentaire' => 'OK pour moi'])
            ->assertRedirect('/validations');
        $this->assertSame(DemandeStatut::EN_ATTENTE_N2, $demande->refresh()->statut);

        $this->actingAs($this->responsable)->get('/validations')->assertSee($demande->reference);
        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/valider");

        $demande->refresh();
        $this->assertSame(DemandeStatut::EN_ATTENTE_TRAITEMENT, $demande->statut);
        $this->assertSame(2, $demande->evenements()->where('action', ActionDemande::VALIDATION)->count());
        $this->assertTrue($demande->evenements()->where('action', ActionDemande::TRANSMISSION)->exists());
        $this->assertSame('OK pour moi', $demande->evenements()->where('niveau', 'N1')->value('commentaire'));
    }

    public function test_le_n2_ne_peut_pas_valider_avant_le_n1(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/valider")->assertForbidden();
        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->refresh()->statut);
    }

    public function test_un_tiers_ne_peut_pas_decider(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/valider")->assertForbidden();
        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/rejeter", ['motif' => 'x'])->assertForbidden();
    }

    public function test_cumul_n1_n2_une_seule_validation(): void
    {
        $this->departement->forceFill(['responsable_id' => $this->chef->id])->save();
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider");

        $demande->refresh();
        $this->assertSame(DemandeStatut::EN_ATTENTE_TRAITEMENT, $demande->statut);
        $this->assertTrue($demande->evenements()->where('action', ActionDemande::VALIDATION)->where('niveau', 'N2')->exists());
    }

    public function test_validation_n1_bloquee_si_n2_devenu_vacant(): void
    {
        $demande = $this->demandeSoumise();
        $this->departement->forceFill(['responsable_id' => null])->save();

        $this->actingAs($this->chef)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/valider")
            ->assertSessionHasErrors('regle_metier');

        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->refresh()->statut);
    }

    public function test_nouveau_chef_designe_apres_soumission_peut_valider(): void
    {
        $demande = $this->demandeSoumise();
        $nouveauChef = $this->membre($this->service, 'NouveauChef');
        $this->service->forceFill(['chef_id' => $nouveauChef->id])->save();

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider")->assertForbidden();
        $this->actingAs($nouveauChef)->post("/demandes/{$demande->id}/valider")->assertRedirect();
        $this->assertSame(DemandeStatut::EN_ATTENTE_N2, $demande->refresh()->statut);
    }

    // ------------------------------------------------------------------
    // Rejet (SFD §7.5, RG-21)
    // ------------------------------------------------------------------

    public function test_rejet_avec_motif_obligatoire(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/rejeter", ['motif' => ''])
            ->assertSessionHasErrors('motif');
        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->refresh()->statut);

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/rejeter", ['motif' => 'Budget épuisé']);
        $this->assertSame(DemandeStatut::REJETEE, $demande->refresh()->statut);

        $this->actingAs($this->agent)->get("/demandes/{$demande->id}")
            ->assertOk()->assertSee('Budget épuisé')->assertSee('Rejetée');
    }

    public function test_une_demande_rejetee_est_finale(): void
    {
        $demande = $this->demandeSoumise();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/rejeter", ['motif' => 'Non justifié']);

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider")->assertForbidden();
        $this->actingAs($this->chef)->get('/validations?onglet=traitees')->assertSee($demande->reference);
    }

    // ------------------------------------------------------------------
    // Informations complémentaires (SFD §10, RG-25, RG-26)
    // ------------------------------------------------------------------

    public function test_complement_demande_par_le_n1_revient_au_n1(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/informations", ['question' => 'Quel modèle ?']);
        $demande->refresh();
        $this->assertSame(DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE, $demande->statut);
        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->statut_retour_complement);
        // (le toast flash cite la référence : on vérifie l'absence du lien dans la liste)
        $this->actingAs($this->chef)->get('/validations')->assertDontSee(route('demandes.show', $demande));

        $this->actingAs($this->agent)->get("/demandes/{$demande->id}")->assertOk()->assertSee('Quel modèle ?')->assertSee('Votre réponse');
        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/reponse", ['reponse' => 'Écran 27 pouces'])
            ->assertRedirect("/demandes/{$demande->id}");

        $demande->refresh();
        $this->assertSame(DemandeStatut::EN_ATTENTE_N1, $demande->statut);
        $this->assertNull($demande->statut_retour_complement);
        $this->assertSame('Écran 27 pouces', $demande->evenements()->where('action', ActionDemande::REPONSE_INFORMATIONS)->value('commentaire'));
        $this->actingAs($this->chef)->get('/validations')->assertSee($demande->reference);
    }

    public function test_complement_demande_par_le_n2_ne_repasse_pas_par_le_n1(): void
    {
        $demande = $this->demandeSoumise();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider");

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/informations", ['question' => 'Pour quand ?']);
        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/reponse", ['reponse' => 'Lundi']);

        $this->assertSame(DemandeStatut::EN_ATTENTE_N2, $demande->refresh()->statut);
        $this->assertSame(1, $demande->evenements()->where('action', ActionDemande::VALIDATION)->where('niveau', 'N1')->count());
    }

    public function test_question_et_reponse_obligatoires(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->chef)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/informations", ['question' => ''])
            ->assertSessionHasErrors('question');

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/informations", ['question' => 'Précisez']);
        $this->actingAs($this->agent)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/reponse", ['reponse' => ''])
            ->assertSessionHasErrors('reponse');
    }

    public function test_seul_le_demandeur_peut_repondre(): void
    {
        $demande = $this->demandeSoumise();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/informations", ['question' => 'Précisez']);

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/reponse", ['reponse' => 'Moi'])->assertForbidden();
    }

    public function test_pas_de_reponse_hors_complement(): void
    {
        $demande = $this->demandeSoumise();

        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/reponse", ['reponse' => 'Rien'])->assertForbidden();
    }

    public function test_suivi_affiche_l_attente_de_reponse(): void
    {
        $demande = $this->demandeSoumise();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/informations", ['question' => 'Précisez']);

        $this->actingAs($this->chef)->get("/demandes/{$demande->id}")
            ->assertOk()->assertSee('en attente de sa réponse')->assertDontSee('Votre décision');
    }
}
