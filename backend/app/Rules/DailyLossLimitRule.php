<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;
use App\Models\Account;
use Illuminate\Support\Facades\Log;

/**
 * Rule: Daily Loss Limit
 *
 * Checks if the account's realized loss for the current day exceeds a threshold.
 * This rule can be evaluated on a specific trade (reactive) or on the account state (proactive).
 */
class DailyLossLimitRule implements RuleStrategy
{
    /**
     * Check if the daily loss limit has been exceeded.
     *
     * @param Trade|Account $entity The entity to evaluate. Can be a Trade (event-based) or Account (periodic).
     * @param array $parameters Expected: ['max_daily_loss' => float]
     * @return bool True if VIOLATED (loss exceeds limit), false if passes.
     */
    public function check(mixed $entity, array $parameters): bool
    {
        $account = $entity instanceof Trade ? $entity->account : $entity;
        
        if (!$account) {
            return false;
        }

        $maxDailyLoss = $parameters['max_daily_loss'] ?? 1000.0;

        // Calculate realized PnL for today (closed trades)
        $dailyPnL = Trade::where('account_id', $account->id)
            ->where('status', 'CLOSED')
            ->whereDate('close_time', now()->today())
            ->sum('profit');

        // If PnL is positive or zero, we are safe (assuming limit is for LOSS)
        // If PnL is negative (loss), check if it exceeds the limit (absolute value)
        if ($dailyPnL >= 0) {
            return false;
        }

        // Check if loss (absolute value) exceeds max allowed
        return abs($dailyPnL) > $maxDailyLoss;
    }

    public static function getType(): string
    {
        return 'daily_loss_limit';
    }

    public static function getDescription(): string
    {
        return 'Detects when the daily realized loss exceeds a configured threshold.';
    }
}
