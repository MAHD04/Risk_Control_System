<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    /**
     * Test getting dashboard stats.
     */
    public function test_returns_dashboard_stats(): void
    {
        // Create test data
        RiskRule::factory()->count(3)->create(['is_active' => true]);
        Account::factory()->count(4)->create(['status' => 'enable']);
        Trade::factory()->count(5)->create(['status' => 'OPEN']);
        Incident::factory()->count(10)->create();

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'active_rules',
                    'total_incidents',
                    'incidents_today',
                    'active_accounts',
                    'disabled_accounts',
                    'open_trades',
                ],
            ])
            ->assertJson(['success' => true]);

        // Verify values are at least what we created (may include data from other tests)
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(3, $data['active_rules']);
        $this->assertGreaterThanOrEqual(10, $data['total_incidents']);
        $this->assertGreaterThanOrEqual(4, $data['active_accounts']);
        $this->assertGreaterThanOrEqual(5, $data['open_trades']);
    }

    /**
     * Test getting incident activity data.
     */
    public function test_returns_incident_activity(): void
    {
        $response = $this->getJson('/api/dashboard/incident-activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'chart_data',
                    'percentage_change',
                ],
            ]);
    }

    /**
     * Test getting recent incidents.
     */
    public function test_returns_recent_incidents(): void
    {
        Incident::factory()->count(10)->create();

        $response = $this->getJson('/api/dashboard/recent-incidents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        // Should return max 5 recent incidents
        $this->assertLessThanOrEqual(5, count($response->json('data')));
    }

    /**
     * Test getting system status.
     */
    public function test_returns_system_status(): void
    {
        RiskRule::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/dashboard/system-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'api_connection',
                    'rule_engine',
                    'event_listener',
                    'last_sync',
                ],
            ])
            ->assertJson([
                'data' => [
                    'api_connection' => 'Connected',
                    'rule_engine' => 'Active',
                    'event_listener' => 'Running',
                ],
            ]);
    }

    /**
     * Test system status with no active rules.
     */
    public function test_returns_inactive_rule_engine_status(): void
    {
        RiskRule::factory()->inactive()->create();

        $response = $this->getJson('/api/dashboard/system-status');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'rule_engine' => 'Inactive',
                ],
            ]);
    }
}
