<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_can_be_reset_and_existing_tokens_are_revoked(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        $oldPasswordHash = $admin->password;
        $token = $admin->createToken('before-password-reset');

        $this->artisan('sgpe:admin:password-reset')
            ->expectsQuestion('Adresse e-mail de l’administrateur', $admin->email)
            ->expectsQuestion('Nouveau mot de passe', 'NewPassword#Togo2026')
            ->expectsQuestion('Confirmer le nouveau mot de passe', 'NewPassword#Togo2026')
            ->expectsOutputToContain('Mot de passe administrateur réinitialisé.')
            ->assertExitCode(0);

        $admin->refresh();

        $this->assertNotSame($oldPasswordHash, $admin->password);
        $this->assertTrue(Hash::check('NewPassword#Togo2026', $admin->password));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_password_reset_requires_an_active_administrator(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => false,
        ]);

        $this->artisan('sgpe:admin:password-reset')
            ->expectsQuestion('Adresse e-mail de l’administrateur', $admin->email)
            ->expectsOutputToContain('Aucun administrateur actif trouvé pour cette adresse.')
            ->assertExitCode(1);
    }

    public function test_password_reset_does_not_change_password_when_confirmation_fails(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
        $oldPasswordHash = $admin->password;

        $this->artisan('sgpe:admin:password-reset')
            ->expectsQuestion('Adresse e-mail de l’administrateur', $admin->email)
            ->expectsQuestion('Nouveau mot de passe', 'NewPassword#Togo2026')
            ->expectsQuestion('Confirmer le nouveau mot de passe', 'DifferentPassword#2026')
            ->assertExitCode(1);

        $this->assertSame($oldPasswordHash, $admin->fresh()->password);
    }
}
