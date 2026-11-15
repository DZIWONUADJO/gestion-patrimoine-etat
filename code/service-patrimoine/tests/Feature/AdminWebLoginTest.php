<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminWebLoginTest extends TestCase
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

    public function test_admin_created_via_command_hashing_path_can_log_in_through_web(): void
    {
        // (a) Create an active admin via the SAME hashing path the admin commands use:
        //     assign a known RANDOM plaintext and let the 'hashed' cast hash it
        //     (NOT the factory pre-hash, which would mask a broken cast/command path).
        $password = Str::random(16);

        $user = User::create([
            'name' => 'Administrateur test',
            'email' => 'admin-test-'.Str::random(6).'@example.tg',
            'password' => $password,
            'role' => 'ADMIN',
            'service_id' => null,
            'is_active' => true,
        ]);

        // (b) Attempt login through the SAME path as the web login
        //     (POST /connexion -> SessionController -> 'web' guard).
        $this->post('/connexion', [
            'email' => $user->email,
            'password' => $password,
        ])->assertRedirect(route('dashboard'));

        // (c) Assert authenticated through the 'web' guard, then log out cleanly.
        $this->assertAuthenticated('web');

        $this->post('/deconnexion')->assertRedirect(route('login'));
        $this->assertGuest('web');
    }
}
