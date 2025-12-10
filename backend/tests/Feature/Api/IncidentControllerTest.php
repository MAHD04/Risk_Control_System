<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Incident;
use App\Models\RiskRule;
use Tests\TestCase;

class IncidentControllerTest extends TestCase
{
    /**
     * Test listing incidents with pagination.
     */
    public function test_can_list_incidents_with_pagination(): void
    {
        Incident::factory()->count(15)->create();

        $response = $this->getJson('/api/incidents?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test filtering incidents by account.
     */
    public function test_can_filter_by_account(): void
    {
        $account = Account::factory()->create();
        Incident::factory()->count(3)->create(['account_id' => $account->id]);
        Incident::factory()->count(5)->create(); // Other accounts

        $response = $this->getJson("/api/incidents?account_id={$account->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test filtering incidents by rule.
     */
    public function test_can_filter_by_rule(): void
    {
        $rule = RiskRule::factory()->create();
        Incident::factory()->count(4)->create(['risk_rule_id' => $rule->id]);
        Incident::factory()->count(3)->create(); // Other rules

        $response = $this->getJson("/api/incidents?risk_rule_id={$rule->id}");

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    /**
     * Test getting unread incidents.
     */
    public function test_can_get_unread_incidents(): void
    {
        Incident::factory()->count(5)->create(['read_at' => null]);
        Incident::factory()->count(3)->read()->create();

        $response = $this->getJson('/api/incidents/unread');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['count', 'notifications'],
            ])
            ->assertJson([
                'data' => ['count' => 5],
            ]);
    }

    /**
     * Test marking incident as read.
     */
    public function test_can_mark_incident_as_read(): void
    {
        $incident = Incident::factory()->create(['read_at' => null]);

        $response = $this->postJson("/api/incidents/{$incident->id}/read");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $incident->refresh();
        $this->assertNotNull($incident->read_at);
    }

    /**
     * Test marking all incidents as read.
     */
    public function test_can_mark_all_as_read(): void
    {
        Incident::factory()->count(5)->create(['read_at' => null]);

        $response = $this->postJson('/api/incidents/read-all');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(0, Incident::whereNull('read_at')->count());
    }

    /**
     * Test getting account stats.
     */
    public function test_can_get_account_stats(): void
    {
        $account = Account::factory()->create();
        $rule = RiskRule::factory()->create();
        Incident::factory()->count(5)->create([
            'account_id' => $account->id,
            'risk_rule_id' => $rule->id,
        ]);

        $response = $this->getJson("/api/incidents/account/{$account->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['account_id', 'total_incidents', 'incidents_by_rule'],
            ])
            ->assertJson([
                'data' => [
                    'account_id' => $account->id,
                    'total_incidents' => 5,
                ],
            ]);
    }

    /**
     * Test showing a single incident.
     */
    public function test_can_show_single_incident(): void
    {
        $incident = Incident::factory()->create();

        $response = $this->getJson("/api/incidents/{$incident->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $incident->id],
            ]);
    }
}
