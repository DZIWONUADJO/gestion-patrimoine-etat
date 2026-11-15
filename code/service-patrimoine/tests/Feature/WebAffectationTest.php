<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAffectationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/mouvements')->assertRedirect(route('login'));
    }

    public function test_gestionnaire_proposes_from_the_bien_page(): void
    {
        [$bien, $destination] = $this->contexte();
        $this->actingAs(User::factory()->create(['role' => 'GESTIONNAIRE', 'is_active' => true]));

        $this->get('/biens/'.$bien->id)->assertOk()->assertSee('Affectations et transferts');

        $this->post('/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
            'motif' => 'Transfert vers la direction régionale',
        ])->assertRedirect(route('biens.show', $bien));

        $affectation = Affectation::query()->sole();
        $this->assertSame('EN_ATTENTE', $affectation->statut);
        $this->assertSame($bien->service_id, $affectation->service_origine_id);
    }

    public function test_chef_service_validates_pending_request_from_mouvements(): void
    {
        [$bien, $destination] = $this->contexte();
        $demandeur = User::factory()->create(['role' => 'GESTIONNAIRE', 'is_active' => true]);
        $validateur = User::factory()->create(['role' => 'CHEF_SERVICE', 'is_active' => true]);

        $affectation = Affectation::create([
            'bien_id' => $bien->id,
            'service_origine_id' => $bien->service_id,
            'service_destinataire_id' => $destination->id,
            'motif' => 'Besoin de renfort',
            'date_demande' => now(),
            'demandeur_id' => $demandeur->id,
        ]);

        $this->actingAs($validateur);
        $this->get('/mouvements')->assertOk()
            ->assertSee('Ministère destinataire')
            ->assertSee('Valider');

        $this->post('/mouvements/'.$affectation->id.'/valider')
            ->assertRedirect(route('mouvements.index'));

        $this->assertDatabaseHas('affectations', [
            'id' => $affectation->id,
            'statut' => 'VALIDEE',
            'valideur_id' => $validateur->id,
        ]);
        $this->assertDatabaseHas('biens', [
            'id' => $bien->id,
            'service_id' => $destination->id,
        ]);
    }

    public function test_chef_service_cannot_propose_affectations(): void
    {
        [$bien, $destination] = $this->contexte();
        $this->actingAs(User::factory()->create(['role' => 'CHEF_SERVICE', 'is_active' => true]));

        $this->post('/biens/'.$bien->id.'/affectations', [
            'service_destinataire_id' => $destination->id,
        ])->assertForbidden();
    }

    public function test_demandeur_cannot_validate_own_request(): void
    {
        [$bien, $destination] = $this->contexte();
        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $this->actingAs($admin);

        $affectation = Affectation::create([
            'bien_id' => $bien->id,
            'service_origine_id' => $bien->service_id,
            'service_destinataire_id' => $destination->id,
            'date_demande' => now(),
            'demandeur_id' => $admin->id,
        ]);

        $this->post('/mouvements/'.$affectation->id.'/valider')
            ->assertSessionHasErrors('affectation');
    }

    /**
     * @return array{0: Bien, 1: Service}
     */
    private function contexte(): array
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
            'numero_inventaire' => 'INV-WEB-1',
            'designation' => 'Ordinateur de gestion',
            'categorie_id' => $categorie->id,
            'service_id' => $origine->id,
            'etat' => 'BON_ETAT',
        ]);

        return [$bien, $destination];
    }
}
