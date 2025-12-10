<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Carbon\Carbon;
use Tests\TestCase;

class TradeControllerTest extends TestCase
{
    /**
     * Test listing trades with pagination.
     */
    public function test_can_list_trades_with_pagination(): void
    {
        Trade::factory()->count(15)->create();

        $response = $this->getJson('/api/trades?per_page=10');

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
     * Test filtering trades by account.
     */
    public function test_can_filter_trades_by_account(): void
    {
        $account = Account::factory()->create();
        Trade::factory()->count(3)->create(['account_id' => $account->id]);
        Trade::factory()->count(5)->create(); // Other accounts

        $response = $this->getJson("/api/trades?account_id={$account->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test filtering trades by status.
     */
    public function test_can_filter_trades_by_status(): void
    {
        Trade::factory()->count(3)->create(['status' => 'OPEN']);
        Trade::factory()->count(2)->closed()->create();

        $response = $this->getJson('/api/trades?status=OPEN');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test creating a trade.
     */
    public function test_can_create_trade(): void
    {
        $account = Account::factory()->create();

        $tradeData = [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.5,
            'open_time' => Carbon::now()->subHour()->toDateTimeString(),
            'open_price' => 1.12345,
            'status' => 'OPEN',
        ];

        $response = $this->postJson('/api/trades', $tradeData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['type' => 'BUY', 'status' => 'OPEN'],
            ]);

        $this->assertDatabaseHas('trades', ['account_id' => $account->id]);
    }

    /**
     * Test validation on trade creation.
     */
    public function test_validates_required_fields_on_create(): void
    {
        $response = $this->postJson('/api/trades', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ])
            ->assertJsonPath('error.details.account_id', fn ($value) => !empty($value))
            ->assertJsonPath('error.details.type', fn ($value) => !empty($value))
            ->assertJsonPath('error.details.volume', fn ($value) => !empty($value));
    }

    /**
     * Test creating closed trade triggers risk evaluation.
     */
    public function test_creating_closed_trade_triggers_risk_evaluation(): void
    {
        $account = Account::factory()->create();
        // Create a rule that will be triggered by short trades
        RiskRule::factory()->hard()->minDuration(60)->create();

        $openTime = Carbon::now()->subSeconds(10);
        $closeTime = Carbon::now()->subSeconds(5); // 5 second trade

        $tradeData = [
            'account_id' => $account->id,
            'type' => 'BUY',
            'volume' => 1.0,
            'open_time' => $openTime->toDateTimeString(),
            'close_time' => $closeTime->toDateTimeString(),
            'open_price' => 1.0,
            'close_price' => 1.01,
            'status' => 'CLOSED',
        ];

        $response = $this->postJson('/api/trades', $tradeData);

        $response->assertStatus(201);

        // Check that an incident was created
        $this->assertDatabaseHas('incidents', ['account_id' => $account->id]);
    }

    /**
     * Test showing a single trade.
     */
    public function test_can_show_single_trade(): void
    {
        $trade = Trade::factory()->create();

        $response = $this->getJson("/api/trades/{$trade->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $trade->id],
            ]);
    }

    /**
     * Test updating a trade.
     */
    public function test_can_update_trade(): void
    {
        $trade = Trade::factory()->create(['status' => 'OPEN']);

        $response = $this->putJson("/api/trades/{$trade->id}", [
            'status' => 'CLOSED',
            'close_time' => Carbon::now()->toDateTimeString(),
            'close_price' => 1.5,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $trade->refresh();
        $this->assertEquals('CLOSED', $trade->status);
    }
}
