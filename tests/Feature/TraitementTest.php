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
 * Étape 4 du module Demandes : traitement, réception, anomalies, régularisation (SFD §8, §9).
 */
class TraitementTest extends TestCase
{
    use RefreshDatabase;

    private Role $demandeurRole;

    private Role $receveurRole;

    private User $responsable;   // demandeur responsable de son département : transmission directe (RG-20)

    private Service $support;    // service destinataire

    private User $receveur;

    private User $collegue;      // autre receveur du service Support

    private User $horsPerimetre; // receveur d'un autre service

    private Ressource $pc;

    private Ressource $ecran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->demandeurRole = Role::create(['libelle' => Role::DEMANDEUR]);
        $this->receveurRole = Role::create(['libelle' => Role::RECEVEUR]);

        $departement = Departement::factory()->create();
        $service = Service::factory()->create(['departement_id' => $departement->id]);
        $this->responsable = $this->membre($service, 'Responsable');
        $departement->forceFill(['responsable_id' => $this->responsable->id])->save();

        $this->support = Service::factory()->create(['libelle' => 'Support']);
        $this->receveur = $this->membre($this->support, 'Receveur', true);
        $this->collegue = $this->membre($this->support, 'Collegue', true);
        $this->horsPerimetre = $this->membre(Service::factory()->create(), 'Ailleurs', true);

