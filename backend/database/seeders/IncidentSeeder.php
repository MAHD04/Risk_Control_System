<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    /**
     * Seed test incidents.
     */
    public function run(): void
    {
        $accounts = Account::all();
        $rules = RiskRule::all();
        $trades = Trade::all();

        if ($accounts->isEmpty() || $rules->isEmpty()) {
            $this->command->warn('No accounts or rules found. Skipping IncidentSeeder.');
            return;
        }

        $now = Carbon::now();

        // Create 15-20 incidents spread over the last 3 days
        $numIncidents = rand(15, 20);

        for ($i = 0; $i < $numIncidents; $i++) {
            $account = $accounts->random();
            $rule = $rules->random();
            $trade = $trades->isNotEmpty() ? $trades->random() : null;

            // Spread incidents: 60% today, 40% over last 72 hours
            $isToday = rand(0, 10) < 6;
            if ($isToday) {
                $triggeredAt = $now->copy()
                    ->subHours(rand(0, $now->hour))
                    ->subMinutes(rand(0, 59));
            } else {
                $triggeredAt = $now->copy()
                    ->subHours(rand(24, 72))
                    ->subMinutes(rand(0, 59));
            }

            // 40% of incidents are read
            $isRead = rand(0, 10) < 4;
            $readAt = $isRead 
                ? $triggeredAt->copy()->addMinutes(rand(5, 120)) 
                : null;

            // Generate realistic details based on rule type
            $details = $this->generateDetails($rule, $trade);

            Incident::create([
                'account_id' => $account->id,
                'risk_rule_id' => $rule->id,
                'trade_id' => $trade?->id,
                'details' => $details,
                'triggered_at' => $triggeredAt,
                'read_at' => $readAt,
            ]);
        }

        $this->command->info('Created ' . Incident::count() . ' test incidents.');
    }

    private function generateDetails(RiskRule $rule, ?Trade $trade): array
    {
        $details = [
            'rule_name' => $rule->name,
            'severity' => $rule->severity,
        ];

        switch ($rule->rule_type) {
            case 'min_duration':
                $details['duration_seconds'] = rand(1, 9);
                $details['min_required'] = 10;
                $details['message'] = 'Trade closed too quickly';
                break;

            case 'volume_consistency':
                $details['current_volume'] = rand(50, 200) / 10;
                $details['average_volume'] = 1.5;
                $details['deviation_factor'] = rand(25, 40) / 10;
                $details['message'] = 'Unusual trading volume detected';
                break;

            case 'trade_frequency':
                $details['trades_in_window'] = rand(11, 20);
                $details['max_allowed'] = 10;
                $details['time_window_minutes'] = 5;
                $details['message'] = 'Excessive trading frequency detected';
                break;

            default:
                $details['message'] = 'Rule violation detected';
        }

        if ($trade) {
            $details['trade_id'] = $trade->id;
            $details['trade_type'] = $trade->type;
            $details['trade_volume'] = $trade->volume;
        }

        return $details;
    }
}
