<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;
use App\Models\Account;

/**
 * Rule: Max Drawdown
 *
 * Checks if the account's equity has dropped below a certain percentage of its starting balance (or high water mark).
 * For simplicity in this iteration, we calculate drawdown based on realized PnL from a fixed starting balance 
 * (assuming starting balance is 0 + deposits, or we just track total loss).
 * 
 * A better approach for a real system is to track 'high_water_mark' on the Account model.
 * Here we will assume 'max_drawdown_amount' is a fixed dollar amount from the initial balance.
 */
class MaxDrawdownRule implements RuleStrategy
{
    /**
     * Check if the max drawdown has been reached.
     *
     * @param Trade|Account $entity The entity to evaluate.
     * @param array $parameters Expected: ['max_drawdown_amount' => float]
     * @return bool True if VIOLATED.
     */
    public function check(mixed $entity, array $parameters): bool
    {
        $account = $entity instanceof Trade ? $entity->account : $entity;

        if (!$account) {
            return false;
        }

        $valueType = $parameters['value_type'] ?? 'FIXED'; // FIXED or PERCENT
        $value = $parameters['max_drawdown_amount'] ?? 2000.0;

        // Calculate the actual drawdown limit amount
        $limitAmount = $value;
        if ($valueType === 'PERCENT') {
            // If PERCENT, calculate based on initial_balance
            // Example: 10% of 100,000 = 10,000
            $limitAmount = ($account->initial_balance * $value) / 100;
        }

        // Calculate total realized PnL
        $totalPnL = Trade::where('account_id', $account->id)
            ->where('status', 'CLOSED')
            ->sum('profit');

        // If total PnL is negative and exceeds the drawdown limit
        if ($totalPnL < 0 && abs($totalPnL) > $limitAmount) {
            return true;
        }

        return false;
    }

    public static function getType(): string
    {
        return 'max_drawdown';
    }

    public static function getDescription(): string
    {
        return 'Detects when the total realized loss exceeds a specific amount (Drawdown).';
    }
}
