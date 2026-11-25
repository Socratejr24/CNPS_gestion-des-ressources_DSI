<?php

namespace Tests\Feature;

use App\Models\Demande;
use App\Models\Departement;
use App\Models\Ressource;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Notifications\EvenementDemande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Étape 5 du module Demandes : notifications (SFD §12) et tableau de bord (SFD §14).
 */
class NotificationsEtTableauDeBordTest extends TestCase
{
    use RefreshDatabase;

    private Role $demandeurRole;

    private User $agent;

    private User $chef;

    private User $responsable;

    private User $receveur;

    private Service $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->demandeurRole = Role::create(['libelle' => Role::DEMANDEUR]);
        $receveurRole = Role::create(['libelle' => Role::RECEVEUR]);

        $departement = Departement::factory()->create();
        $service = Service::factory()->create(['departement_id' => $departement->id]);
        $this->agent = $this->membre($service, 'Agent');
        $this->chef = $this->membre($service, 'Chef');
        $this->responsable = $this->membre($service, 'Responsable');
        $service->forceFill(['chef_id' => $this->chef->id])->save();
        $departement->forceFill(['responsable_id' => $this->responsable->id])->save();

        $this->support = Service::factory()->create();
        $this->receveur = $this->membre($this->support, 'Receveur');
        $this->receveur->roles()->attach($receveurRole);
    }

    private function membre(Service $service, string $nom): User
    {
        $user = User::factory()->create(['service_id' => $service->id, 'nom' => $nom]);
        $user->roles()->attach($this->demandeurRole);

        return $user;
    }

    private function soumettre(): Demande
    {
        $this->actingAs($this->agent)->post('/demandes', [
            'action' => 'soumettre', 'objet' => 'Licence', 'description' => 'Besoin',
            'destination_type' => 'SERVICE', 'destination_service_id' => $this->support->id,
            'lignes' => [['ressource_id' => Ressource::factory()->create()->id, 'quantite' => 1]],
        ]);

        return Demande::latest('id')->firstOrFail();
    }

    private function titres(User $user): array
    {
        return $user->notifications()->get()->pluck('data.titre')->all();
    }

    // ------------------------------------------------------------------
    // Notifications (SFD §12.2)
    // ------------------------------------------------------------------

    public function test_soumission_notifie_le_n1_mais_pas_son_auteur(): void
    {
        $demande = $this->soumettre();

        // L'auteur d'une action n'est jamais notifié de sa propre action (il vient de la faire).
        $this->assertNotContains("Demande {$demande->reference} soumise", $this->titres($this->agent));
        $this->assertContains("Demande {$demande->reference} à valider (N+1)", $this->titres($this->chef));
        $this->assertSame([], $this->titres($this->responsable)); // pas encore son tour
    }

    public function test_circuit_complet_notifie_chaque_acteur(): void
    {
        $demande = $this->soumettre();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider");
        $this->assertContains("Demande {$demande->reference} à valider (N+2)", $this->titres($this->responsable));

        $this->actingAs($this->responsable)->post("/demandes/{$demande->id}/valider");
        $this->assertContains("Nouvelle demande {$demande->reference} à prendre en charge", $this->titres($this->receveur));

        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/prise-en-charge");
        $this->assertContains("Demande {$demande->reference} prise en charge", $this->titres($this->agent));

        $ligne = $demande->lignes()->first();
        $this->actingAs($this->receveur)->post("/demandes/{$demande->id}/mise-a-disposition", [
            'lignes' => [$ligne->id => ['quantite_fournie' => 1]], 'date' => now()->format('Y-m-d'),
        ]);
        $this->assertContains("Ressources mises à disposition ({$demande->reference})", $this->titres($this->agent));

        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/confirmation");
        $this->assertContains("Demande {$demande->reference} clôturée", $this->titres($this->receveur));
    }

    public function test_rejet_et_questions_notifies(): void
    {
        $demande = $this->soumettre();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/informations", ['question' => 'Pour qui ?']);
        $this->assertContains("Informations demandées sur {$demande->reference}", $this->titres($this->agent));

        $this->actingAs($this->agent)->post("/demandes/{$demande->id}/reponse", ['reponse' => 'Pour moi']);
        $this->assertContains("Réponse du demandeur sur {$demande->reference}", $this->titres($this->chef));

        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/rejeter", ['motif' => 'Hors budget']);
        $notif = $this->agent->notifications()->get()->firstWhere('data.titre', "Demande {$demande->reference} rejetée");
        $this->assertSame('Motif : Hors budget', $notif->data['message']);
    }

    public function test_l_auteur_n_est_pas_notifie_de_sa_propre_action(): void
    {
        $demande = $this->soumettre();
        $this->actingAs($this->chef)->post("/demandes/{$demande->id}/valider");

        $this->assertNotContains("Demande {$demande->reference} validée (N+1)", $this->titres($this->chef));
        $this->assertContains("Demande {$demande->reference} validée (N+1)", $this->titres($this->agent));
    }

    public function test_email_seulement_si_active(): void
    {
        Notification::fake();
        config(['demandes.notifications.mail' => false]);
        $this->soumettre();
        Notification::assertSentTo($this->chef, EvenementDemande::class, fn ($n, $canaux) => $canaux === ['database']);

        Notification::fake();
        config(['demandes.notifications.mail' => true]);
        $this->soumettre();
        Notification::assertSentTo($this->chef, EvenementDemande::class, fn ($n, $canaux) => in_array('mail', $canaux, true));
    }

    // ------------------------------------------------------------------
    // Interface des notifications
    // ------------------------------------------------------------------

    public function test_cloche_page_et_lecture(): void
    {
        $demande = $this->soumettre();

        $this->actingAs($this->chef)->get('/dashboard')->assertOk()->assertSee('notifCount = 1', false);
        $this->actingAs($this->chef)->get('/notifications')->assertOk()->assertSee("à valider (N+1)");

        $notification = $this->chef->notifications()->first();
        $this->actingAs($this->chef)->get("/notifications/{$notification->id}")->assertRedirect("/demandes/{$demande->id}");
        $this->assertNotNull($notification->refresh()->read_at);
        $this->actingAs($this->chef)->get('/dashboard')->assertSee('notifCount = 0', false);
    }

    public function test_tout_marquer_comme_lu(): void
    {
        $this->soumettre();
        $this->soumettre();

        $this->actingAs($this->chef)->from('/notifications')->post('/notifications/tout-lire')->assertRedirect('/notifications');
        $this->assertSame(0, $this->chef->unreadNotifications()->count());
    }

    public function test_on_ne_lit_pas_la_notification_d_un_autre(): void
    {
        $this->soumettre();
        $notification = $this->chef->notifications()->first();

        $this->actingAs($this->agent)->get("/notifications/{$notification->id}")->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Tableau de bord (SFD §14)
    // ------------------------------------------------------------------

    public function test_tableau_de_bord_du_demandeur_avec_donnees_reelles(): void
    {
        $demande = $this->soumettre();
        Demande::factory()->pour($this->chef)->create(['objet' => 'Demande du chef']);

        $this->actingAs($this->agent)->get('/dashboard')
            ->assertOk()
            ->assertSee('Mes dernières demandes')
            ->assertSee($demande->objet)
            ->assertDontSee('Demande du chef')
            ->assertSee('soumise');
    }

    public function test_tuiles_de_travail_selon_les_responsabilites(): void
    {
        $this->soumettre();

        $this->actingAs($this->chef)->get('/dashboard')->assertSee('Demandes à valider');
        $this->actingAs($this->receveur)->get('/dashboard')->assertSee('À prendre en charge');
        $this->actingAs($this->agent)->get('/dashboard')->assertDontSee('Demandes à valider')->assertDontSee('À prendre en charge');
    }

    public function test_vue_globale_pour_l_administrateur(): void
    {
        $demande = $this->soumettre();
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['libelle' => Role::ADMINISTRATEUR]));

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()->assertSee('Vue administrateur')->assertSee($demande->objet);
    }

    public function test_tableau_de_bord_sans_demande(): void
    {
        $this->actingAs($this->agent)->get('/dashboard')
            ->assertOk()->assertSee('Aucune demande pour le moment')->assertSee('Aucune notification');
    }
}
