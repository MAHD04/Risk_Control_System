<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;
use App\Models\Account;

/**
 * Rule: Max Open Positions
 *
 * Checks if the number of concurrent open trades exceeds a limit.
 */
class MaxOpenPositionsRule implements RuleStrategy
{
    /**
     * Check if the max open positions limit has been exceeded.
     *
     * @param Trade|Account $entity The entity to evaluate.
     * @param array $parameters Expected: ['max_positions' => int]
     * @return bool True if VIOLATED (too many positions), false if passes.
     */
    public function check(mixed $entity, array $parameters): bool
    {
        $account = $entity instanceof Trade ? $entity->account : $entity;

        if (!$account) {
            return false;
        }

        $maxPositions = $parameters['max_positions'] ?? 5;

        // Count currently open trades
        $openPositionsCount = Trade::where('account_id', $account->id)
            ->where('status', 'OPEN')
            ->count();

        // Violated if count exceeds max
        // Note: If triggered by a new trade creation, that trade might already be counted depending on when this runs.
        // Usually we want to prevent OPENING a new one if we are AT the limit.
        // But as a detective control, we flag if we are ABOVE the limit.
        
        return $openPositionsCount > $maxPositions;
    }

    public static function getType(): string
    {
        return 'max_open_positions';
    }

    public static function getDescription(): string
    {
        return 'Detects when the number of open positions exceeds a configured limit.';
    }
}
