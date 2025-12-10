<?php

namespace Tests\Unit\Rules;

use App\Models\Account;
use App\Models\Trade;
use App\Rules\VolumeConsistencyRule;
use Carbon\Carbon;
use Tests\TestCase;

class VolumeConsistencyRuleTest extends TestCase
{
    private VolumeConsistencyRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new VolumeConsistencyRule();
    }

    /**
     * Test that volume spike is detected.
     */
    public function test_detects_volume_spike(): void
    {
        $account = Account::factory()->create();

        // Create historical trades with avg volume of 1.0
        for ($i = 0; $i < 5; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'volume' => 1.0,
                'status' => 'CLOSED',
                'close_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        // Create trade with 5x the average volume
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'volume' => 5.0, // 5x the average of 1.0
            'status' => 'OPEN',
        ]);

        $result = $this->rule->check($trade, [
            'max_factor' => 2.0, // Only allow up to 2x
        ]);

        $this->assertTrue($result, 'Should violate when volume is too high');
    }

    /**
     * Test that abnormally low volume is detected.
     */
    public function test_detects_low_volume(): void
    {
        $account = Account::factory()->create();

        // Create historical trades with avg volume of 10.0
        for ($i = 0; $i < 5; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'volume' => 10.0,
                'status' => 'CLOSED',
                'close_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        // Create trade with very low volume
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'volume' => 1.0, // 0.1x the average of 10.0
            'status' => 'OPEN',
        ]);

        $result = $this->rule->check($trade, [
            'min_factor' => 0.5, // Must be at least 0.5x average
        ]);

        $this->assertTrue($result, 'Should violate when volume is too low');
    }

    /**
     * Test that normal volume passes.
     */
    public function test_normal_volume_passes(): void
    {
        $account = Account::factory()->create();

        // Create historical trades with avg volume of 1.0
        for ($i = 0; $i < 5; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'volume' => 1.0,
                'status' => 'CLOSED',
                'close_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        // Create trade with normal volume
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'volume' => 1.5, // Within 0.5x to 2.0x range
            'status' => 'OPEN',
        ]);

        $result = $this->rule->check($trade, [
            'min_factor' => 0.5,
            'max_factor' => 2.0,
        ]);

        $this->assertFalse($result, 'Normal volume should pass');
    }

    /**
     * Test that first trade (no history) is skipped.
     */
    public function test_first_trade_is_skipped(): void
    {
        $account = Account::factory()->create();

        // Create trade with no historical data
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'volume' => 100.0, // Any volume
            'status' => 'OPEN',
        ]);

        $result = $this->rule->check($trade, [
            'max_factor' => 2.0,
        ]);

        $this->assertFalse($result, 'First trade should not violate (no history)');
    }

    /**
     * Test that lookback count is respected.
     */
    public function test_respects_lookback_count(): void
    {
        $account = Account::factory()->create();

        // Create 3 old trades with high volume
        for ($i = 0; $i < 3; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'volume' => 100.0,
                'status' => 'CLOSED',
                'close_time' => Carbon::now()->subDays(10 + $i),
            ]);
        }

        // Create 5 recent trades with avg volume of 1.0
        for ($i = 0; $i < 5; $i++) {
            Trade::factory()->create([
                'account_id' => $account->id,
                'volume' => 1.0,
                'status' => 'CLOSED',
                'close_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        // Test with volume that's 2x the recent average
        $trade = Trade::factory()->create([
            'account_id' => $account->id,
            'volume' => 2.0,
            'status' => 'OPEN',
        ]);

        $result = $this->rule->check($trade, [
            'max_factor' => 2.0,
            'lookback_trades' => 5, // Only look at 5 most recent
        ]);

        $this->assertFalse($result, 'Should use only recent trades in lookback');
    }

    /**
     * Test that only the same account's trades are considered.
     */
    public function test_only_considers_same_account(): void
    {
        $targetAccount = Account::factory()->create();
        $otherAccount = Account::factory()->create();

        // Create trades for other account with high volume
        for ($i = 0; $i < 5; $i++) {
            Trade::factory()->create([
                'account_id' => $otherAccount->id,
                'volume' => 100.0,
                'status' => 'CLOSED',
                'close_time' => Carbon::now()->subMinutes($i * 10),
            ]);
        }

        // Create trade for target account (first trade)
        $trade = Trade::factory()->create([
            'account_id' => $targetAccount->id,
            'volume' => 1.0,
            'status' => 'OPEN',
        ]);

        $result = $this->rule->check($trade, [
            'max_factor' => 2.0,
        ]);

        $this->assertFalse($result, 'Should only consider trades from same account');
    }

    /**
     * Test that the rule returns correct type identifier.
     */
    public function test_returns_correct_type(): void
    {
        $this->assertEquals('volume_consistency', VolumeConsistencyRule::getType());
    }

    /**
     * Test that the rule returns a description.
     */
    public function test_returns_description(): void
    {
        $this->assertNotEmpty(VolumeConsistencyRule::getDescription());
    }
}
