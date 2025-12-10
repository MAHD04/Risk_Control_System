<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;

/**
 * Rule: Volume Consistency
 * 
 * Checks if the current trade's volume is consistent with the user's
 * historical average. Flags trades that are suspiciously larger or smaller.
 */
class VolumeConsistencyRule implements RuleStrategy
{
    /**
     * Check if the trade volume is outside the acceptable range.
     *
     * @param Trade $trade The trade to evaluate.
     * @param array $parameters Expected: [
     *     'min_factor' => float (e.g., 0.5),
     *     'max_factor' => float (e.g., 2.0),
     *     'lookback_trades' => int (e.g., 10)
     * ]
     * @return bool True if VIOLATED (volume inconsistent), false if passes.
     */
    public function check(Trade $trade, array $parameters): bool
    {
        $minFactor = $parameters['min_factor'] ?? 0.5;
        $maxFactor = $parameters['max_factor'] ?? 2.0;
        $lookbackCount = $parameters['lookback_trades'] ?? 10;

        // Get the last N closed trades for this account (excluding current trade)
        $historicalTrades = Trade::where('account_id', $trade->account_id)
            ->where('id', '!=', $trade->id)
            ->where('status', 'CLOSED')
            ->orderBy('close_time', 'desc')
            ->limit($lookbackCount)
            ->get();

        // If no history, we can't evaluate consistency - skip
        if ($historicalTrades->isEmpty()) {
            return false;
        }

        // Calculate average volume
        $averageVolume = $historicalTrades->avg('volume');

        if ($averageVolume <= 0) {
            return false;
        }

        // Calculate acceptable range
        $minAllowed = $averageVolume * $minFactor;
        $maxAllowed = $averageVolume * $maxFactor;

        $currentVolume = (float) $trade->volume;

        // Violated if outside the acceptable range
        return $currentVolume < $minAllowed || $currentVolume > $maxAllowed;
    }

    public static function getType(): string
    {
        return 'volume_consistency';
    }

    public static function getDescription(): string
    {
        return 'Detects trades with volume significantly different from historical average.';
    }
}
