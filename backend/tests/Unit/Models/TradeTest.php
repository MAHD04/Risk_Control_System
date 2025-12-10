<?php

namespace Tests\Unit\Models;

use App\Models\Account;
use App\Models\Trade;
use Carbon\Carbon;
use Tests\TestCase;

class TradeTest extends TestCase
{
    /**
     * Test that trade duration is calculated correctly.
     */
    public function test_calculates_duration_in_seconds(): void
    {
        $openTime = Carbon::now()->subMinutes(5);
        $closeTime = Carbon::now();

        $trade = Trade::factory()->create([
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'status' => 'CLOSED',
        ]);

        // 5 minutes = 300 seconds
        $this->assertEquals(300, $trade->getDurationInSeconds());
    }

    /**
     * Test that open trades return null for duration.
     */
    public function test_open_trade_returns_null_duration(): void
    {
        $trade = Trade::factory()->create([
            'status' => 'OPEN',
            'close_time' => null,
        ]);

        $this->assertNull($trade->getDurationInSeconds());
    }

    /**
     * Test that isClosed returns true for closed trades.
     */
    public function test_identifies_closed_trades(): void
    {
        $closedTrade = Trade::factory()->closed()->create();
        $openTrade = Trade::factory()->create(['status' => 'OPEN']);

        $this->assertTrue($closedTrade->isClosed());
        $this->assertFalse($openTrade->isClosed());
    }

    /**
     * Test that isOpen returns true for open trades.
     */
    public function test_identifies_open_trades(): void
    {
        $openTrade = Trade::factory()->create(['status' => 'OPEN']);
        $closedTrade = Trade::factory()->closed()->create();

        $this->assertTrue($openTrade->isOpen());
        $this->assertFalse($closedTrade->isOpen());
    }

    /**
     * Test that trade belongs to an account.
     */
    public function test_belongs_to_account(): void
    {
        $account = Account::factory()->create();
        $trade = Trade::factory()->forAccount($account)->create();

        $this->assertInstanceOf(Account::class, $trade->account);
        $this->assertEquals($account->id, $trade->account->id);
    }

    /**
     * Test that trade has many incidents.
     */
    public function test_has_many_incidents(): void
    {
        $trade = Trade::factory()->create();

        $this->assertEmpty($trade->incidents);
        $this->assertIsIterable($trade->incidents);
    }

    /**
     * Test that volume is cast to decimal.
     */
    public function test_volume_cast_to_decimal(): void
    {
        $trade = Trade::factory()->withVolume(1.55)->create();

        $this->assertEquals(1.55, (float) $trade->volume);
    }

    /**
     * Test that open_time is cast to datetime.
     */
    public function test_open_time_cast_to_datetime(): void
    {
        $trade = Trade::factory()->create();

        $this->assertInstanceOf(Carbon::class, $trade->open_time);
    }

    /**
     * Test that close_time is cast to datetime when present.
     */
    public function test_close_time_cast_to_datetime(): void
    {
        $trade = Trade::factory()->closed()->create();

        $this->assertInstanceOf(Carbon::class, $trade->close_time);
    }
}
