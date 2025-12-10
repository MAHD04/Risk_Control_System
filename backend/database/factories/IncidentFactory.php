<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'risk_rule_id' => RiskRule::factory(),
            'trade_id' => Trade::factory(),
            'details' => [
                'rule_type' => 'min_duration',
                'rule_parameters' => ['min_duration_seconds' => 10],
                'trade_volume' => fake()->randomFloat(2, 0.01, 10.00),
                'trade_duration_seconds' => fake()->numberBetween(1, 300),
            ],
            'triggered_at' => now(),
            'read_at' => null,
        ];
    }

    /**
     * Mark incident as read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Associate with a specific account.
     */
    public function forAccount(Account $account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $account->id,
        ]);
    }

    /**
     * Associate with a specific rule.
     */
    public function forRule(RiskRule $rule): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_rule_id' => $rule->id,
        ]);
    }

    /**
     * Associate with a specific trade.
     */
    public function forTrade(Trade $trade): static
    {
        return $this->state(fn (array $attributes) => [
            'trade_id' => $trade->id,
            'account_id' => $trade->account_id,
        ]);
    }
}
