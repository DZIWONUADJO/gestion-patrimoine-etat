<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AffectationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_affectations(): void
    {
        $this->getJson('/api/affectations')->assertUnauthorized();
    }

    public function test_chef_service_can_read_but_not_propose(): void
    {
        [$bien, $destination] = $this->contexte();
        $this->actingAsRole('CHEF_SERVICE');

        $this->getJson('/api/affectations')->assertOk();
        $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertForbidden();
    }

    public function test_gestionnaire_proposes_and_admin_validates(): void
    {
        [$bien, $destination] = $this->contexte();
        $gestionnaire = $this->user('GESTIONNAIRE');
        Sanctum::actingAs($gestionnaire);

        $response = $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
            'motif' => 'Renfort de la direction régionale',
            'date_effet' => '2026-12-01',
        ])->assertCreated()
            ->assertJsonPath('data.statut', 'EN_ATTENTE')
            ->assertJsonPath('data.service_origine_id', $bien->service_id)
            ->assertJsonPath('data.demandeur.id', $gestionnaire->id);

        $affectationId = $response->json('data.id');

        // Une seule demande en attente par bien.
        $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['bien']);

        // Le gestionnaire ne peut pas valider.
        $this->postJson('/api/affectations/'.$affectationId.'/valider')->assertForbidden();

        Sanctum::actingAs($this->user('ADMIN'));
        $this->postJson('/api/affectations/'.$affectationId.'/valider')
            ->assertOk()
            ->assertJsonPath('data.statut', 'VALIDEE')
            ->assertJsonPath('data.date_effet', '2026-12-01');

        // Le bien suit le nouveau service responsable.
        $this->assertDatabaseHas('biens', [
            'id' => $bien->id,
            'service_id' => $destination->id,
        ]);
    }

    public function test_validation_closes_the_previous_active_affectation(): void
    {
        [$bien, $destination, $tiers] = $this->contexte(true);
        $gestionnaire = $this->user('GESTIONNAIRE');
        Sanctum::actingAs($gestionnaire);

        $premiere = $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($this->user('CHEF_SERVICE'));
        $this->postJson('/api/affectations/'.$premiere.'/valider')->assertOk();

        // Nouveau transfert vers un troisième service.
        Sanctum::actingAs($gestionnaire);
        $seconde = $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $tiers->id,
            'date_effet' => '2026-11-20',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($this->user('ADMIN'));
        $this->postJson('/api/affectations/'.$seconde.'/valider')->assertOk();

        $this->assertDatabaseHas('affectations', [
            'id' => $premiere,
            'statut' => 'VALIDEE',
            'date_fin' => '2026-11-20',
        ]);
        $this->assertDatabaseHas('affectations', [
            'id' => $seconde,
            'statut' => 'VALIDEE',
            'date_fin' => null,
            'service_origine_id' => $destination->id,
        ]);
        $this->assertDatabaseHas('biens', [
            'id' => $bien->id,
            'service_id' => $tiers->id,
        ]);

        // L'historique complet reste consultable.
        Sanctum::actingAs($this->user('CHEF_SERVICE'));
        $this->getJson('/api/affectations?bien_id='.$bien->id)
            ->assertOk()
            ->assertJsonCount(2, 'data.data');
    }

    public function test_demander_ne_peut_pas_valider_sa_propre_demande(): void
    {
        [$bien, $destination] = $this->contexte();
        $admin = $this->user('ADMIN');
        Sanctum::actingAs($admin);

        $affectationId = $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/affectations/'.$affectationId.'/valider')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['affectation']);
    }

    public function test_rejet_exige_un_motif_et_annulation_libere_le_bien(): void
    {
        [$bien, $destination] = $this->contexte();
        $gestionnaire = $this->user('GESTIONNAIRE');
        Sanctum::actingAs($gestionnaire);

        $affectationId = $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($this->user('CHEF_SERVICE'));
        $this->postJson('/api/affectations/'.$affectationId.'/rejeter')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['motif_decision']);

        $this->postJson('/api/affectations/'.$affectationId.'/rejeter', [
            'motif_decision' => 'Besoin non justifié cette année.',
        ])->assertOk()->assertJsonPath('data.statut', 'REJETEE');

        // Après rejet, une nouvelle demande est possible.
        Sanctum::actingAs($gestionnaire);
        $nouvelle = $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertCreated()->json('data.id');

        // Le demandeur peut annuler sa propre demande.
        $this->postJson('/api/affectations/'.$nouvelle.'/annuler')->assertOk()
            ->assertJsonPath('data.statut', 'ANNULEE');

        // Une demande déjà traitée ne peut plus être modifiée.
        Sanctum::actingAs($this->user('CHEF_SERVICE'));
        $this->postJson('/api/affectations/'.$affectationId.'/valider')->assertUnprocessable();
    }

    public function test_destination_inactive_ou_identique_est_refusee(): void
    {
        [$bien, $destination] = $this->contexte();
        Sanctum::actingAs($this->user('GESTIONNAIRE'));

        // Le service actuel du bien est sa destination : refus.
        $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $bien->service_id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['service_destinataire_id']);

        $destination->update(['is_active' => false]);
        $this->postJson('/api/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['service_destinataire_id']);
    }

    /**
     * @return array{0: Bien, 1: Service, 2?: Service}
     */
    private function contexte(bool $avecTiers = false): array
    {
        $categorie = Categorie::create(['libelle' => 'Informatique']);
        $origine = Service::create([
            'code_service' => 'MIN-ORIGINE',
            'nom_service' => 'Ministère d’origine',
            'type' => 'MINISTERE',
        ]);
        $destination = Service::create([
            'code_service' => 'MIN-DEST',
            'nom_service' => 'Ministère destinataire',
            'type' => 'MINISTERE',
        ]);

        $bien = Bien::create([
            'numero_inventaire' => 'INV-AFF-1',
            'designation' => 'Véhicule de service',
            'categorie_id' => $categorie->id,
            'service_id' => $origine->id,
            'etat' => 'BON_ETAT',
        ]);

        $resultat = [$bien, $destination];

        if ($avecTiers) {
            $resultat[] = Service::create([
                'code_service' => 'MIN-TIERS',
                'nom_service' => 'Ministère tiers',
                'type' => 'MINISTERE',
            ]);
        }

        return $resultat;
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => $role,
            'email' => strtolower($role).'+'.uniqid().'@test.local',
            'password' => 'motdepasse',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function actingAsRole(string $role): void
    {
        Sanctum::actingAs($this->user($role));
    }
}
