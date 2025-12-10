<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trade>
 */
class TradeFactory extends Factory
{
    protected $model = Trade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openTime = fake()->dateTimeBetween('-1 month', 'now');
        
        return [
            'account_id' => Account::factory(),
            'type' => fake()->randomElement(['BUY', 'SELL']),
            'volume' => fake()->randomFloat(2, 0.01, 10.00),
            'open_time' => $openTime,
            'close_time' => null,
            'open_price' => fake()->randomFloat(5, 1.00000, 2.00000),
            'close_price' => null,
            'status' => 'OPEN',
        ];
    }

    /**
     * Set trade as closed with specific duration.
     */
    public function closed(?int $durationSeconds = null): static
    {
        return $this->state(function (array $attributes) use ($durationSeconds) {
            $openTime = $attributes['open_time'] ?? now()->subMinutes(30);
            $duration = $durationSeconds ?? fake()->numberBetween(60, 3600);
            $closeTime = (clone $openTime)->modify("+{$duration} seconds");
            
            return [
                'status' => 'CLOSED',
                'close_time' => $closeTime,
                'close_price' => fake()->randomFloat(5, 1.00000, 2.00000),
            ];
        });
    }

    /**
     * Set trade as closed with very short duration (for testing min_duration rule).
     */
    public function shortDuration(int $seconds = 5): static
    {
        return $this->state(function (array $attributes) use ($seconds) {
            $openTime = $attributes['open_time'] ?? now()->subMinutes(5);
            $closeTime = (clone $openTime)->modify("+{$seconds} seconds");
            
            return [
                'status' => 'CLOSED',
                'close_time' => $closeTime,
                'close_price' => fake()->randomFloat(5, 1.00000, 2.00000),
            ];
        });
    }

    /**
     * Set trade with specific volume.
     */
    public function withVolume(float $volume): static
    {
        return $this->state(fn (array $attributes) => [
            'volume' => $volume,
        ]);
    }

    /**
     * Set trade for a specific account.
     */
    public function forAccount(Account $account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $account->id,
        ]);
    }

    /**
     * Create a recent trade (within the last hour).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'open_time' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }
}
