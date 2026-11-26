<?php

namespace Tests\Feature;

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
 * Écran « Toutes les demandes » de l'administrateur (SFD §3.3.1).
 */
class AdministrationDemandesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $responsable;   // soumet en transmission directe (RG-20)

    private Service $support;

    private Departement $departement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $demandeur = Role::create(['libelle' => Role::DEMANDEUR]);
        $receveur = Role::create(['libelle' => Role::RECEVEUR]);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::create(['libelle' => Role::ADMINISTRATEUR]));

        $this->departement = Departement::factory()->create(['libelle' => 'Développement']);
        $service = Service::factory()->create(['departement_id' => $this->departement->id]);
        $this->responsable = User::factory()->create(['service_id' => $service->id, 'nom' => 'Konan', 'matricule' => 'm1003']);
        $this->responsable->roles()->attach($demandeur);
        $this->departement->forceFill(['responsable_id' => $this->responsable->id])->save();

        $this->support = Service::factory()->create();
        User::factory()->create(['service_id' => $this->support->id])->roles()->attach($receveur);
    }

    private function soumettre(string $objet): Demande
    {
        $this->actingAs($this->responsable)->post('/demandes', [
            'action' => 'soumettre', 'objet' => $objet, 'description' => 'Motif',
            'destination_type' => 'SERVICE', 'destination_service_id' => $this->support->id,
            'lignes' => [['ressource_id' => Ressource::factory()->create(['designation' => 'Écran'])->id, 'quantite' => 2]],
        ]);

        return Demande::latest('id')->firstOrFail();
    }

    public function test_reserve_a_l_administrateur(): void
    {
        $this->actingAs($this->responsable)->get('/admin/demandes')->assertForbidden();
        $this->actingAs($this->responsable)->get('/admin/demandes/export')->assertForbidden();
        $this->actingAs($this->admin)->get('/admin/demandes')->assertOk();
    }

    public function test_liste_les_demandes_soumises_sans_les_brouillons(): void
    {
        $soumise = $this->soumettre('Demande soumise');
        Demande::factory()->pour($this->responsable)->create(['objet' => 'Brouillon privé']);

        $this->actingAs($this->admin)->get('/admin/demandes')
            ->assertOk()->assertSee('Demande soumise')->assertSee($soumise->reference)->assertDontSee('Brouillon privé');
    }

    public function test_filtres(): void
    {
        $ouverte = $this->soumettre('Écran double');
        $rejetee = $this->soumettre('Licence refusée');
        $rejetee->forceFill(['statut' => DemandeStatut::REJETEE])->save();

        $this->actingAs($this->admin)->get('/admin/demandes?etat=EN_COURS')->assertSee('Écran double')->assertDontSee('Licence refusée');
        $this->actingAs($this->admin)->get('/admin/demandes?etat=TERMINEES')->assertSee('Licence refusée')->assertDontSee('Écran double');
        $this->actingAs($this->admin)->get('/admin/demandes?etat=REJETEE')->assertSee('Licence refusée');
        $this->actingAs($this->admin)->get('/admin/demandes?q=m1003')->assertSee('Écran double');
        $this->actingAs($this->admin)->get("/admin/demandes?departement_id={$this->departement->id}")->assertSee('Écran double');
        $this->actingAs($this->admin)->get('/admin/demandes?departement_id=999999')->assertDontSee('Écran double');
        $this->actingAs($this->admin)->get('/admin/demandes?du='.now()->addDay()->format('Y-m-d'))->assertDontSee($ouverte->reference);
    }

    public function test_periode_incoherente_refusee(): void
    {
        $this->actingAs($this->admin)->from('/admin/demandes')
            ->get('/admin/demandes?du=2026-11-20&au=2026-11-01')
            ->assertSessionHasErrors('au');
    }

    public function test_export_csv(): void
    {
        $demande = $this->soumettre('Écran double');

        $reponse = $this->actingAs($this->admin)->get('/admin/demandes/export');
        $reponse->assertOk();
        $this->assertStringContainsString('text/csv', $reponse->headers->get('Content-Type'));

        $csv = $reponse->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Référence;Objet;Demandeur', $csv);
        $this->assertStringContainsString($demande->reference, $csv);
        $this->assertStringContainsString('Écran × 2', $csv);
    }

    public function test_detail_depuis_la_liste_admin(): void
    {
        $demande = $this->soumettre('Écran double');

        $this->actingAs($this->admin)->get("/demandes/{$demande->id}?depuis=admin")
            ->assertOk()->assertSee('Retour à toutes les demandes')
            ->assertDontSee('Votre décision')->assertDontSee('Prendre en charge');
    }

    public function test_entree_de_menu(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertSee(route('admin.demandes.index'));
    }
}
