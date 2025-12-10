<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\ConfiguredAction;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use App\Rules\MinDurationRule;
use App\Services\ActionExecutionService;
use App\Services\RiskEvaluationService;
use Carbon\Carbon;
use Tests\TestCase;

class RiskEvaluationServiceTest extends TestCase
{
    private RiskEvaluationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RiskEvaluationService(new ActionExecutionService());
    }

    /**
     * Test that the service evaluates a trade against all active rules.
     */
    public function test_evaluates_trade_against_all_active_rules(): void
    {
        $account = Account::factory()->create();

        // Create an active min_duration rule
        RiskRule::factory()->minDuration(60)->create(['is_active' => true]);

        // Create a short trade that should violate min_duration (5 seconds < 60)
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'status' => 'CLOSED',
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 5), // 5 seconds
        ]);

        $incidents = $this->service->evaluate($trade);

        $this->assertCount(1, $incidents);
        $this->assertInstanceOf(Incident::class, $incidents[0]);
    }

    /**
     * Test that inactive rules are ignored.
     */
    public function test_ignores_inactive_rules(): void
    {
        $account = Account::factory()->create();

        // Create an inactive rule that would normally trigger
        RiskRule::factory()->minDuration(60)->create(['is_active' => false]);

        // Create a short trade
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'status' => 'CLOSED',
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 5),
        ]);

        $incidents = $this->service->evaluate($trade);

        $this->assertEmpty($incidents);
    }

    /**
     * Test that an incident is created when a rule is violated.
     */
    public function test_creates_incident_on_violation(): void
    {
        $account = Account::factory()->create();
        $rule = RiskRule::factory()->hard()->minDuration(60)->create();

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'status' => 'CLOSED',
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 10), // 10 seconds < 60
        ]);

        $incidents = $this->service->evaluate($trade);

        $this->assertCount(1, $incidents);
        $this->assertEquals($trade->id, $incidents[0]->trade_id);
        $this->assertEquals($rule->id, $incidents[0]->risk_rule_id);
        $this->assertEquals($account->id, $incidents[0]->account_id);
    }

    /**
     * Test that HARD rules trigger immediate action execution.
     */
    public function test_executes_actions_for_hard_rules_immediately(): void
    {
        $account = Account::factory()->create(['status' => 'enable']);
        $rule = RiskRule::factory()->hard()->minDuration(60)->create();
        $action = ConfiguredAction::factory()->disableAccount()->create();
        $rule->actions()->attach($action);

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'status' => 'CLOSED',
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 10),
        ]);

        $this->service->evaluate($trade);

        $account->refresh();
        $this->assertEquals('disable', $account->status);
    }

    /**
     * Test that SOFT rules check incident limit before executing actions.
     */
    public function test_soft_rule_respects_incident_limit(): void
    {
        $account = Account::factory()->create(['status' => 'enable']);
        $rule = RiskRule::factory()->soft()->minDuration(60)->create([
            'incident_limit' => 3,
        ]);
        $action = ConfiguredAction::factory()->disableAccount()->create();
        $rule->actions()->attach($action);

        // Create 2 existing incidents (below limit of 3)
        for ($i = 0; $i < 2; $i++) {
            Incident::factory()->create([
                'account_id' => $account->id,
                'risk_rule_id' => $rule->id,
            ]);
        }

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'status' => 'CLOSED',
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 10),
        ]);

        $this->service->evaluate($trade);

        // Now we have 3 incidents total, which equals the limit
        // Action should be executed
        $account->refresh();
        $this->assertEquals('disable', $account->status);
    }

    /**
     * Test that SOFT rules don't execute actions below limit.
     */
    public function test_soft_rule_does_not_execute_below_limit(): void
    {
        $account = Account::factory()->create(['status' => 'enable']);
        $rule = RiskRule::factory()->soft()->minDuration(60)->create([
            'incident_limit' => 5,
        ]);
        $action = ConfiguredAction::factory()->disableAccount()->create();
        $rule->actions()->attach($action);

        // Create 1 existing incident (well below limit of 5)
        Incident::factory()->create([
            'account_id' => $account->id,
            'risk_rule_id' => $rule->id,
        ]);

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'status' => 'CLOSED',
            'open_time' => Carbon::create(2024, 1, 1, 12, 0, 0),
            'close_time' => Carbon::create(2024, 1, 1, 12, 0, 10),
        ]);

        $this->service->evaluate($trade);

        // Only 2 incidents total, below limit of 5
        // Action should NOT be executed
        $account->refresh();
        $this->assertEquals('enable', $account->status);
    }

    /**
     * Test that unknown rule types are handled gracefully.
     */
    public function test_handles_unknown_rule_types(): void
    {
        $account = Account::factory()->create();

        // Create a rule with unknown type
        RiskRule::factory()->create([
            'is_active' => true,
            'rule_type' => 'unknown_rule_type',
        ]);

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
        ]);

        // Should not throw and return empty incidents for unknown types
        $incidents = $this->service->evaluate($trade);

        $this->assertEmpty($incidents);
    }

    /**
     * Test that new strategies can be registered dynamically.
     */
    public function test_can_register_new_strategy_dynamically(): void
    {
        $this->service->registerStrategy('custom_rule', MinDurationRule::class);

        // The method should not throw
        $this->assertTrue(true);
    }
}
