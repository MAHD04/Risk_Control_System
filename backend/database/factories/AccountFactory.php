<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'login' => fake()->unique()->randomNumber(6),
            'status' => 'enable',
            'trading_status' => 'enable',
        ];
    }

    /**
     * Set account as disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disable',
        ]);
    }

    /**
     * Set trading as disabled.
     */
    public function tradingDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'trading_status' => 'disable',
        ]);
    }
}
