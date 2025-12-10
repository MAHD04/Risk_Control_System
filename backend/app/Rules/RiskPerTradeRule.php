<?php

namespace App\Rules;

use App\Contracts\RuleStrategy;
use App\Models\Trade;

/**
 * Rule: Risk Per Trade
 *
 * Checks if the potential loss of a trade (difference between Open Price and Stop Loss)
 * exceeds a configurable percentage of the account balance or a fixed amount.
 */
class RiskPerTradeRule implements RuleStrategy
{
    /**
     * Check if the risk per trade is too high.
     *
     * @param Trade|Account $entity The entity to evaluate.
     * @param array $parameters Expected: ['max_risk_amount' => float]
     * @return bool True if VIOLATED.
     */
    public function check(mixed $entity, array $parameters): bool
    {
        // This rule only applies to specific Trades, not generic Account checks
        if (!($entity instanceof Trade)) {
            return false;
        }

        $trade = $entity;

        // If no stop loss, we can't calculate risk (or we could consider it infinite risk!)
        // For this rule, we'll assume if SL is missing, it MIGHT be a violation if we enforced SL, 
        // but let's stick to calculating risk if SL exists.
        if ($trade->stop_loss === null) {
            return false; 
        }

        $valueType = $parameters['value_type'] ?? 'FIXED'; // FIXED or PERCENT
        $value = $parameters['max_risk_amount'] ?? 100.0;

        // Calculate the actual risk limit amount
        $limitAmount = $value;
        if ($valueType === 'PERCENT') {
            // If PERCENT, calculate based on current balance
            // Example: 1% of 100,000 = 1,000
            $account = $trade->account;
            if ($account) {
                $limitAmount = ($account->balance * $value) / 100;
            }
        }

        // Calculate risk amount
        // Risk = abs(Open Price - Stop Loss) * Volume * ContractSize (assuming 1 for now or handled in volume)
        // Simplified: Risk = Price Diff * Volume
        
        $priceDiff = abs($trade->open_price - $trade->stop_loss);
        $riskAmount = $priceDiff * $trade->volume; // Adjust multiplier if needed for specific assets (Forex lots etc)

        return $riskAmount > $limitAmount;
    }

    public static function getType(): string
    {
        return 'risk_per_trade';
    }

    public static function getDescription(): string
    {
        return 'Detects if a single trade risks more than a allowed amount based on Stop Loss.';
    }
}
