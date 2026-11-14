<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_bearer_token(): void
    {
        $user = User::factory()->create([
            'password' => 'correct-password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.role', UserRole::ServiceChief->value);

        $token = $response->json('data.access_token');
        $this->assertIsString($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_login_rejects_incorrect_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'correct-password',
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Les identifiants fournis sont incorrects.',
            ]);
    }

    public function test_login_rejects_inactive_users(): void
    {
        $user = User::factory()->create([
            'password' => 'correct-password',
            'is_active' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_inactive_user_cannot_use_an_existing_bearer_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $user->is_active = false;
        $user->save();

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_login_validates_required_credentials(): void
    {
        $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_logout_revokes_the_current_bearer_token(): void
    {
        $user = User::factory()->create();
        $issuedToken = $user->createToken('test');

        $this->withToken($issuedToken->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $issuedToken->accessToken->id,
        ]);

        $this->app['auth']->forgetGuards();

        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_user_endpoint_rejects_unauthenticated_requests(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
