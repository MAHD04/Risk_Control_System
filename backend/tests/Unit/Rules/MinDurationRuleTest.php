<?php

namespace Tests\Unit\Rules;

use App\Models\Account;
use App\Models\Trade;
use App\Rules\MinDurationRule;
use Carbon\Carbon;
use Tests\TestCase;

class MinDurationRuleTest extends TestCase
{
    private MinDurationRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new MinDurationRule();
    }

    /**
     * Test that open trades are ignored by the rule.
     */
    public function test_open_trades_are_ignored(): void
    {
        $trade = Trade::factory()->create([
            'status' => 'OPEN',
            'close_time' => null,
        ]);

        $result = $this->rule->check($trade, ['min_duration_seconds' => 10]);

        $this->assertFalse($result);
    }

    /**
     * Test that short trades violate the rule.
     */
    public function test_short_trade_violates_rule(): void
    {
        $openTime = Carbon::now()->subSeconds(30);
        $closeTime = Carbon::now()->subSeconds(25); // 5 seconds duration

        $trade = Trade::factory()->create([
            'status' => 'CLOSED',
            'open_time' => $openTime,
            'close_time' => $closeTime,
        ]);

        $result = $this->rule->check($trade, ['min_duration_seconds' => 10]);

        $this->assertTrue($result, 'Trade with 5 second duration should violate min_duration_seconds of 10');
    }

    /**
     * Test that trades meeting the minimum duration pass the rule.
     */
    public function test_long_trade_passes_rule(): void
    {
        // Create timestamps with explicit 5-minute gap where close > open
        $openTime = Carbon::create(2024, 1, 1, 12, 0, 0);
        $closeTime = Carbon::create(2024, 1, 1, 12, 5, 0); // 5 minutes = 300 seconds

        $trade = Trade::factory()->create([
            'status' => 'CLOSED',
            'open_time' => $openTime,
            'close_time' => $closeTime,
        ]);

        $result = $this->rule->check($trade, ['min_duration_seconds' => 10]);

        $this->assertFalse($result, 'Trade with 300 second duration should pass min_duration_seconds of 10');
    }

    /**
     * Test that the rule uses default min_duration when not specified.
     */
    public function test_uses_default_min_duration_when_not_specified(): void
    {
        $openTime = Carbon::now()->subSeconds(5);
        $closeTime = Carbon::now(); // 5 seconds duration

        $trade = Trade::factory()->create([
            'status' => 'CLOSED',
            'open_time' => $openTime,
            'close_time' => $closeTime,
        ]);

        // Default is 10 seconds, so 5 second trade should violate
        $result = $this->rule->check($trade, []);

        $this->assertTrue($result, 'Trade with 5 second duration should violate default min_duration of 10');
    }

    /**
     * Test that trades without close_time are ignored.
     */
    public function test_trade_without_close_time_is_ignored(): void
    {
        $trade = Trade::factory()->create([
            'status' => 'CLOSED',
            'close_time' => null,
        ]);

        $result = $this->rule->check($trade, ['min_duration_seconds' => 10]);

        $this->assertFalse($result);
    }

    /**
     * Test that the rule returns correct type identifier.
     */
    public function test_returns_correct_type(): void
    {
        $this->assertEquals('min_duration', MinDurationRule::getType());
    }

    /**
     * Test that the rule returns a description.
     */
    public function test_returns_description(): void
    {
        $this->assertNotEmpty(MinDurationRule::getDescription());
    }
}
