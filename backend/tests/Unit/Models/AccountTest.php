<?php

namespace Tests\Unit\Models;

use App\Models\Account;
use App\Models\Incident;
use App\Models\Trade;
use Tests\TestCase;

class AccountTest extends TestCase
{
    /**
     * Test that disableAccount updates status.
     */
    public function test_disables_account_status(): void
    {
        $account = Account::factory()->create(['status' => 'enable']);

        $account->disableAccount();

        $account->refresh();
        $this->assertEquals('disable', $account->status);
    }

    /**
     * Test that disableTrading updates trading_status.
     */
    public function test_disables_trading(): void
    {
        $account = Account::factory()->create(['trading_status' => 'enable']);

        $account->disableTrading();

        $account->refresh();
        $this->assertEquals('disable', $account->trading_status);
    }

    /**
     * Test that isEnabled returns correct status.
     */
    public function test_is_enabled_returns_correct_status(): void
    {
        $enabledAccount = Account::factory()->create(['status' => 'enable']);
        $disabledAccount = Account::factory()->disabled()->create();

        $this->assertTrue($enabledAccount->isEnabled());
        $this->assertFalse($disabledAccount->isEnabled());
    }

    /**
     * Test that isTradingEnabled returns correct status.
     */
    public function test_is_trading_enabled_returns_correct_status(): void
    {
        $enabledAccount = Account::factory()->create(['trading_status' => 'enable']);
        $disabledAccount = Account::factory()->tradingDisabled()->create();

        $this->assertTrue($enabledAccount->isTradingEnabled());
        $this->assertFalse($disabledAccount->isTradingEnabled());
    }

    /**
     * Test that account has many trades.
     */
    public function test_has_many_trades(): void
    {
        $account = Account::factory()->create();
        Trade::factory()->forAccount($account)->count(3)->create();

        $this->assertCount(3, $account->trades);
        $this->assertInstanceOf(Trade::class, $account->trades->first());
    }

    /**
     * Test that account has many incidents.
     */
    public function test_has_many_incidents(): void
    {
        $account = Account::factory()->create();
        Incident::factory()->forAccount($account)->count(2)->create();

        $this->assertCount(2, $account->incidents);
        $this->assertInstanceOf(Incident::class, $account->incidents->first());
    }

    /**
     * Test that login is cast to integer.
     */
    public function test_login_cast_to_integer(): void
    {
        $account = Account::factory()->create(['login' => '123456']);

        $this->assertEquals(123456, $account->login);
        $this->assertIsInt($account->login);
    }

    /**
     * Test that disabling trading does not affect account status.
     */
    public function test_disable_trading_does_not_affect_account_status(): void
    {
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);

        $account->disableTrading();

        $account->refresh();
        $this->assertEquals('enable', $account->status);
        $this->assertEquals('disable', $account->trading_status);
    }

    /**
     * Test that disabling account does not affect trading status.
     */
    public function test_disable_account_does_not_affect_trading_status(): void
    {
        $account = Account::factory()->create([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);

        $account->disableAccount();

        $account->refresh();
        $this->assertEquals('disable', $account->status);
        $this->assertEquals('enable', $account->trading_status);
    }
}
