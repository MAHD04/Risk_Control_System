<?php

namespace Database\Seeders;

use App\Models\ConfiguredAction;
use App\Models\RiskRule;
use Illuminate\Database\Seeder;

class RiskRuleSeeder extends Seeder
{
    /**
     * Seed default risk rules.
     */
    public function run(): void
    {
        // Rule 1: Minimum Duration (HARD)
        $minDurationRule = RiskRule::firstOrCreate(
            ['rule_type' => 'min_duration'],
            [
                'name' => 'Fast Trade Detection',
                'rule_type' => 'min_duration',
                'parameters' => [
                    'min_duration_seconds' => 10,
                ],
                'severity' => 'HARD',
                'incident_limit' => 1,
                'is_active' => true,
            ]
        );

        // Rule 2: Volume Consistency (SOFT)
        $volumeRule = RiskRule::firstOrCreate(
            ['rule_type' => 'volume_consistency'],
            [
                'name' => 'Unusual Volume Detection',
                'rule_type' => 'volume_consistency',
                'parameters' => [
                    'min_factor' => 0.5,
                    'max_factor' => 2.0,
                    'lookback_trades' => 10,
                ],
                'severity' => 'SOFT',
                'incident_limit' => 3,
                'is_active' => true,
            ]
        );

        // Rule 3: Trade Frequency (SOFT)
        $frequencyRule = RiskRule::firstOrCreate(
            ['rule_type' => 'trade_frequency'],
            [
                'name' => 'High Frequency Trading Detection',
                'rule_type' => 'trade_frequency',
                'parameters' => [
                    'time_window_minutes' => 5,
                    'max_open_trades' => 10,
                ],
                'severity' => 'SOFT',
                'incident_limit' => 2,
                'is_active' => false, // Disabled by default
            ]
        );

        // Attach actions to rules
        $emailAction = ConfiguredAction::where('action_type', 'NOTIFY_EMAIL')->first();
        $slackAction = ConfiguredAction::where('action_type', 'NOTIFY_SLACK')->first();
        $disableAccountAction = ConfiguredAction::where('action_type', 'DISABLE_ACCOUNT')->first();
        $disableTradingAction = ConfiguredAction::where('action_type', 'DISABLE_TRADING')->first();

        if ($emailAction && $slackAction && $disableAccountAction && $disableTradingAction) {
            // HARD rule: Disable account + notifications
            $minDurationRule->actions()->syncWithoutDetaching([
                $emailAction->id,
                $slackAction->id,
                $disableAccountAction->id,
            ]);

            // SOFT rules: Just notifications + disable trading
            $volumeRule->actions()->syncWithoutDetaching([
                $emailAction->id,
                $disableTradingAction->id,
            ]);

            $frequencyRule->actions()->syncWithoutDetaching([
                $slackAction->id,
            ]);
        }
    }
}
