<?php

namespace Tests\Unit\Rules;

use App\Models\Account;
use App\Models\Trade;
use App\Rules\TradeFrequencyRule;
use Carbon\Carbon;
use Tests\TestCase;

class TradeFrequencyRuleTest extends TestCase
{
    private TradeFrequencyRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new TradeFrequencyRule();
    }

    /**
     * Test that exceeding max trades triggers a violation.
     */
    public function test_exceeding_max_trades_triggers_violation(): void
    {
        $account = Account::factory()->create();

        // Create 5 trades in the last hour
        for ($i = 0; $i < 5; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'open_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        // Create the trade being evaluated
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'open_time' => Carbon::now(),
        ]);

        $result = $this->rule->check($trade, [
            'time_window_minutes' => 60,
            'max_open_trades' => 5, // We now have 6 trades
        ]);

        $this->assertTrue($result, 'Should violate when exceeding max trades');
    }

    /**
     * Test that staying within max trades passes.
     */
    public function test_within_max_trades_passes(): void
    {
        $account = Account::factory()->create();

        // Create 3 trades in the last hour
        for ($i = 0; $i < 3; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'open_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'open_time' => Carbon::now(),
        ]);

        $result = $this->rule->check($trade, [
            'time_window_minutes' => 60,
            'max_open_trades' => 10, // We have 4 trades, under the limit
        ]);

        $this->assertFalse($result, 'Should pass when within max trades');
    }

    /**
     * Test that the time window is respected.
     */
    public function test_respects_time_window(): void
    {
        $account = Account::factory()->create();

        // Create trades outside the time window (2 hours ago)
        for ($i = 0; $i < 10; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'open_time' => Carbon::now()->subHours(2),
            ]);
        }

        // Create the trade being evaluated
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'open_time' => Carbon::now(),
        ]);

        $result = $this->rule->check($trade, [
            'time_window_minutes' => 60, // Only look at last hour
            'max_open_trades' => 5,
        ]);

        $this->assertFalse($result, 'Old trades outside window should not count');
    }

    /**
     * Test min trades threshold.
     */
    public function test_below_min_trades_triggers_violation(): void
    {
        $account = Account::factory()->create();

        // Create only 1 trade (the one being evaluated)
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'open_time' => Carbon::now(),
        ]);

        $result = $this->rule->check($trade, [
            'time_window_minutes' => 60,
            'min_open_trades' => 5, // Require at least 5 trades
        ]);

        $this->assertTrue($result, 'Should violate when below min trades');
    }

    /**
     * Test that accounts are isolated (only count trades from same account).
     */
    public function test_only_counts_trades_from_same_account(): void
    {
        $targetAccount = Account::factory()->create();
        $otherAccount = Account::factory()->create();

        // Create 10 trades for OTHER account
        for ($i = 0; $i < 10; $i++) {
            Trade::factory()->create([
                'account_id' => $otherAccount->id,
                'open_time' => Carbon::now()->subMinutes($i),
            ]);
        }

        // Create the trade being evaluated for target account
        $trade = Trade::factory()->create([
            'account_id' => $targetAccount->id,
            'open_time' => Carbon::now(),
        ]);

        $result = $this->rule->check($trade, [
            'time_window_minutes' => 60,
            'max_open_trades' => 5,
        ]);

        $this->assertFalse($result, 'Should only count trades from the same account');
    }

    /**
     * Test that the rule returns correct type identifier.
     */
    public function test_returns_correct_type(): void
    {
        $this->assertEquals('trade_frequency', TradeFrequencyRule::getType());
    }

    /**
     * Test default time window value.
     */
    public function test_uses_default_time_window(): void
    {
        $account = Account::factory()->create();

        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'open_time' => Carbon::now(),
        ]);

        // Should use default 60 minute window
        $result = $this->rule->check($trade, [
            'max_open_trades' => 10,
        ]);

        $this->assertFalse($result);
    }
}
