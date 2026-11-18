<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SocleTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
    }

    public function test_utilisateurs_schema_matches_model(): void
    {
        $this->assertTrue(Schema::hasColumns('utilisateurs', ['password', 'remember_token']));
        $this->assertFalse(Schema::hasColumn('utilisateurs', 'mot_de_passe'));

        foreach (['matricule', 'email', 'identifiant'] as $column) {
            $this->assertTrue(
                Schema::hasIndex('utilisateurs', [$column], 'unique'),
                "Index unique manquant sur utilisateurs.$column",
            );
        }
    }

    public function test_permissions_has_no_duplicate_french_timestamp_columns(): void
    {
        foreach (['cree_le', 'modifie_le', 'supprime_le'] as $column) {
            $this->assertFalse(Schema::hasColumn('permissions', $column));
        }
    }

    public function test_user_factory_creates_user_with_hashed_password(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->nom);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_ldap_user_can_be_stored_without_password(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->assertNull($user->fresh()->password);
    }

    public function test_seeder_creates_functional_roles_and_admin(): void
    {
        $this->seed();
        $this->seed(); // idempotent

        $this->assertEqualsCanonicalizing(
            ['Administrateur', 'Demandeur', 'Receveur'],
            Role::pluck('libelle')->all(),
        );
        $this->assertSame(1, User::where('email', 'admin@cnps.ci')->count());
    }

    public function test_seeder_restores_soft_deleted_role(): void
    {
        Role::create(['libelle' => 'Demandeur', 'is_active' => true])->delete();

        $this->seed();

        $this->assertNotNull(Role::where('libelle', 'Demandeur')->first());
    }

    public function test_pages_render_with_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $this->withoutVite();

        $this->get('/login')->assertOk();
        $this->get('/dashboard')->assertOk();
        $this->get('/demandes/mes-demandes')->assertOk();
    }

    public function test_health_route_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
