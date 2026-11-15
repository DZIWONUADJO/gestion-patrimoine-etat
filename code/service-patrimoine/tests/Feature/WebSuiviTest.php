<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Inventaire;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebSuiviTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventaire_pages_render_and_follow_workflow(): void
    {
        $bien = $this->contexte();
        $agent = $this->user('GESTIONNAIRE');
        $this->actingAs($agent);

        // Liste vide puis création.
        $this->get('/inventaires')->assertOk()->assertSee('Aucun inventaire');
        $this->get('/inventaires/nouveau')->assertOk();

        $this->post('/inventaires', [
            'libelle' => 'Inventaire bureau Lomé',
            'lieu' => 'Lomé',
            'date' => '2026-11-15',
        ])->assertRedirect(route('inventaires.index'));

        $inventaire = Inventaire::firstOrFail();
        $this->assertSame($agent->id, $inventaire->agent_responsable_id);

        // Page de détail + relevé introuvable.
        $this->get('/inventaires/'.$inventaire->id)->assertOk()
            ->assertSee('Relever un bien')
            ->assertSee($bien->numero_inventaire);

        $this->post('/inventaires/'.$inventaire->id.'/lignes', [
            'bien_id' => $bien->id,
            'statut' => 'INTROUVABLE',
            'commentaire' => 'Absent.',
        ])->assertRedirect();

        $this->assertSame('INTROUVABLE', $bien->refresh()->etat);

        // Un autre utilisateur ne peut pas clôturer.
        $this->actingAs($this->user('GESTIONNAIRE'));
        $this->post('/inventaires/'.$inventaire->id.'/cloturer')->assertForbidden();

        // L'agent clôture.
        $this->actingAs($agent);
        $this->post('/inventaires/'.$inventaire->id.'/cloturer')->assertRedirect();
        $this->assertSame('CLOTUREE', $inventaire->refresh()->statut);
    }

    public function test_bien_page_shows_suivi_forms_for_manager(): void
    {
        $bien = $this->contexte();
        $this->actingAs($this->user('GESTIONNAIRE'));

        $this->get('/biens/'.$bien->id)
            ->assertOk()
            ->assertSee('État et maintenance')
            ->assertSee('Historique des états')
            ->assertSee('Nouvelle intervention');

        // Changement d'état via le formulaire web.
        $this->post('/biens/'.$bien->id.'/etat', [
            'etat' => 'ETAT_MOYEN',
            'commentaire' => 'Rayures sur le capot.',
        ])->assertRedirect();

        $this->assertSame('ETAT_MOYEN', $bien->refresh()->etat);
        $this->assertDatabaseHas('etat_historiques', [
            'bien_id' => $bien->id,
            'vers_etat' => 'ETAT_MOYEN',
        ]);
    }

    public function test_chef_service_sees_readonly_bien_page(): void
    {
        $bien = $this->contexte();
        $this->actingAs($this->user('CHEF_SERVICE'));

        $this->get('/biens/'.$bien->id)
            ->assertOk()
            ->assertDontSee('Nouvelle intervention')
            ->assertDontSee('Changer l’état');
    }

    private function contexte(): Bien
    {
        $categorie = Categorie::create(['libelle' => 'Mobilier']);
        $service = Service::create([
            'code_service' => 'MIN-WEB',
            'nom_service' => 'Ministère web',
            'type' => 'MINISTERE',
        ]);

        return Bien::create([
            'numero_inventaire' => 'INV-WEB-1',
            'designation' => 'Bureau direction',
            'categorie_id' => $categorie->id,
            'service_id' => $service->id,
            'etat' => 'BON_ETAT',
        ]);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => $role,
            'email' => strtolower($role).'+'.uniqid().'@web.local',
            'password' => 'motdepasse',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
