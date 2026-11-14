<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_reference_data(): void
    {
        $this->getJson('/api/categories')->assertUnauthorized();
        $this->getJson('/api/services')->assertUnauthorized();
    }

    public function test_agent_can_read_but_cannot_change_categories(): void
    {
        $this->actingAsRole('CHEF_SERVICE');

        $this->getJson('/api/categories')->assertOk();
        $this->postJson('/api/categories', [
            'libelle' => 'Informatique',
        ])->assertForbidden();
    }

    public function test_inactive_user_cannot_read_reference_data(): void
    {
        $this->actingAsRole('ADMIN', false);

        $this->getJson('/api/categories')->assertUnauthorized();
    }

    public function test_admin_can_create_and_update_categories(): void
    {
        $this->actingAsRole('ADMIN');

        $response = $this->postJson('/api/categories', [
            'libelle' => 'Informatique',
            'description' => 'Ordinateurs et réseaux.',
        ])->assertCreated()
            ->assertJsonPath('data.libelle', 'Informatique')
            ->assertJsonPath('data.is_active', true);

        $categoryId = $response->json('data.id');

        $this->postJson('/api/categories', [
            'libelle' => 'Informatique',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['libelle']);

        $this->patchJson('/api/categories/'.$categoryId, [
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.is_active', false);
    }

    public function test_admin_can_create_a_service_hierarchy_and_filter_roots(): void
    {
        $this->actingAsRole('ADMIN');

        $ministry = $this->postJson('/api/services', [
            'code_service' => 'MIN-SANTE',
            'nom_service' => 'Ministère de la Santé',
            'type' => 'MINISTERE',
        ])->assertCreated()->json('data');

        $this->postJson('/api/services', [
            'code_service' => 'DIR-SANTE-LOME',
            'nom_service' => 'Direction régionale de Lomé',
            'type' => 'DIRECTION_REGIONALE',
            'parent_id' => $ministry['id'],
        ])->assertCreated()
            ->assertJsonPath('data.parent_id', $ministry['id']);

        $this->getJson('/api/services?parent_id=')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $ministry['id']);
    }

    public function test_service_cannot_be_moved_under_its_own_descendant(): void
    {
        $this->actingAsRole('ADMIN');
        $ministry = Service::create([
            'code_service' => 'MIN-ECONOMIE',
            'nom_service' => 'Ministère de l’Économie',
            'type' => 'MINISTERE',
        ]);
        $direction = Service::create([
            'code_service' => 'DIR-BUDGET',
            'nom_service' => 'Direction du budget',
            'type' => 'DIRECTION_GENERALE',
            'parent_id' => $ministry->id,
        ]);

        $this->patchJson('/api/services/'.$ministry->id, [
            'parent_id' => $direction->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id']);

        $this->assertDatabaseHas('services', [
            'id' => $ministry->id,
            'parent_id' => null,
        ]);
    }

    public function test_non_admin_cannot_create_services(): void
    {
        $this->actingAsRole('GESTIONNAIRE');

        $this->postJson('/api/services', [
            'code_service' => 'DIR-TEST',
            'nom_service' => 'Direction test',
            'type' => 'DIRECTION_GENERALE',
        ])->assertForbidden();
    }

    private function actingAsRole(string $role, bool $isActive = true): void
    {
        $user = new User;
        $user->forceFill([
            'id' => 1,
            'role' => $role,
            'is_active' => $isActive,
        ]);

        Sanctum::actingAs($user);
    }
}
