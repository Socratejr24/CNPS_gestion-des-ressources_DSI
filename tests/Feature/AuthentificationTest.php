<?php

namespace Tests\Feature;

use App\Enums\CategorieUtilisateur;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\Ldap\AnnuaireIndisponibleException;
use App\Services\Ldap\AnnuaireLdap;
use App\Services\Ldap\FakeAnnuaireLdap;
use App\Services\Ldap\ProfilLdap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthentificationTest extends TestCase
{
    use RefreshDatabase;

    private FakeAnnuaireLdap $annuaire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Role::create(['libelle' => Role::DEMANDEUR]);

        $this->annuaire = new FakeAnnuaireLdap;
        $this->annuaire->ajouterCompte('m2005', 'secret', new ProfilLdap(
            matricule: 'm2005', nom: 'Ackadie', prenom: 'Dylane', email: 'dylane@cnps.ci', fonction: 'Analyste',
        ));
        $this->app->instance(AnnuaireLdap::class, $this->annuaire);
    }

    private function connexion(string $mode, string $identifiant, string $password)
    {
        return $this->postJson('/login', compact('mode', 'identifiant', 'password'));
    }

    // ------------------------------------------------------------------
    // Agents CNPS — LDAP (SFD §3.1.1)
    // ------------------------------------------------------------------

    public function test_premiere_connexion_ldap_cree_le_profil_avec_role_demandeur(): void
    {
        $this->connexion('agent', 'm2005', 'secret')
            ->assertOk()
            ->assertJsonPath('redirect', route('affectation.attente'));

        $user = User::where('matricule', 'm2005')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(CategorieUtilisateur::AGENT, $user->categorie);
        $this->assertSame('dylane@cnps.ci', $user->email);
        $this->assertNull($user->password);
        $this->assertTrue($user->hasRole(Role::DEMANDEUR));  // RG-10
        $this->assertFalse($user->aUneAffectation());        // RG-11
    }

    public function test_connexion_suivante_met_a_jour_le_profil_sans_doublon(): void
    {
        $this->connexion('agent', 'm2005', 'secret')->assertOk();
        $this->post('/logout');

        $this->annuaire->ajouterCompte('m2005', 'secret', new ProfilLdap(
            matricule: 'm2005', nom: 'Ackadie-Kone', prenom: 'Dylane', email: 'dylane@cnps.ci',
        ));

        $this->connexion('agent', 'm2005', 'secret')->assertOk();

        $this->assertSame(1, User::where('matricule', 'm2005')->count());
        $this->assertSame('Ackadie-Kone', User::where('matricule', 'm2005')->value('nom'));
        $this->assertSame(1, User::where('matricule', 'm2005')->first()->roles()->count());
    }

    public function test_agent_affecte_est_redirige_vers_le_dashboard(): void
    {
        $user = User::factory()->create([
            'matricule' => 'm2005', 'identifiant' => 'm2005',
            'service_id' => Service::factory()->create()->id,
        ]);

        $this->connexion('agent', 'm2005', 'secret')
            ->assertOk()->assertJsonPath('redirect', route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_mauvais_mot_de_passe_ldap(): void
    {
        $this->connexion('agent', 'm2005', 'faux')
            ->assertStatus(422)->assertJsonPath('message', 'Matricule ou mot de passe incorrect.');

        $this->assertGuest();
        $this->assertDatabaseMissing('utilisateurs', ['matricule' => 'm2005']);
    }

    public function test_annuaire_indisponible(): void
    {
        $this->app->instance(AnnuaireLdap::class, new class implements AnnuaireLdap
        {
            public function authentifier(string $identifiant, string $motDePasse): ?ProfilLdap
            {
                throw new AnnuaireIndisponibleException('timeout');
            }
        });

        $this->connexion('agent', 'm2005', 'secret')
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'indisponible'));
    }

    public function test_agent_desactive_refuse(): void
    {
        User::factory()->create(['matricule' => 'm2005', 'identifiant' => 'm2005', 'is_active' => false]);

        $this->connexion('agent', 'm2005', 'secret')
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'désactivé'));
        $this->assertGuest();
    }

    public function test_login_ldap_ne_peut_pas_prendre_un_compte_prestataire(): void
    {
        User::factory()->create([
            'identifiant' => 'm2005', 'categorie' => CategorieUtilisateur::PRESTATAIRE,
        ]);

        $this->connexion('agent', 'm2005', 'secret')->assertStatus(422);
        $this->assertGuest();
    }

    // ------------------------------------------------------------------
    // Prestataires — comptes locaux (SFD §3.1.2)
    // ------------------------------------------------------------------

    public function test_connexion_prestataire(): void
    {
        $user = User::factory()->create([
            'email' => 'presta@ex.ci', 'password' => 'Secret123',
            'categorie' => CategorieUtilisateur::PRESTATAIRE,
            'service_id' => Service::factory()->create()->id,
        ]);

        $this->connexion('prestataire', 'presta@ex.ci', 'Secret123')
            ->assertOk()->assertJsonPath('redirect', route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_prestataire_mauvais_mot_de_passe(): void
    {
        User::factory()->create([
            'email' => 'presta@ex.ci', 'password' => 'Secret123',
            'categorie' => CategorieUtilisateur::PRESTATAIRE,
        ]);

        $this->connexion('prestataire', 'presta@ex.ci', 'faux')
            ->assertStatus(422)->assertJsonPath('message', 'Adresse e-mail ou mot de passe incorrect.');
        $this->connexion('prestataire', 'inconnu@ex.ci', 'faux')
            ->assertStatus(422)->assertJsonPath('message', 'Adresse e-mail ou mot de passe incorrect.');
    }

    public function test_un_agent_ne_peut_pas_se_connecter_en_mode_prestataire(): void
    {
        User::factory()->create(['email' => 'agent@cnps.ci', 'password' => 'Secret123']); // AGENT

        $this->connexion('prestataire', 'agent@cnps.ci', 'Secret123')->assertStatus(422);
        $this->assertGuest();
    }

    // ------------------------------------------------------------------
    // Sécurité
    // ------------------------------------------------------------------

    public function test_limitation_des_tentatives(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->connexion('agent', 'm2005', 'faux')->assertStatus(422);
        }

        // Même le bon mot de passe est refusé pendant le blocage.
        $this->connexion('agent', 'm2005', 'secret')->assertStatus(429);
        $this->assertGuest();
    }

    public function test_formulaire_classique_sans_javascript(): void
    {
        $this->post('/login', ['mode' => 'agent', 'identifiant' => 'm2005', 'password' => 'faux'])
            ->assertRedirect()->assertSessionHasErrors('identifiant');

        $this->post('/login', ['mode' => 'agent', 'identifiant' => 'm2005', 'password' => 'secret'])
            ->assertRedirect(route('affectation.attente'));
    }

    public function test_deconnexion(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/logout')->assertOk();

        $this->assertGuest();
    }

    // ------------------------------------------------------------------
    // Accès aux pages (RG-11)
    // ------------------------------------------------------------------

    public function test_invite_redirige_vers_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/affectation-en-attente')->assertRedirect(route('login'));
    }

    public function test_utilisateur_connecte_ne_revoit_pas_le_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_sans_affectation_pas_d_acces_metier(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('affectation.attente'));
        $this->actingAs($user)->getJson('/demandes/mes-demandes')->assertForbidden();
        $this->actingAs($user)->get('/affectation-en-attente')
            ->assertOk()->assertSee('Affectation en attente');
    }

    public function test_administrateur_sans_affectation_accede_au_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@ex.ci', 'password' => 'Secret123',
            'categorie' => CategorieUtilisateur::PRESTATAIRE,
            'service_id' => null,
        ]);
        $admin->roles()->attach(Role::create(['libelle' => Role::ADMINISTRATEUR])->id);

        $this->connexion('prestataire', 'admin@ex.ci', 'Secret123')
            ->assertOk()->assertJsonPath('redirect', route('dashboard'));
        $this->get('/dashboard')->assertOk();
    }

    public function test_page_attente_redirige_une_fois_affecte(): void
    {
        $user = User::factory()->create(['service_id' => Service::factory()->create()->id]);

        $this->actingAs($user)->get('/affectation-en-attente')->assertRedirect(route('dashboard'));
    }

    public function test_compte_desactive_en_cours_de_session_est_deconnecte(): void
    {
        $user = User::factory()->create(['service_id' => Service::factory()->create()->id]);
        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->update(['is_active' => false]);

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_dashboard_affiche_l_utilisateur_connecte(): void
    {
        $user = User::factory()->create([
            'prenom' => 'Dylane', 'service_id' => Service::factory()->create(['libelle' => 'Service Test'])->id,
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()->assertSee('Bonjour Dylane')->assertSee('Service Test');
    }

    public function test_user_can_sappuie_sur_les_permissions_des_roles(): void
    {
        $receveur = Role::create(['libelle' => Role::RECEVEUR]);
        $receveur->permissions()->attach(Permission::create(['libelle' => 'T', 'slug' => 'voir.traitement'])->id);

        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertFalse(user_can('voir.traitement'));

        $user->roles()->attach($receveur->id);
        $user->unsetRelation('roles');
        $this->assertTrue(user_can('voir.traitement'));

        $receveur->update(['is_active' => false]);
        $user->unsetRelation('roles');
        $this->assertFalse(user_can('voir.traitement'));
    }
}
