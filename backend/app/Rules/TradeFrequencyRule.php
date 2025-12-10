<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;

/**
 * Rule: Trade Frequency (Open Trades in Time Window)
 * 
 * Checks if the number of trades opened by a user within a time window
 * exceeds or falls below configurable thresholds.
 */
class TradeFrequencyRule implements RuleStrategy
{
    /**
     * Check if the number of open trades in the time window violates limits.
     *
     * @param Trade $trade The trade to evaluate.
     * @param array $parameters Expected: [
     *     'time_window_minutes' => int (e.g., 60),
     *     'min_open_trades' => int|null (optional),
     *     'max_open_trades' => int|null (optional)
     * ]
     * @return bool True if VIOLATED, false if passes.
     */
    public function check(Trade $trade, array $parameters): bool
    {
        $timeWindowMinutes = $parameters['time_window_minutes'] ?? 60;
        $minOpenTrades = $parameters['min_open_trades'] ?? null;
        $maxOpenTrades = $parameters['max_open_trades'] ?? null;

        // Calculate the time window start
        $windowStart = now()->subMinutes($timeWindowMinutes);

        // Count trades opened in the time window for this account
        $tradeCount = Trade::where('account_id', $trade->account_id)
            ->where('open_time', '>=', $windowStart)
            ->count();

        // Check min threshold (if configured)
        if ($minOpenTrades !== null && $tradeCount < $minOpenTrades) {
            return true; // Violated: too few trades
        }

        // Check max threshold (if configured)
        if ($maxOpenTrades !== null && $tradeCount > $maxOpenTrades) {
            return true; // Violated: too many trades
        }

        return false;
    }

    public static function getType(): string
    {
        return 'trade_frequency';
    }

    public static function getDescription(): string
    {
        return 'Detects when a user opens too many or too few trades within a time window.';
    }
}
