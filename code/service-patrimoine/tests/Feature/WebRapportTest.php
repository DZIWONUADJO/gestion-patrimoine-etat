<?php

namespace Tests\Feature;

use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRapportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_rapports(): void
    {
        $this->get('/rapports')->assertRedirect(route('login'));
        $this->get('/rapports/biens')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_biens_pdf_and_csv(): void
    {
        $this->contexte();
        $this->actingAs($this->user('CHEF_SERVICE'));

        $this->get('/rapports')->assertOk()->assertSee('Inventaire des biens');

        $this->get('/rapports/biens')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $response = $this->get('/rapports/biens?format=csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('INV-RAP-1', $response->streamedContent());
    }

    public function test_mouvements_report_exports_csv(): void
    {
        $this->contexte();
        $this->actingAs($this->user('ADMIN'));

        $this->get('/rapports/mouvements?format=csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dashboard_shows_activity_indicators(): void
    {
        $bien = $this->contexte();
        $this->actingAs($this->user('GESTIONNAIRE'));

        $bien->update(['etat' => 'EN_REPARATION']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Mouvements en attente')
            ->assertSee('Interventions en cours')
            ->assertSee('Inventaires en cours')
            ->assertSee('Répartition des biens actifs par état')
            ->assertSee('Derniers mouvements')
            ->assertSee('Anomalies d’inventaire récentes');
    }

    private function contexte(): Bien
    {
        $categorie = Categorie::create(['libelle' => 'Véhicules']);
        $service = Service::create([
            'code_service' => 'MIN-RAP',
            'nom_service' => 'Ministère des rapports',
            'type' => 'MINISTERE',
        ]);

        return Bien::create([
            'numero_inventaire' => 'INV-RAP-1',
            'designation' => 'Camionnette de liaison',
            'categorie_id' => $categorie->id,
            'service_id' => $service->id,
            'etat' => 'BON_ETAT',
        ]);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => $role,
            'email' => strtolower($role).'+'.uniqid().'@rap.local',
            'password' => 'motdepasse',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
