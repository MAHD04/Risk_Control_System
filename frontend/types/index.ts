// =============================================================================
// Risk Control System - TypeScript Type Definitions
// =============================================================================

// -----------------------------------------------------------------------------
// Enums & Constants
// -----------------------------------------------------------------------------

export type RuleType = 'min_duration' | 'volume_consistency' | 'trade_frequency' | 'daily_loss_limit' | 'max_open_positions' | 'max_drawdown' | 'risk_per_trade';
export type Severity = 'SOFT' | 'HARD';
export type TradeType = 'BUY' | 'SELL';
export type TradeStatus = 'OPEN' | 'CLOSED';
export type AccountStatus = 'enable' | 'disable';
export type ActionType = 'DISABLE_ACCOUNT' | 'DISABLE_TRADING' | 'NOTIFY_EMAIL' | 'NOTIFY_SLACK';

// -----------------------------------------------------------------------------
// Core Models
// -----------------------------------------------------------------------------

export interface ConfiguredAction {
    id: number;
    name: string;
    action_type: ActionType;
    config: Record<string, unknown>;
    created_at: string;
    updated_at: string;
}

export interface RiskRule {
    id: number;
    name: string;
    description?: string;
    rule_type: RuleType;
    parameters: RiskRuleParameters;
    severity: Severity;
    incident_limit: number;
    is_active: boolean;
    actions?: ConfiguredAction[];
    created_at: string;
    updated_at: string;
}

export interface RiskRuleParameters {
    // MIN_DURATION rule
    min_duration_seconds?: number;
    // VOLUME rule
    max_volume?: number;
    min_factor?: number;
    max_factor?: number;
    lookback_trades?: number;
    time_window_minutes?: number;
    // FREQUENCY rule
    min_open_trades?: number;
    max_open_trades?: number;
    // Generic additional params
    value_type?: 'FIXED' | 'PERCENT';
    [key: string]: unknown;
}

export interface Account {
    id: number;
    login: number;
    status: AccountStatus;
    trading_status: AccountStatus;
    trades?: Trade[];
    incidents?: Incident[];
    created_at: string;
    updated_at: string;
}

export interface Trade {
    id: number;
    account_id: number;
    type: TradeType;
    volume: number;
    open_time: string;
    close_time: string | null;
    open_price: number;
    close_price: number | null;
    status: TradeStatus;
    account?: Account;
    incidents?: Incident[];
    created_at: string;
    updated_at: string;
}

export interface Incident {
    id: number;
    account_id: number;
    risk_rule_id: number;
    trade_id: number;
    details: IncidentDetails;
    triggered_at: string;
    account?: Account;
    risk_rule?: RiskRule;
    trade?: Trade;
    created_at: string;
    updated_at: string;
}

export interface IncidentDetails {
    rule_name?: string;
    rule_type?: RuleType;
    severity?: Severity;
    violation_details?: Record<string, unknown>;
    actions_taken?: string[];
    [key: string]: unknown;
}

// -----------------------------------------------------------------------------
// API Response Types
// -----------------------------------------------------------------------------

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface ApiResponse<T> {
    data: T;
    message?: string;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
}

// -----------------------------------------------------------------------------
// Request DTOs
// -----------------------------------------------------------------------------

export interface CreateRiskRuleRequest {
    name: string;
    description?: string;
    rule_type: RuleType;
    parameters: RiskRuleParameters;
    severity: Severity;
    incident_limit: number;
    is_active?: boolean;
}

export interface UpdateRiskRuleRequest extends Partial<CreateRiskRuleRequest> { }

export interface AttachActionsRequest {
    action_ids: number[];
}

export interface CreateTradeRequest {
    account_id: number;
    type: TradeType;
    volume: number;
    open_time?: string;
    open_price: number;
}

export interface UpdateTradeRequest {
    close_time?: string;
    close_price?: number;
    status?: TradeStatus;
}

export interface CreateAccountRequest {
    login: number;
    status?: AccountStatus;
    trading_status?: AccountStatus;
}

// -----------------------------------------------------------------------------
// Account Stats
// -----------------------------------------------------------------------------

export interface AccountStats {
    account_id: number;
    total_incidents: number;
    incidents_by_severity: {
        SOFT: number;
        HARD: number;
    };
    incidents_by_rule: Record<string, number>;
}

// -----------------------------------------------------------------------------
// Dashboard Stats
// -----------------------------------------------------------------------------

export interface DashboardStats {
    active_rules: number;
    total_incidents: number;
    incidents_today: number;
    active_accounts: number;
    disabled_accounts: number;
    open_trades: number;
}