        $this->pc = Ressource::factory()->create(['designation' => 'Ordinateur portable']);
        $this->ecran = Ressource::factory()->create(['designation' => 'Écran 24']);
    }

    private function membre(Service $service, string $nom, bool $receveur = false): User
    {
        $user = User::factory()->create(['service_id' => $service->id, 'nom' => $nom]);
        $user->roles()->attach($this->demandeurRole);
        if ($receveur) {
            $user->roles()->attach($this->receveurRole);
        }

        return $user;
    }

    /** Demande transmise au service Support (statut EN_ATTENTE_TRAITEMENT). */
    private function demandeTransmise(): Demande
    {
        $this->actingAs($this->responsable)->post('/demandes', [
            'action' => 'soumettre',
            'objet' => 'Équipement nouvel arrivant',
            'description' => 'Arrivée lundi.',
            'destination_type' => 'SERVICE',
            'destination_service_id' => $this->support->id,
            'lignes' => [
                ['ressource_id' => $this->pc->id, 'quantite' => 1],
                ['ressource_id' => $this->ecran->id, 'quantite' => 2],
            ],
        ]);

        return Demande::latest('id')->firstOrFail();
    }

    private function demandePriseEnCharge(): Demande
    {
        $demande = $this->demandeTransmise();
        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/prise-en-charge");

        return $demande->refresh();
    }

    private function quantites(Demande $demande, int $pc, int $ecran): array
    {
        $lignes = $demande->lignes()->get()->keyBy('ressource_id');

        return [
            $lignes[$this->pc->id]->id => ['quantite_fournie' => $pc],
            $lignes[$this->ecran->id]->id => ['quantite_fournie' => $ecran, 'observation' => 'Un seul écran en stock'],
        ];
    }

    private function demandeMiseADisposition(int $ecrans = 1): Demande
    {
        $demande = $this->demandePriseEnCharge();
        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/mise-a-disposition", [
            'lignes' => $this->quantites($demande, 1, $ecrans),
            'date' => now()->format('Y-m-d'),
            'observations' => 'À récupérer au bureau 214',
        ]);

        return $demande->refresh();
    }

    // ------------------------------------------------------------------
    // Écran « Demandes reçues » et prise en charge (RG-23, RG-34)
    // ------------------------------------------------------------------

    public function test_demande_visible_par_les_receveurs_du_perimetre_uniquement(): void
    {
        $demande = $this->demandeTransmise();

        $this->actingAs($this->receveur)->get('/traitement')->assertOk()->assertSee(route('demandes.show', $demande));
        $this->actingAs($this->collegue)->get('/traitement')->assertSee(route('demandes.show', $demande));
        $this->actingAs($this->horsPerimetre)->get('/traitement')->assertDontSee(route('demandes.show', $demande));

        $this->actingAs($this->receveur)->get("/demandes/{$demande->id}")->assertOk()->assertSee('Prendre en charge');
        $this->actingAs($this->horsPerimetre)->get("/demandes/{$demande->id}")->assertForbidden();
    }

    public function test_menu_traitement_reserve_aux_receveurs(): void
    {
        $this->actingAs($this->receveur)->get('/dashboard')->assertSee(route('traitement.index'));
        $this->actingAs($this->responsable)->get('/dashboard')->assertDontSee(route('traitement.index'));
    }

    public function test_prise_en_charge(): void
    {
        $demande = $this->demandePriseEnCharge();

        $this->assertSame(DemandeStatut::EN_COURS_TRAITEMENT, $demande->statut);
        $this->assertSame($this->receveur->id, $demande->receveur_id);
        $this->assertNotNull($demande->prise_en_charge_le);
        $this->assertTrue($demande->evenements()->where('action', ActionDemande::PRISE_EN_CHARGE)->exists());

        // Le collègue ne peut plus la prendre ni la traiter, mais peut la consulter.
        $this->actingAs($this->collegue)->post("/demandes/{$demande->id}/prise-en-charge")->assertForbidden();
        $this->actingAs($this->collegue)->post("/demandes/{$demande->id}/impossibilite", ['justification' => 'x'])->assertForbidden();
        $this->actingAs($this->collegue)->get("/demandes/{$demande->id}")->assertOk();
        $this->actingAs($this->receveur)->get('/traitement?onglet=en-cours')->assertSee(route('demandes.show', $demande));
    }

    public function test_hors_perimetre_ne_peut_pas_prendre_en_charge(): void
    {
        $demande = $this->demandeTransmise();

        $this->actingAs($this->horsPerimetre)->post("/demandes/{$demande->id}/prise-en-charge")->assertForbidden();
        $this->assertSame(DemandeStatut::EN_ATTENTE_TRAITEMENT, $demande->refresh()->statut);
    }

    public function test_destination_departement_couvre_ses_receveurs(): void
    {
        $this->actingAs($this->responsable)->post('/demandes', [
            'action' => 'soumettre', 'objet' => 'X', 'description' => 'Y',
            'destination_type' => 'DEPARTEMENT', 'destination_departement_id' => $this->support->departement_id,
            'lignes' => [['ressource_id' => $this->pc->id, 'quantite' => 1]],
        ]);
        $demande = Demande::latest('id')->first();

        $this->actingAs($this->collegue)->post("/demandes/{$demande->id}/prise-en-charge")->assertRedirect();
        $this->assertSame($this->collegue->id, $demande->refresh()->receveur_id);
    }

    // ------------------------------------------------------------------
    // Mise à disposition (RG-27) et impossibilité
    // ------------------------------------------------------------------

    public function test_mise_a_disposition_avec_quantites_effectives(): void
    {
        $demande = $this->demandeMiseADisposition();

        $this->assertSame(DemandeStatut::EN_ATTENTE_CONFIRMATION, $demande->statut);
        $this->assertSame('À récupérer au bureau 214', $demande->observations_mise_a_disposition);
        $this->assertSame(1, $demande->lignes()->where('ressource_id', $this->ecran->id)->value('quantite_fournie'));
        $this->assertStringContainsString('Écran 24 : 1 / 2', $demande->evenements()->where('action', ActionDemande::MISE_A_DISPOSITION)->value('commentaire'));

        $this->actingAs($this->responsable)->get("/demandes/{$demande->id}")
            ->assertOk()->assertSee('Confirmer la réception')->assertSee('Un seul écran en stock');
    }

    public function test_mise_a_disposition_sans_aucune_ressource_refusee(): void
    {
        $demande = $this->demandePriseEnCharge();

        $this->actingAs($this->receveur)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/mise-a-disposition", ['lignes' => $this->quantites($demande, 0, 0), 'date' => now()->format('Y-m-d')])
            ->assertSessionHasErrors('regle_metier');

        $this->assertSame(DemandeStatut::EN_COURS_TRAITEMENT, $demande->refresh()->statut);
    }

    public function test_date_de_mise_a_disposition_dans_le_futur_refusee(): void
    {
        $demande = $this->demandePriseEnCharge();

        $this->actingAs($this->receveur)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/mise-a-disposition", ['lignes' => $this->quantites($demande, 1, 1), 'date' => now()->addDay()->format('Y-m-d')])
            ->assertSessionHasErrors('date');
    }

    public function test_impossibilite_de_traitement_finale(): void
    {
        $demande = $this->demandePriseEnCharge();

        $this->actingAs($this->receveur)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/impossibilite", ['justification' => ''])
            ->assertSessionHasErrors('justification');

        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/impossibilite", ['justification' => 'Rupture de stock fournisseur']);
        $demande->refresh();
        $this->assertSame(DemandeStatut::TRAITEMENT_IMPOSSIBLE, $demande->statut);
        $this->assertTrue($demande->statut->estFinal());

        $this->actingAs($this->responsable)->get("/demandes/{$demande->id}")->assertOk()->assertSee('Rupture de stock fournisseur');
        $this->actingAs($this->receveur)->get('/traitement?onglet=terminees')->assertSee(route('demandes.show', $demande));
    }

    public function test_complement_demande_par_le_receveur_lui_revient(): void
    {
        $demande = $this->demandePriseEnCharge();

        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/informations-receveur", ['question' => 'Quel bureau ?']);
        $this->assertSame(DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE, $demande->refresh()->statut);

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/reponse", ['reponse' => 'Bureau 214']);
        $demande->refresh();
        $this->assertSame(DemandeStatut::EN_COURS_TRAITEMENT, $demande->statut);
        $this->assertSame($this->receveur->id, $demande->receveur_id);
    }

    // ------------------------------------------------------------------
    // Réception, anomalie, régularisation (SFD §9, RG-28 à RG-31)
    // ------------------------------------------------------------------

    public function test_confirmation_de_reception_cloture_la_demande(): void
    {
        $demande = $this->demandeMiseADisposition();

        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/confirmation")->assertForbidden();
        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/confirmation", ['commentaire' => 'Merci'])
            ->assertRedirect("/demandes/{$demande->id}");

        $demande->refresh();
        $this->assertSame(DemandeStatut::CLOTUREE, $demande->statut);
        $this->assertNotNull($demande->cloturee_le);
        $this->actingAs($this->responsable)->get("/demandes/{$demande->id}")->assertOk()->assertDontSee('Confirmer la réception');
    }

    public function test_anomalie_puis_regularisation_puis_cloture(): void
    {
        $demande = $this->demandeMiseADisposition(ecrans: 1);
        $ligneEcran = $demande->lignes()->where('ressource_id', $this->ecran->id)->value('id');

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/anomalie", [
            'nature' => 'RESSOURCE_MANQUANTE',
            'concernees' => [$ligneEcran],
            'quantites' => [$ligneEcran => 1],
            'description' => 'Il manque un écran.',
        ])->assertRedirect("/demandes/{$demande->id}");

        $demande->refresh();
        $this->assertSame(DemandeStatut::EN_COURS_REGULARISATION, $demande->statut);
        $anomalie = $demande->anomalies()->first();
        $this->assertSame([['ligne_id' => $ligneEcran, 'quantite' => 1]], $anomalie->lignes);
        $this->assertFalse($anomalie->estRegularisee());

        // Régularisation par le receveur attitré uniquement (RG-30), sans reprise des validations.
        $this->actingAs($this->collegue)->post("/demandes/{$demande->id}/regularisation", [
            'lignes' => $this->quantites($demande, 1, 2), 'explication' => 'x',
        ])->assertForbidden();

        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/regularisation", [
            'lignes' => $this->quantites($demande, 1, 2), 'explication' => 'Second écran livré.',
        ])->assertRedirect("/demandes/{$demande->id}");

        $demande->refresh();
        $this->assertSame(DemandeStatut::EN_ATTENTE_CONFIRMATION, $demande->statut);
        $this->assertTrue($anomalie->refresh()->estRegularisee());
        $this->assertSame(2, $demande->lignes()->where('ressource_id', $this->ecran->id)->value('quantite_fournie'));
        $this->assertFalse($demande->evenements()->where('action', ActionDemande::VALIDATION)->exists());

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/confirmation");
        $this->assertSame(DemandeStatut::CLOTUREE, $demande->refresh()->statut);
    }

    public function test_anomalie_exige_nature_ressource_et_description(): void
    {
        $demande = $this->demandeMiseADisposition();

        $this->actingAs($this->responsable)->from("/demandes/{$demande->id}")
            ->post("/demandes/{$demande->id}/anomalie", ['nature' => '', 'description' => ''])
            ->assertSessionHasErrors(['nature', 'description', 'concernees']);

        $this->assertSame(DemandeStatut::EN_ATTENTE_CONFIRMATION, $demande->refresh()->statut);
    }

    public function test_pas_de_confirmation_avant_mise_a_disposition(): void
    {
        $demande = $this->demandePriseEnCharge();

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/confirmation")->assertForbidden();
    }

    public function test_suivi_complet_jusqu_a_la_cloture(): void
    {
        $demande = $this->demandeMiseADisposition(ecrans: 2);
        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/confirmation");

        $this->actingAs($this->responsable)->get("/demandes/{$demande->id}")
            ->assertOk()
            ->assertSee('Clôturée')
            ->assertSee('Prise en charge')
            ->assertSee('Mise à disposition des ressources')
            ->assertSee('Confirmation de réception');
    }
}
