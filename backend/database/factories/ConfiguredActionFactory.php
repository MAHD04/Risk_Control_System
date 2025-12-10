<?php

namespace Database\Factories;

use App\Models\ConfiguredAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConfiguredAction>
 */
class ConfiguredActionFactory extends Factory
{
    protected $model = ConfiguredAction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(2),
            'action_type' => fake()->randomElement(['NOTIFY_EMAIL', 'NOTIFY_SLACK', 'DISABLE_ACCOUNT', 'DISABLE_TRADING', 'ALERT', 'CLOSE_TRADE']),
            'config' => [],
        ];
    }

    /**
     * Create a NOTIFY_EMAIL action.
     */
    public function email(string $recipient = 'admin@example.com', string $subject = 'Risk Alert'): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Email Notification',
            'action_type' => 'NOTIFY_EMAIL',
            'config' => [
                'recipient' => $recipient,
                'subject' => $subject,
            ],
        ]);
    }

    /**
     * Create a NOTIFY_SLACK action.
     */
    public function slack(string $channel = '#risk-alerts'): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Slack Notification',
            'action_type' => 'NOTIFY_SLACK',
            'config' => [
                'channel' => $channel,
                'webhook_url' => 'https://hooks.slack.com/mock',
            ],
        ]);
    }

    /**
     * Create a DISABLE_ACCOUNT action.
     */
    public function disableAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Disable Account',
            'action_type' => 'DISABLE_ACCOUNT',
            'config' => [],
        ]);
    }

    /**
     * Create a DISABLE_TRADING action.
     */
    public function disableTrading(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Disable Trading',
            'action_type' => 'DISABLE_TRADING',
            'config' => [],
        ]);
    }

    /**
     * Create an ALERT action.
     */
    public function alert(string $alertType = 'WARNING', string $message = 'Risk rule violation detected'): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'System Alert',
            'action_type' => 'ALERT',
            'config' => [
                'alert_type' => $alertType,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Create a CLOSE_TRADE action.
     */
    public function closeTrade(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Close Trade',
            'action_type' => 'CLOSE_TRADE',
            'config' => [],
        ]);
    }
}
