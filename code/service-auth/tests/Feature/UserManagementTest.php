<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function test_admin_can_create_a_user_scoped_to_a_service(): void
    {
        $serviceId = DB::table('services')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        Sanctum::actingAs($admin);

        $this->postJson('/api/users', [
            'name' => 'Gestionnaire de test',
            'email' => 'gestionnaire@example.tg',
            'password' => 'Patrimoine#Togo2026',
            'password_confirmation' => 'Patrimoine#Togo2026',
            'role' => UserRole::Manager->value,
            'service_id' => $serviceId,
        ])->assertCreated()
            ->assertJsonPath('data.role', UserRole::Manager->value)
            ->assertJsonPath('data.service_id', $serviceId)
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('users', [
            'email' => 'gestionnaire@example.tg',
            'role' => UserRole::Manager->value,
            'service_id' => $serviceId,
            'is_active' => true,
        ]);

        $this->assertNotSame(
            'Patrimoine#Togo2026',
            User::where('email', 'gestionnaire@example.tg')->value('password')
        );
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => UserRole::ServiceChief,
        ]));

        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_admin_can_show_user_without_exposing_password(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/users/'.$user->id)
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', UserRole::ServiceChief->value)
            ->assertJsonMissingPath('data.password');
    }

    public function test_manager_must_be_assigned_to_an_existing_service(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => UserRole::Admin,
        ]));

        $this->postJson('/api/users', [
            'name' => 'Gestionnaire sans service',
            'email' => 'sans-service@example.tg',
            'password' => 'Patrimoine#Togo2026',
            'password_confirmation' => 'Patrimoine#Togo2026',
            'role' => UserRole::Manager->value,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['service_id']);
    }

    public function test_admin_cannot_disable_or_demote_their_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        Sanctum::actingAs($admin);

        $this->patchJson('/api/users/'.$admin->id, [
            'is_active' => false,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_active' => true,
        ]);
    }

    public function test_initial_admin_command_creates_an_admin_without_public_registration(): void
    {
        $this->artisan('sgpe:admin:create')
            ->expectsQuestion('Nom complet', 'Administrateur initial')
            ->expectsQuestion('Adresse e-mail', 'admin@example.tg')
            ->expectsQuestion('Mot de passe', 'Patrimoine#Togo2026')
            ->expectsQuestion('Confirmer le mot de passe', 'Patrimoine#Togo2026')
            ->expectsOutputToContain('Administrateur créé.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.tg',
            'role' => UserRole::Admin->value,
            'service_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_initial_admin_command_will_not_create_a_duplicate_admin(): void
    {
        User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->artisan('sgpe:admin:create')
            ->expectsOutputToContain('Un administrateur existe déjà.')
            ->assertExitCode(1);
    }

    public function test_deactivating_a_user_revokes_their_tokens(): void
    {
        $serviceId = DB::table('services')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'service_id' => $serviceId,
        ]);
        $issuedToken = $manager->createToken('test');
        Sanctum::actingAs($admin);

        $this->patchJson('/api/users/'.$manager->id, [
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $issuedToken->accessToken->id,
        ]);

        $this->app['auth']->forgetGuards();

        $this->withToken($issuedToken->plainTextToken)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
