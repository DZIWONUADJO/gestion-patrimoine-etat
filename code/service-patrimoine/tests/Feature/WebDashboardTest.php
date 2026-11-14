<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('CHEF_SERVICE');
            }

            if (! Schema::hasColumn('users', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function test_guest_is_sent_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/connexion')->assertOk()->assertSee('Connectez-vous');
    }

    public function test_active_user_can_login_and_see_live_reference_counts(): void
    {
        $user = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);
        Categorie::create([
            'libelle' => 'Informatique',
            'description' => 'Ordinateurs et réseaux.',
        ]);
        Service::create([
            'code_service' => 'MIN-SANTE',
            'nom_service' => 'Ministère de la Santé',
            'type' => 'MINISTERE',
        ]);

        $this->post('/connexion', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/')
            ->assertOk()
            ->assertViewHas('activeCategoryCount', 1)
            ->assertViewHas('activeServiceCount', 1)
            ->assertSee('Ministère de la Santé')
            ->assertSee('Informatique');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => false,
        ]);

        $this->from(route('login'))->post('/connexion', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_session_is_closed_when_the_user_becomes_inactive(): void
    {
        $user = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);
        $this->actingAs($user);

        $user->update(['is_active' => false]);
        $this->app['auth']->forgetGuards();

        $this->get('/')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);
        $this->actingAs($user);

        $this->post('/deconnexion')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
