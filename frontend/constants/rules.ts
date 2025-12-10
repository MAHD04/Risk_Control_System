// =============================================================================
// Risk Control System - Rule Constants
// =============================================================================

import type { RuleType, Severity } from '@/types';

// Default parameters for each rule type
export const RULE_DEFAULT_PARAMETERS = {
    min_duration: {
        min_duration_seconds: 60,
    },
    volume_consistency: {
        min_factor: 0.5,
        max_factor: 2.0,
        lookback_trades: 10,
    },
    trade_frequency: {
        time_window_minutes: 60,
        min_open_trades: 0,
        max_open_trades: 10,
    },
    daily_loss_limit: {
        max_daily_loss: 1000,
    },
    max_open_positions: {
        max_positions: 5,
    },
    max_drawdown: {
        max_drawdown_amount: 10, // 10%
        value_type: 'PERCENT',
    },
    risk_per_trade: {
        max_risk_amount: 1, // 1%
        value_type: 'PERCENT',
    },
} as const;

// Rule type definitions with labels and descriptions
export const RULE_TYPES: { value: RuleType; label: string; description: string; category: 'BASIC' | 'OTHER' }[] = [
    {
        value: 'min_duration',
        label: 'Minimum Duration',
        description: 'Triggers when a trade closes in less than X seconds',
        category: 'BASIC',
    },
    {
        value: 'volume_consistency',
        label: 'Volume Consistency',
        description: 'Triggers when trade volume deviates from user average',
        category: 'BASIC',
    },
    {
        value: 'trade_frequency',
        label: 'Trade Frequency',
        description: 'Triggers based on number of trades in a time window',
        category: 'BASIC',
    },
    {
        value: 'daily_loss_limit',
        label: 'Daily Loss Limit',
        description: 'Triggers when daily realized loss exceeds a threshold',
        category: 'OTHER',
    },
    {
        value: 'max_open_positions',
        label: 'Max Open Positions',
        description: 'Triggers when open positions exceed a limit',
        category: 'OTHER',
    },
    {
        value: 'max_drawdown',
        label: 'Max Drawdown',
        description: 'Triggers when total loss exceeds a specific amount',
        category: 'OTHER',
    },
    {
        value: 'risk_per_trade',
        label: 'Risk Per Trade',
        description: 'Triggers when a single trade risks too much money',
        category: 'OTHER',
    },
];

// Severity definitions with labels and descriptions
export const SEVERITIES: { value: Severity; label: string; description: string }[] = [
    {
        value: 'SOFT',
        label: 'Soft Rule',
        description: 'Accumulates incidents before triggering action',
    },
    {
        value: 'HARD',
        label: 'Hard Rule',
        description: 'Executes action immediately on violation',
    },
];

// Default incident limit for SOFT rules
export const DEFAULT_INCIDENT_LIMIT = 3;

// Rule type labels for display
export const RULE_TYPE_LABELS: Record<string, string> = {
    min_duration: 'Duration',
    volume_consistency: 'Volume',
    trade_frequency: 'Frequency',
    daily_loss_limit: 'Daily Loss',
    max_open_positions: 'Max Positions',
    max_drawdown: 'Drawdown',
    risk_per_trade: 'Risk/Trade',
};

// Rule type badge styles
export const RULE_TYPE_BADGE_STYLES: Record<string, string> = {
    min_duration: 'badge-info',
    volume_consistency: 'badge-warning',
    trade_frequency: 'badge-success',
    daily_loss_limit: 'badge-error',
    max_open_positions: 'badge-error',
    max_drawdown: 'badge-error',
    risk_per_trade: 'badge-warning',
};

// =============================================================================
// Helper Functions
// =============================================================================

/**
 * Get the badge CSS class for a rule type
 */
export const getRuleTypeBadge = (type: string): string => {
    return RULE_TYPE_BADGE_STYLES[type] || 'badge-info';
};

/**
 * Get the short display label for a rule type
 */
export const getRuleTypeLabel = (type: string): string => {
    return RULE_TYPE_LABELS[type] || type;
};

/**
 * Get the full label (with description) for a rule type
 */
export const getRuleTypeFullLabel = (type: string): string => {
    const ruleType = RULE_TYPES.find(rt => rt.value === type);
    return ruleType?.label || type;
};
