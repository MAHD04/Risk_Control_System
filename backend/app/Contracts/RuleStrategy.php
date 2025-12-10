<?php

namespace App\Contracts;

use App\Models\Trade;

/**
 * Interface for all risk rule strategies.
 * Each rule type must implement this contract.
 */
interface RuleStrategy
{
    /**
     * Check if the given trade violates this rule.
     *
     * @param Trade $trade The trade to evaluate.
     * @param array $parameters The configurable parameters for this rule.
     * @return bool True if the rule is VIOLATED, false if it passes.
     */
    public function check(Trade $trade, array $parameters): bool;

    /**
     * Get the unique identifier for this rule type.
     * This should match the 'rule_type' column in the risk_rules table.
     */
    public static function getType(): string;

    /**
     * Get a human-readable description of what this rule does.
     */
    public static function getDescription(): string;
}
