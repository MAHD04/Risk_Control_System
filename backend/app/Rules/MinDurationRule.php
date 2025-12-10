<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;

/**
 * Rule: Minimum Trade Duration
 * 
 * Checks if a closed trade lasted less than a configurable minimum duration.
 * If the trade duration is below the threshold, the rule is violated.
 */
class MinDurationRule implements RuleStrategy
{
    /**
     * Check if the trade duration is less than the minimum allowed.
     *
     * @param Trade $trade The trade to evaluate.
     * @param array $parameters Expected: ['min_duration_seconds' => int]
     * @return bool True if VIOLATED (duration too short), false if passes.
     */
    public function check(Trade $trade, array $parameters): bool
    {
        // Only evaluate closed trades
        if (!$trade->isClosed() || !$trade->close_time) {
            return false;
        }

        $minDuration = $parameters['min_duration_seconds'] ?? 10;
        $actualDuration = $trade->getDurationInSeconds();

        // Violated if actual duration is less than minimum
        return $actualDuration < $minDuration;
    }

    public static function getType(): string
    {
        return 'min_duration';
    }

    public static function getDescription(): string
    {
        return 'Detects trades that close faster than a minimum allowed duration.';
    }
}
