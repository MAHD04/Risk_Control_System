<?php

namespace Database\Seeders;

use App\Models\ConfiguredAction;
use Illuminate\Database\Seeder;

class ConfiguredActionSeeder extends Seeder
{
    /**
     * Seed the default configured actions.
     */
    public function run(): void
    {
        $actions = [
            [
                'name' => 'Email Notification',
                'action_type' => 'NOTIFY_EMAIL',
                'config' => [
                    'recipient' => 'admin@mmtech.com',
                    'subject' => 'Risk Alert: Rule Violation Detected',
                ],
            ],
            [
                'name' => 'Slack Notification',
                'action_type' => 'NOTIFY_SLACK',
                'config' => [
                    'channel' => '#risk-alerts',
                    'webhook_url' => 'https://hooks.slack.com/services/mock',
                ],
            ],
            [
                'name' => 'Disable Account',
                'action_type' => 'DISABLE_ACCOUNT',
                'config' => [],
            ],
            [
                'name' => 'Disable Trading',
                'action_type' => 'DISABLE_TRADING',
                'config' => [],
            ],
        ];

        foreach ($actions as $action) {
            ConfiguredAction::firstOrCreate(
                ['action_type' => $action['action_type']],
                $action
            );
        }
    }
}

