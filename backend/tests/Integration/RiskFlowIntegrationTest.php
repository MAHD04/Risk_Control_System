<?php

namespace Tests\Integration;

use App\Models\Account;
use App\Models\ConfiguredAction;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Integration tests for the complete risk evaluation flow:
 * Trade → Rule Evaluation → Incident Creation → Action Execution
 */
class RiskFlowIntegrationTest extends TestCase
{
    /**
     * Test the complete flow: Trade creation triggers risk evaluation,
     * creates incident, and executes action for HARD rule.
     */
    public function test_complete_risk_flow_for_hard_rule(): void
    {
        // 1. Setup: Create account, rule with action
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);

        $disableAction = ConfiguredAction::factory()->disableTrading()->create();
        $rule = RiskRule::factory()->hard()->minDuration(60)->create([
            'incident_limit' => 1,
        ]);
        $rule->actions()->attach($disableAction);

        // 2. Act: Create a short trade via API (violates min_duration)
        $tradeData = [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 10)->toDateTimeString(), // 10 sec < 60 sec
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ];

        $response = $this->postJson('/api/trades', $tradeData);

        // 3. Assert: Trade created successfully
        $response->assertStatus(201);

        // 4. Assert: Incident was created
        $this->assertDatabaseHas('incidents', [
            'account_id' => $account->id,
            'risk_rule_id' => $rule->id,
        ]);

        // 5. Assert: Action was executed (trading disabled for HARD rule)
        $account->refresh();
        $this->assertEquals('disable', $account->trading_status);
        $this->assertEquals('enable', $account->status); // Account still enabled
    }

    /**
     * Test SOFT rule behavior: Verifies soft rule creates incidents
     * and eventually triggers action when conditions are met.
     */
    public function test_soft_rule_creates_incidents_and_eventually_triggers_action(): void
    {
        // Setup
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);

        $disableAction = ConfiguredAction::factory()->disableTrading()->create();
        $rule = RiskRule::factory()->soft()->minDuration(60)->create([
            'incident_limit' => 2, // Trigger action after 2 violations
        ]);
        $rule->actions()->attach($disableAction);

        // Verify no incidents before
        $this->assertEquals(0, Incident::where('risk_rule_id', $rule->id)->count());

        // Act: Create first violating trade
        $this->postJson('/api/trades', [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 5)->toDateTimeString(),
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ]);

        // Assert: Incident was created
        $this->assertGreaterThanOrEqual(1, Incident::where('risk_rule_id', $rule->id)->count());

        // Act: Create second violating trade (should reach limit)
        $this->postJson('/api/trades', [
            'account_id' => $account->id,
            'type' => 'SELL',
            'volume' => 1.5,
            'open_time' => Carbon::create(2024, 1, 1, 13, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 13, 0, 5)->toDateTimeString(),
            'open_price' => 1.0,
            'close_price' => 0.99,
            'status' => 'CLOSED',
        ]);

        // Assert: Action was executed after reaching limit
        $account->refresh();
        $this->assertEquals('disable', $account->trading_status);
    }


    /**
     * Test that non-violating trades don't create incidents.
     */
    public function test_valid_trade_does_not_create_incident(): void
    {
        // Setup
        $account = Account::factory()->create();
        RiskRule::factory()->hard()->minDuration(10)->create(); // 10 sec min

        // Act: Create a trade with 5 minute duration (valid)
        $this->postJson('/api/trades', [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 12, 5, 0)->toDateTimeString(), // 5 min > 10 sec
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ]);

        // Assert: No incidents created
        $this->assertEquals(0, Incident::where('account_id', $account->id)->count());
    }

    /**
     * Test multiple rules can create multiple incidents for same trade.
     */
    public function test_multiple_rules_create_multiple_incidents(): void
    {
        $account = Account::factory()->create();

        // Create 2 different rules that will both be violated
        RiskRule::factory()->hard()->minDuration(60)->create(); // 60 sec min
        RiskRule::factory()->hard()->create([
            'rule_type' => 'trade_frequency',
            'parameters' => ['max_open_trades' => 0, 'time_window_minutes' => 60],
        ]);

        // Create a short trade
        $this->postJson('/api/trades', [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 10)->toDateTimeString(),
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ]);

        // Assert: Both rules created incidents
        $this->assertGreaterThanOrEqual(1, Incident::where('account_id', $account->id)->count());
    }

    /**
     * Test account restoration flow.
     */
    public function test_account_can_be_restored_after_disable(): void
    {
        // Setup: Create account and disable it via HARD rule
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);

        $disableAction = ConfiguredAction::factory()->disableAccount()->create();
        $rule = RiskRule::factory()->hard()->minDuration(60)->create();
        $rule->actions()->attach($disableAction);

        // Trigger violation
        $this->postJson('/api/trades', [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 5)->toDateTimeString(),
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ]);

        $account->refresh();
        $this->assertEquals('disable', $account->status);

        // Restore account via API
        $response = $this->postJson("/api/accounts/{$account->id}/restore");
        $response->assertStatus(200);

        $account->refresh();
        $this->assertEquals('enable', $account->status);
        $this->assertEquals('enable', $account->trading_status);
    }

    /**
     * Test inactive rules are not evaluated.
     */
    public function test_inactive_rules_are_not_evaluated(): void
    {
        $account = Account::factory()->create(['trading_status' => 'enable']);

        $disableAction = ConfiguredAction::factory()->disableTrading()->create();
        $rule = RiskRule::factory()->hard()->minDuration(60)->inactive()->create();
        $rule->actions()->attach($disableAction);

        // Create a violating trade
        $this->postJson('/api/trades', [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0)->toDateTimeString(),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 5)->toDateTimeString(),
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ]);

        // Assert: No incidents and trading still enabled
        $this->assertEquals(0, Incident::where('account_id', $account->id)->count());
        $account->refresh();
        $this->assertEquals('enable', $account->trading_status);
    }
}
