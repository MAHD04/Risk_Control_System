<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\ConfiguredAction;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Services\ActionExecutionService;
use Tests\TestCase;

class ActionExecutionServiceTest extends TestCase
{
    private ActionExecutionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ActionExecutionService();
    }

    /**
     * Test that DISABLE_ACCOUNT action updates account status.
     */
    public function test_disables_account_correctly(): void
    {
        $account = Account::factory()->create(['status' => 'enable']);
        $rule = RiskRule::factory()->create();
        $action = ConfiguredAction::factory()->disableAccount()->create();
        $rule->actions()->attach($action);
        $incident = Incident::factory()->forAccount($account)->forRule($rule)->create();

        $this->service->executeActionsForRule($rule, $incident, $account);

        $account->refresh();
        $this->assertEquals('disable', $account->status);
    }

    /**
     * Test that DISABLE_TRADING action updates trading status.
     */
    public function test_disables_trading_correctly(): void
    {
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);
        $rule = RiskRule::factory()->create();
        $action = ConfiguredAction::factory()->disableTrading()->create();
        $rule->actions()->attach($action);
        $incident = Incident::factory()->forAccount($account)->forRule($rule)->create();

        $this->service->executeActionsForRule($rule, $incident, $account);

        $account->refresh();
        $this->assertEquals('enable', $account->status); // Account still enabled
        $this->assertEquals('disable', $account->trading_status); // Trading disabled
    }

    /**
     * Test that rules without actions complete without error.
     */
    public function test_handles_rules_without_actions(): void
    {
        $account = Account::factory()->create();
        $rule = RiskRule::factory()->create(); // No actions attached
        $incident = Incident::factory()->forAccount($account)->forRule($rule)->create();

        // Should not throw
        $this->service->executeActionsForRule($rule, $incident, $account);

        // Account should remain unchanged
        $account->refresh();
        $this->assertEquals('enable', $account->status);
    }

    /**
     * Test that multiple actions are all executed.
     */
    public function test_executes_multiple_actions(): void
    {
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);
        $rule = RiskRule::factory()->create();

        $disableAccountAction = ConfiguredAction::factory()->disableAccount()->create();
        $disableTradingAction = ConfiguredAction::factory()->disableTrading()->create();

        $rule->actions()->attach([$disableAccountAction->id, $disableTradingAction->id]);
        $incident = Incident::factory()->forAccount($account)->forRule($rule)->create();

        $this->service->executeActionsForRule($rule, $incident, $account);

        $account->refresh();
        $this->assertEquals('disable', $account->status);
        $this->assertEquals('disable', $account->trading_status);
    }

    /**
     * Test that EMAIL action executes (mock action - just verifies no error).
     */
    public function test_email_action_executes_without_error(): void
    {
        $account = Account::factory()->create();
        $rule = RiskRule::factory()->create();
        $action = ConfiguredAction::factory()->email('test@example.com', 'Alert!')->create();
        $rule->actions()->attach($action);
        $incident = Incident::factory()->forAccount($account)->forRule($rule)->create();

        // Should not throw - email is logged, not actually sent
        $this->service->executeActionsForRule($rule, $incident, $account);

        $this->assertTrue(true); // Just verifying no exception
    }

    /**
     * Test that SLACK action executes (mock action - just verifies no error).
     */
    public function test_slack_action_executes_without_error(): void
    {
        $account = Account::factory()->create();
        $rule = RiskRule::factory()->create();
        $action = ConfiguredAction::factory()->slack('#alerts')->create();
        $rule->actions()->attach($action);
        $incident = Incident::factory()->forAccount($account)->forRule($rule)->create();

        // Should not throw - slack is logged, not actually sent
        $this->service->executeActionsForRule($rule, $incident, $account);

        $this->assertTrue(true); // Just verifying no exception
    }
}
