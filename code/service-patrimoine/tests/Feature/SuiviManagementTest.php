<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuiviManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_suivi_endpoints(): void
    {
        $bien = $this->contexte();

        $this->getJson('/api/biens/'.$bien->id.'/historiques-etats')->assertUnauthorized();
        $this->postJson('/api/biens/'.$bien->id.'/etat', ['etat' => 'ETAT_MOYEN'])->assertUnauthorized();
        $this->postJson('/api/biens/'.$bien->id.'/interventions', [
            'description' => 'Test',
            'date_debut' => '2026-11-15',
        ])->assertUnauthorized();
        $this->getJson('/api/inventaires')->assertUnauthorized();
    }

    public function test_chef_service_cannot_change_state_or_manage_interventions(): void
    {
        $bien = $this->contexte();
        Sanctum::actingAs($this->user('CHEF_SERVICE'));

        $this->postJson('/api/biens/'.$bien->id.'/etat', ['etat' => 'ETAT_MOYEN'])->assertForbidden();
        $this->postJson('/api/biens/'.$bien->id.'/interventions', [
            'description' => 'Réparation écran',
            'date_debut' => '2026-11-15',
        ])->assertForbidden();
    }

    public function test_state_change_is_historized(): void
    {
        $bien = $this->contexte();
        $gestionnaire = $this->user('GESTIONNAIRE');
        Sanctum::actingAs($gestionnaire);

        $this->postJson('/api/biens/'.$bien->id.'/etat', [
            'etat' => 'ETAT_MOYEN',
            'commentaire' => 'Usure normale constatée.',
        ])->assertCreated()
            ->assertJsonPath('data.etat', 'ETAT_MOYEN');

        $this->assertDatabaseHas('etat_historiques', [
            'bien_id' => $bien->id,
            'de_etat' => 'BON_ETAT',
            'vers_etat' => 'ETAT_MOYEN',
            'auteur_id' => $gestionnaire->id,
            'commentaire' => 'Usure normale constatée.',
        ]);

        // Même état : refus.
        $this->postJson('/api/biens/'.$bien->id.'/etat', ['etat' => 'ETAT_MOYEN'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['etat']);

        Sanctum::actingAs($this->user('CHEF_SERVICE'));
        $this->getJson('/api/biens/'.$bien->id.'/historiques-etats')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_intervention_lifecycle_moves_state_and_restores(): void
    {
        $bien = $this->contexte();
        Sanctum::actingAs($this->user('GESTIONNAIRE'));

        $interventionId = $this->postJson('/api/biens/'.$bien->id.'/interventions', [
            'type' => 'REPARATION',
            'description' => 'Remplacement de la batterie',
            'prestataire' => 'Atelier Lomé',
            'cout' => 45000,
            'date_debut' => '2026-11-15',
        ])->assertCreated()
            ->assertJsonPath('data.statut', 'EN_COURS')
            ->assertJsonPath('data.etat_precedent', 'BON_ETAT')
            ->json('data.id');

        $bien->refresh();
        $this->assertSame('EN_REPARATION', $bien->etat);
        $this->assertDatabaseHas('etat_historiques', [
            'bien_id' => $bien->id,
            'vers_etat' => 'EN_REPARATION',
        ]);

        // Une seule intervention en cours par bien.
        $this->postJson('/api/biens/'.$bien->id.'/interventions', [
            'description' => 'Autre intervention',
            'date_debut' => '2026-11-15',
        ])->assertUnprocessable();

        // Clôture : le bien retrouve son état antérieur.
        Sanctum::actingAs($this->user('ADMIN'));
        $this->postJson('/api/interventions/'.$interventionId.'/terminer', [
            'commentaire' => 'Batterie remplacée.',
        ])->assertOk()->assertJsonPath('data.statut', 'TERMINEE');

        $this->assertSame('BON_ETAT', $bien->refresh()->etat);
        $this->assertDatabaseHas('etat_historiques', [
            'bien_id' => $bien->id,
            'de_etat' => 'EN_REPARATION',
            'vers_etat' => 'BON_ETAT',
        ]);
    }

    public function test_cancelling_intervention_restores_previous_state(): void
    {
        $bien = $this->contexte();
        Sanctum::actingAs($this->user('GESTIONNAIRE'));

        $interventionId = $this->postJson('/api/biens/'.$bien->id.'/interventions', [
            'description' => 'Visite technique',
            'date_debut' => '2026-11-15',
        ])->assertCreated()->json('data.id');

        $this->assertSame('EN_REPARATION', $bien->refresh()->etat);

        $this->postJson('/api/interventions/'.$interventionId.'/annuler')->assertOk()
            ->assertJsonPath('data.statut', 'ANNULEE');

        $this->assertSame('BON_ETAT', $bien->refresh()->etat);
    }

    public function test_inventaire_lifecycle_and_introuvable_marks_bien(): void
    {
        $bien = $this->contexte();
        $agent = $this->user('GESTIONNAIRE');
        Sanctum::actingAs($agent);

        $inventaireId = $this->postJson('/api/inventaires', [
            'libelle' => 'Inventaire test',
            'lieu' => 'Lomé',
            'date' => '2026-11-15',
        ])->assertCreated()
            ->assertJsonPath('data.statut', 'EN_COURS')
            ->assertJsonPath('data.agent_responsable.id', $agent->id)
            ->json('data.id');

        // Relevé présent.
        $this->postJson('/api/inventaires/'.$inventaireId.'/lignes', [
            'bien_id' => $bien->id,
            'statut' => 'PRESENT',
        ])->assertCreated()->assertJsonPath('data.statut', 'PRESENT');

        // Re-relevé corrige le constat sans dupliquer.
        $this->postJson('/api/inventaires/'.$inventaireId.'/lignes', [
            'bien_id' => $bien->id,
            'statut' => 'ANOMALIE',
            'commentaire' => 'N° de série illisible',
        ])->assertOk()->assertJsonPath('data.statut', 'ANOMALIE');
        $this->assertDatabaseCount('inventaire_lignes', 1);

        // Recherche par numéro d'inventaire.
        $this->postJson('/api/inventaires/'.$inventaireId.'/lignes', [
            'numero_inventaire' => $bien->numero_inventaire,
            'statut' => 'INTROUVABLE',
            'commentaire' => 'Absent du bureau.',
        ])->assertOk()->assertJsonPath('data.statut', 'INTROUVABLE');

        // Le bien passe à INTROUVABLE avec historique.
        $this->assertSame('INTROUVABLE', $bien->refresh()->etat);
        $this->assertDatabaseHas('etat_historiques', [
            'bien_id' => $bien->id,
            'vers_etat' => 'INTROUVABLE',
            'auteur_id' => $agent->id,
        ]);

        // Un autre utilisateur ne peut pas clôturer.
        Sanctum::actingAs($this->user('GESTIONNAIRE'));
        $this->postJson('/api/inventaires/'.$inventaireId.'/cloturer')->assertForbidden();

        // L'agent responsable clôture.
        Sanctum::actingAs($agent);
        $this->postJson('/api/inventaires/'.$inventaireId.'/cloturer')->assertOk()
            ->assertJsonPath('data.statut', 'CLOTUREE');

        // Plus de relevé possible.
        $this->postJson('/api/inventaires/'.$inventaireId.'/lignes', [
            'bien_id' => $bien->id,
            'statut' => 'PRESENT',
        ])->assertUnprocessable();

        // Détail complet consultable.
        Sanctum::actingAs($this->user('CHEF_SERVICE'));
        $this->getJson('/api/inventaires/'.$inventaireId)
            ->assertOk()
            ->assertJsonCount(1, 'data.lignes');
    }

    public function test_state_change_rejected_on_archived_bien(): void
    {
        $bien = $this->contexte();
        $bien->update(['is_active' => false]);
        Sanctum::actingAs($this->user('ADMIN'));

        $this->postJson('/api/biens/'.$bien->id.'/etat', ['etat' => 'HORS_SERVICE'])
            ->assertUnprocessable();
    }

    private function contexte(): Bien
    {
        $categorie = Categorie::create(['libelle' => 'Informatique']);
        $service = Service::create([
            'code_service' => 'MIN-SUIVI',
            'nom_service' => 'Ministère du suivi',
            'type' => 'MINISTERE',
        ]);

        return Bien::create([
            'numero_inventaire' => 'INV-SUIVI-1',
            'designation' => 'Ordinateur portable',
            'categorie_id' => $categorie->id,
            'service_id' => $service->id,
            'etat' => 'BON_ETAT',
        ]);
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
}
