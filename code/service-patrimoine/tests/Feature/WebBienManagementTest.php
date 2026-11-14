<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebBienManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/biens')->assertRedirect(route('login'));
        $this->get('/biens/nouveau')->assertRedirect(route('login'));
    }

    public function test_chef_service_can_consult_but_not_manage_biens(): void
    {
        [$categorie, $service] = $this->references();
        $bien = $this->createBien($categorie, $service);
        $this->actingAs(User::factory()->create(['role' => 'CHEF_SERVICE', 'is_active' => true]));

        $this->get('/biens')->assertOk()->assertSee('Ordinateur portable');
        $this->get('/biens/'.$bien->id)->assertOk()->assertSee('INV-0001');
        $this->get('/biens/nouveau')->assertForbidden();
        $this->post('/biens', $this->payload($categorie, $service))->assertForbidden();
        $this->post('/biens/'.$bien->id.'/archiver')->assertForbidden();
    }

    public function test_gestionnaire_can_create_a_bien_from_the_web(): void
    {
        [$categorie, $service] = $this->references();
        $this->actingAs(User::factory()->create(['role' => 'GESTIONNAIRE', 'is_active' => true]));

        $this->get('/biens/nouveau')->assertOk()->assertSee('Enregistrer un bien');

        $response = $this->post('/biens', $this->payload($categorie, $service))
            ->assertRedirect();

        $bien = Bien::query()->sole();
        $response->assertRedirect(route('biens.show', $bien));
        $this->assertDatabaseHas('biens', [
            'numero_inventaire' => 'INV-0001',
            'designation' => 'Ordinateur portable',
        ]);
    }

    public function test_gestionnaire_can_update_and_search_biens(): void
    {
        [$categorie, $service] = $this->references();
        $bien = $this->createBien($categorie, $service);
        $this->actingAs(User::factory()->create(['role' => 'GESTIONNAIRE', 'is_active' => true]));

        $this->patch('/biens/'.$bien->id, [
            'etat' => 'A_REPARER',
            'localisation' => 'Bureau 4',
        ])->assertRedirect(route('biens.show', $bien));

        $this->assertDatabaseHas('biens', ['id' => $bien->id, 'etat' => 'A_REPARER']);

        $this->get('/biens?q=portable')->assertOk()->assertSee('Ordinateur portable');
        $this->get('/biens?q=inexistant')->assertOk()->assertDontSee('Ordinateur portable');
    }

    public function test_admin_can_archive_and_restore_a_bien(): void
    {
        [$categorie, $service] = $this->references();
        $bien = $this->createBien($categorie, $service);
        $this->actingAs(User::factory()->create(['role' => 'ADMIN', 'is_active' => true]));

        $this->post('/biens/'.$bien->id.'/archiver')->assertRedirect(route('biens.show', $bien));
        $this->assertDatabaseHas('biens', ['id' => $bien->id, 'is_active' => false]);

        $this->get('/biens/'.$bien->id.'/modifier')->assertRedirect(route('biens.show', $bien));

        $this->post('/biens/'.$bien->id.'/restaurer')->assertRedirect(route('biens.show', $bien));
        $this->assertDatabaseHas('biens', ['id' => $bien->id, 'is_active' => true]);
    }

    public function test_invalid_payload_is_rejected_with_errors(): void
    {
        [$categorie] = $this->references();
        $this->actingAs(User::factory()->create(['role' => 'GESTIONNAIRE', 'is_active' => true]));

        $this->post('/biens', [
            'numero_inventaire' => 'INV-0001',
            'designation' => 'Sans rattachement',
        ])->assertSessionHasErrors(['categorie_id', 'service_id']);
    }

    /**
     * @return array{0: Categorie, 1: Service}
     */
    private function references(): array
    {
        $categorie = Categorie::create(['libelle' => 'Informatique']);
        $service = Service::create([
            'code_service' => 'MIN-SANTE',
            'nom_service' => 'Ministère de la Santé',
            'type' => 'MINISTERE',
        ]);

        return [$categorie, $service];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function payload(Categorie $categorie, Service $service, array $overrides = []): array
    {
        return array_merge([
            'numero_inventaire' => 'INV-0001',
            'designation' => 'Ordinateur portable',
            'categorie_id' => $categorie->id,
            'service_id' => $service->id,
            'etat' => 'BON_ETAT',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createBien(Categorie $categorie, Service $service, array $overrides = []): Bien
    {
        return Bien::create($this->payload($categorie, $service, $overrides));
    }
}
