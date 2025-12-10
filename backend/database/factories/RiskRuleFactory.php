<?php

namespace Database\Factories;

use App\Models\RiskRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RiskRule>
 */
class RiskRuleFactory extends Factory
{
    protected $model = RiskRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'rule_type' => fake()->randomElement(['min_duration', 'volume_consistency', 'trade_frequency']),
            'parameters' => [],
            'severity' => fake()->randomElement(['HARD', 'SOFT']),
            'incident_limit' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    /**
     * Set rule as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set rule as HARD severity.
     */
    public function hard(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'HARD',
        ]);
    }

    /**
     * Set rule as SOFT severity.
     */
    public function soft(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'SOFT',
        ]);
    }

    /**
     * Create a min_duration rule with specific parameters.
     */
    public function minDuration(int $minDurationSeconds = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'min_duration',
            'parameters' => ['min_duration_seconds' => $minDurationSeconds],
        ]);
    }

    /**
     * Create a volume_consistency rule with specific parameters.
     */
    public function volumeConsistency(
        float $minFactor = 0.5,
        float $maxFactor = 2.0,
        int $lookbackTrades = 10
    ): static {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'volume_consistency',
            'parameters' => [
                'min_factor' => $minFactor,
                'max_factor' => $maxFactor,
                'lookback_trades' => $lookbackTrades,
            ],
        ]);
    }

    /**
     * Create a trade_frequency rule with specific parameters.
     */
    public function tradeFrequency(
        int $timeWindowMinutes = 60,
        ?int $minOpenTrades = null,
        ?int $maxOpenTrades = 10
    ): static {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'trade_frequency',
            'parameters' => [
                'time_window_minutes' => $timeWindowMinutes,
                'min_open_trades' => $minOpenTrades,
                'max_open_trades' => $maxOpenTrades,
            ],
        ]);
    }
}
