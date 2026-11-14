<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BienManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_biens(): void
    {
        $this->getJson('/api/biens')->assertUnauthorized();
    }

    public function test_inactive_user_cannot_read_biens(): void
    {
        $this->actingAsRole('ADMIN', false);

        $this->getJson('/api/biens')->assertUnauthorized();
    }

    public function test_chef_service_can_read_but_not_manage_biens(): void
    {
        [$categorie, $service] = $this->references();
        $this->actingAsRole('CHEF_SERVICE');

        $bien = $this->createBien($categorie, $service);

        $this->getJson('/api/biens')->assertOk()
            ->assertJsonCount(1, 'data.data');
        $this->getJson('/api/biens/'.$bien->id)->assertOk();
        $this->postJson('/api/biens', $this->payload($categorie, $service))
            ->assertForbidden();
    }

    public function test_gestionnaire_can_create_and_update_a_bien(): void
    {
        [$categorie, $service] = $this->references();
        $this->actingAsRole('GESTIONNAIRE');

        $response = $this->postJson('/api/biens', $this->payload($categorie, $service))
            ->assertCreated()
            ->assertJsonPath('data.numero_inventaire', 'INV-0001')
            ->assertJsonPath('data.etat', 'BON_ETAT')
            ->assertJsonPath('data.categorie.libelle', $categorie->libelle);

        $bienId = $response->json('data.id');

        $this->postJson('/api/biens', $this->payload($categorie, $service))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['numero_inventaire']);

        $this->patchJson('/api/biens/'.$bienId, [
            'etat' => 'ETAT_MOYEN',
            'localisation' => 'Bureau 12, Lomé',
        ])->assertOk()
            ->assertJsonPath('data.etat', 'ETAT_MOYEN')
            ->assertJsonPath('data.localisation', 'Bureau 12, Lomé');
    }

    public function test_bien_requires_known_category_service_and_valid_state(): void
    {
        [$categorie, $service] = $this->references();
        $this->actingAsRole('GESTIONNAIRE');

        $this->postJson('/api/biens', $this->payload($categorie, $service, [
            'categorie_id' => 9999,
            'etat' => 'CASSE',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['categorie_id', 'etat']);
    }

    public function test_index_filters_by_search_state_and_references(): void
    {
        [$categorie, $service] = $this->references();
        $autreCategorie = Categorie::create(['libelle' => 'Véhicules']);
        $this->actingAsRole('GESTIONNAIRE');

        $ordinateur = $this->createBien($categorie, $service);
        $this->createBien($autreCategorie, $service, [
            'numero_inventaire' => 'VEH-0001',
            'designation' => 'Véhicule de service',
            'etat' => 'EN_REPARATION',
        ]);

        $this->getJson('/api/biens?q=ordinateur')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $ordinateur->id);

        $this->getJson('/api/biens?etat=EN_REPARATION')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.numero_inventaire', 'VEH-0001');

        $this->getJson('/api/biens?categorie_id='.$autreCategorie->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_only_admin_can_archive_and_restore_a_bien(): void
    {
        [$categorie, $service] = $this->references();
        $this->actingAsRole('GESTIONNAIRE');
        $bien = $this->createBien($categorie, $service);

        $this->postJson('/api/biens/'.$bien->id.'/archiver')->assertForbidden();

        $this->actingAsRole('ADMIN');
        $this->postJson('/api/biens/'.$bien->id.'/archiver')
            ->assertOk()
            ->assertJsonPath('data.is_active', false)
            ->assertJsonStructure(['data' => ['archive_le']]);

        $this->patchJson('/api/biens/'.$bien->id, ['etat' => 'HORS_SERVICE'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['bien']);

        $this->postJson('/api/biens/'.$bien->id.'/restaurer')
            ->assertOk()
            ->assertJsonPath('data.is_active', true);
    }

    public function test_gestionnaire_can_attach_and_remove_justificatifs(): void
    {
        Storage::fake('local');
        [$categorie, $service] = $this->references();
        $this->actingAsRole('GESTIONNAIRE');
        $bien = $this->createBien($categorie, $service);

        $response = $this->post('/api/biens/'.$bien->id.'/documents', [
            'fichier' => UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf'),
        ])->assertCreated()
            ->assertJsonPath('data.nom', 'facture.pdf');

        $documentId = $response->json('data.id');

        $this->getJson('/api/biens/'.$bien->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.documents');

        $this->deleteJson('/api/biens/'.$bien->id.'/documents/'.$documentId)
            ->assertNoContent();

        $this->assertDatabaseMissing('bien_documents', ['id' => $documentId]);
    }

    public function test_document_of_another_bien_cannot_be_deleted(): void
    {
        Storage::fake('local');
        [$categorie, $service] = $this->references();
        $this->actingAsRole('GESTIONNAIRE');
        $bien = $this->createBien($categorie, $service);
        $autre = $this->createBien($categorie, $service, [
            'numero_inventaire' => 'INV-0002',
        ]);

        $document = $autre->documents()->create([
            'nom' => 'doc.pdf',
            'chemin' => 'biens/'.$autre->id.'/doc.pdf',
        ]);

        $this->deleteJson('/api/biens/'.$bien->id.'/documents/'.$document->id)
            ->assertNotFound();
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
            'valeur_acquisition' => 850000,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createBien(Categorie $categorie, Service $service, array $overrides = []): Bien
    {
        return Bien::create($this->payload($categorie, $service, $overrides));
    }

    private function actingAsRole(string $role, bool $isActive = true): void
    {
        $user = User::create([
            'name' => $role,
            'email' => strtolower($role).'+'.uniqid().'@test.local',
            'password' => 'motdepasse',
            'role' => $role,
            'is_active' => $isActive,
        ]);

        Sanctum::actingAs($user);
    }
}
