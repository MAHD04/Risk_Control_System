<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Incident;
use App\Models\Trade;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    /**
     * Test listing accounts.
     */
    public function test_can_list_accounts(): void
    {
        Account::factory()->count(5)->create();

        $response = $this->getJson('/api/accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test creating an account.
     */
    public function test_can_create_account(): void
    {
        $response = $this->postJson('/api/accounts', [
            'login' => 123456,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'login' => 123456,
                    'status' => 'enable',
                    'trading_status' => 'enable',
                ],
            ]);

        $this->assertDatabaseHas('accounts', ['login' => 123456]);
    }

    /**
     * Test validation on account creation.
     */
    public function test_validates_unique_login(): void
    {
        Account::factory()->create(['login' => 123456]);

        $response = $this->postJson('/api/accounts', [
            'login' => 123456,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ])
            ->assertJsonPath('error.details.login', fn ($value) => !empty($value));
    }

    /**
     * Test showing a single account.
     */
    public function test_can_show_account(): void
    {
        $account = Account::factory()->create();
        Trade::factory()->count(3)->forAccount($account)->create();
        Incident::factory()->count(2)->forAccount($account)->create();

        $response = $this->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'account' => ['id' => $account->id],
                    'risk_status' => [
                        'is_enabled' => true,
                        'is_trading_enabled' => true,
                    ],
                ],
            ]);
    }

    /**
     * Test restoring a disabled account.
     */
    public function test_can_restore_disabled_account(): void
    {
        $account = Account::factory()->create([
            'status' => 'disable',
            'trading_status' => 'disable',
        ]);

        $response = $this->postJson("/api/accounts/{$account->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'enable',
                    'trading_status' => 'enable',
                ],
            ]);

        $account->refresh();
        $this->assertTrue($account->isEnabled());
        $this->assertTrue($account->isTradingEnabled());
    }
}
