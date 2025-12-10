'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Save, X, Loader2, Plus } from 'lucide-react';
import { riskRulesApi } from '@/services';
import type { RiskRule, ConfiguredAction, CreateRiskRuleRequest, RuleType, Severity } from '@/types';
import {
    RULE_TYPES,
    SEVERITIES,
    RULE_DEFAULT_PARAMETERS,
    DEFAULT_INCIDENT_LIMIT
} from '@/constants/rules';

interface RuleFormProps {
    rule?: RiskRule;
    isEditing?: boolean;
}

export default function RuleForm({ rule, isEditing = false }: RuleFormProps) {
    const router = useRouter();
    const [loading, setLoading] = useState(false);
    const [loadingActions, setLoadingActions] = useState(true);
    const [availableActions, setAvailableActions] = useState<ConfiguredAction[]>([]);
    const [selectedActionIds, setSelectedActionIds] = useState<number[]>([]);

    // Form state
    const [name, setName] = useState(rule?.name || '');
    const [description, setDescription] = useState(rule?.description || '');
    const [ruleType, setRuleType] = useState<RuleType>(rule?.rule_type || 'min_duration');
    const [severity, setSeverity] = useState<Severity>(rule?.severity || 'SOFT');
    const [incidentLimit, setIncidentLimit] = useState(rule?.incident_limit || DEFAULT_INCIDENT_LIMIT);
    const [isActive, setIsActive] = useState(rule?.is_active ?? true);
    const [ruleCategory, setRuleCategory] = useState<'BASIC' | 'OTHER'>('BASIC');

    // Parameters based on rule type - using centralized defaults
    const minDurationDefaults = RULE_DEFAULT_PARAMETERS.min_duration;
    const volumeDefaults = RULE_DEFAULT_PARAMETERS.volume_consistency;
    const frequencyDefaults = RULE_DEFAULT_PARAMETERS.trade_frequency;
    const dailyLossDefaults = RULE_DEFAULT_PARAMETERS.daily_loss_limit;
    const maxPositionsDefaults = RULE_DEFAULT_PARAMETERS.max_open_positions;
    const maxDrawdownDefaults = RULE_DEFAULT_PARAMETERS.max_drawdown;
    const riskPerTradeDefaults = RULE_DEFAULT_PARAMETERS.risk_per_trade;

    const [minDurationSeconds, setMinDurationSeconds] = useState(
        rule?.parameters?.min_duration_seconds || minDurationDefaults.min_duration_seconds
    );
    const [minFactor, setMinFactor] = useState(
        rule?.parameters?.min_factor || volumeDefaults.min_factor
    );
    const [maxFactor, setMaxFactor] = useState(
        rule?.parameters?.max_factor || volumeDefaults.max_factor
    );
    const [lookbackTrades, setLookbackTrades] = useState(
        rule?.parameters?.lookback_trades || volumeDefaults.lookback_trades
    );
    const [timeWindowMinutes, setTimeWindowMinutes] = useState(
        rule?.parameters?.time_window_minutes || frequencyDefaults.time_window_minutes
    );
    const [minOpenTrades, setMinOpenTrades] = useState(
        rule?.parameters?.min_open_trades || frequencyDefaults.min_open_trades
    );
    const [maxOpenTrades, setMaxOpenTrades] = useState(
        rule?.parameters?.max_open_trades || frequencyDefaults.max_open_trades
    );
    const [maxDailyLoss, setMaxDailyLoss] = useState(
        rule?.parameters?.max_daily_loss || dailyLossDefaults.max_daily_loss
    );
    const [maxPositions, setMaxPositions] = useState(
        rule?.parameters?.max_positions || maxPositionsDefaults.max_positions
    );
    const [maxDrawdownAmount, setMaxDrawdownAmount] = useState(
        rule?.parameters?.max_drawdown_amount || maxDrawdownDefaults.max_drawdown_amount
    );
    const [maxRiskAmount, setMaxRiskAmount] = useState(
        rule?.parameters?.max_risk_amount || riskPerTradeDefaults.max_risk_amount
    );
    // Default to PERCENT as it is the industry standard
    const [valueType] = useState<'PERCENT'>('PERCENT');

    useEffect(() => {
        loadAvailableActions();
    }, []);

    useEffect(() => {
        if (rule?.actions) {
            setSelectedActionIds(rule.actions.map(a => a.id));
        }
    }, [rule]);

    const loadAvailableActions = async () => {
        try {
            const actions = await riskRulesApi.listActions();
            setAvailableActions(actions);
        } catch (err) {
            console.error('Error loading actions:', err);
        } finally {
            setLoadingActions(false);
        }
    };

    const buildParameters = () => {
        switch (ruleType) {
            case 'min_duration':
                return { min_duration_seconds: minDurationSeconds };
            case 'volume_consistency':
                return {
                    min_factor: minFactor,
                    max_factor: maxFactor,
                    lookback_trades: lookbackTrades
                };
            case 'trade_frequency':
                return {
                    time_window_minutes: timeWindowMinutes,
                    min_open_trades: minOpenTrades,
                    max_open_trades: maxOpenTrades
                };
            case 'daily_loss_limit':
                return { max_daily_loss: maxDailyLoss };
            case 'max_open_positions':
                return { max_positions: maxPositions };
            case 'max_drawdown':
                return { max_drawdown_amount: maxDrawdownAmount, value_type: valueType };
            case 'risk_per_trade':
                return { max_risk_amount: maxRiskAmount, value_type: valueType };
            default:
                return {};
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        try {
            setLoading(true);

            const data: CreateRiskRuleRequest = {
                name,
                description: description || undefined,
                rule_type: ruleType,
                severity,
                incident_limit: severity === 'SOFT' ? incidentLimit : 1,
                is_active: isActive,
                parameters: buildParameters(),
            };

            let savedRule: RiskRule;

            if (isEditing && rule) {
                savedRule = await riskRulesApi.update(rule.id, data);
            } else {
                savedRule = await riskRulesApi.create(data);
            }

            // Attach actions if selected
            if (selectedActionIds.length > 0) {
                await riskRulesApi.attachActions(savedRule.id, { action_ids: selectedActionIds });
            }

            router.push('/rules');
        } catch (err) {
            console.error('Error saving rule:', err);
            alert('Error saving rule. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const toggleAction = (actionId: number) => {
        setSelectedActionIds(prev =>
            prev.includes(actionId)
                ? prev.filter(id => id !== actionId)
                : [...prev, actionId]
        );
    };

    return (
        <form onSubmit={handleSubmit} className="animate-fade-in space-y-8">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-white">
                        {isEditing ? 'Edit Rule' : 'Create New Rule'}
                    </h1>
                    <p className="mt-1 text-slate-400">
                        {isEditing
                            ? 'Modify the rule configuration'
                            : 'Configure a new risk rule for your trading accounts'}
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={() => router.push('/rules')}
                        className="btn btn-secondary"
                    >
                        <X className="w-4 h-4" />
                        Cancel
                    </button>
                    <button
                        type="submit"
                        disabled={loading}
                        className="btn btn-primary"
                    >
                        {loading ? (
                            <Loader2 className="w-4 h-4 animate-spin" />
                        ) : (
                            <Save className="w-4 h-4" />
                        )}
                        {isEditing ? 'Save Changes' : 'Create Rule'}
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Main Form */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Basic Info */}
                    <div className="card">
                        <h2 className="text-lg font-semibold text-white mb-6">Basic Information</h2>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Rule Name *
                                </label>
                                <input
                                    type="text"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    className="input"
                                    placeholder="e.g., Quick Trade Detection"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Description
                                </label>
                                <textarea
                                    value={description}
                                    onChange={(e) => setDescription(e.target.value)}
                                    className="input min-h-[80px]"
                                    placeholder="Optional description of what this rule does..."
                                />
                            </div>
                            <div className="flex items-center gap-3">
                                <label className="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={isActive}
                                        onChange={(e) => setIsActive(e.target.checked)}
                                        className="sr-only peer"
                                    />
                                    <div className={`toggle ${isActive ? 'active' : ''}`}></div>
                                </label>
                                <span className="text-sm text-slate-300">
                                    {isActive ? 'Rule is Active' : 'Rule is Inactive'}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Rule Type */}
                    <div className="card">
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-lg font-semibold text-white">Rule Type</h2>
                            <div className="relative">
                                <select
                                    value={ruleCategory}
                                    onChange={(e) => setRuleCategory(e.target.value as 'BASIC' | 'OTHER')}
                                    className="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5"
                                >
                                    <option value="BASIC">Basic Rules</option>
                                    <option value="OTHER">Other Rules</option>
                                </select>
                            </div>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {RULE_TYPES.filter(type => type.category === ruleCategory).map((type) => (
                                <button
                                    key={type.value}
                                    type="button"
                                    onClick={() => setRuleType(type.value)}
                                    className={`p-4 rounded-lg border text-left transition-all ${ruleType === type.value
                                        ? 'border-indigo-500 bg-indigo-500/10'
                                        : 'border-slate-700 hover:border-slate-600 bg-slate-900/50'
                                        }`}
                                >
                                    <p className={`font-medium ${ruleType === type.value ? 'text-indigo-400' : 'text-white'
                                        }`}>
                                        {type.label}
                                    </p>
                                    <p className="text-xs text-slate-500 mt-1">
                                        {type.description}
                                    </p>
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Dynamic Parameters */}
                    <div className="card">
                        <h2 className="text-lg font-semibold text-white mb-6">Parameters</h2>

                        {ruleType === 'min_duration' && (
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Minimum Duration (seconds) *
                                </label>
                                <input
                                    type="number"
                                    value={minDurationSeconds}
                                    onChange={(e) => setMinDurationSeconds(Number(e.target.value))}
                                    className="input max-w-xs"
                                    min="1"
                                    required
                                />
                                <p className="text-xs text-slate-500 mt-2">
                                    Trades that close in less than this time will trigger an incident.
                                </p>
                            </div>
                        )}

                        {ruleType === 'volume_consistency' && (
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-slate-300 mb-2">
                                            Min Factor *
                                        </label>
                                        <input
                                            type="number"
                                            value={minFactor}
                                            onChange={(e) => setMinFactor(Number(e.target.value))}
                                            className="input"
                                            step="0.1"
                                            min="0"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-slate-300 mb-2">
                                            Max Factor *
                                        </label>
                                        <input
                                            type="number"
                                            value={maxFactor}
                                            onChange={(e) => setMaxFactor(Number(e.target.value))}
                                            className="input"
                                            step="0.1"
                                            min="0"
                                            required
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-300 mb-2">
                                        Lookback Trades *
                                    </label>
                                    <input
                                        type="number"
                                        value={lookbackTrades}
                                        onChange={(e) => setLookbackTrades(Number(e.target.value))}
                                        className="input max-w-xs"
                                        min="1"
                                        required
                                    />
                                </div>
                                <p className="text-xs text-slate-500">
                                    Volume is valid if between {minFactor}x and {maxFactor}x of the average of last {lookbackTrades} trades.
                                </p>
                            </div>
                        )}

                        {ruleType === 'trade_frequency' && (
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-slate-300 mb-2">
                                        Time Window (minutes) *
                                    </label>
                                    <input
                                        type="number"
                                        value={timeWindowMinutes}
                                        onChange={(e) => setTimeWindowMinutes(Number(e.target.value))}
                                        className="input max-w-xs"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-slate-300 mb-2">
                                            Min Open Trades
                                        </label>
                                        <input
                                            type="number"
                                            value={minOpenTrades}
                                            onChange={(e) => setMinOpenTrades(Number(e.target.value))}
                                            className="input"
                                            min="0"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-slate-300 mb-2">
                                            Max Open Trades
                                        </label>
                                        <input
                                            type="number"
                                            value={maxOpenTrades}
                                            onChange={(e) => setMaxOpenTrades(Number(e.target.value))}
                                            className="input"
                                            min="0"
                                        />
                                    </div>
                                </div>
                                <p className="text-xs text-slate-500">
                                    Triggers if user opens more than {maxOpenTrades} or less than {minOpenTrades} trades in {timeWindowMinutes} minutes.
                                </p>
                            </div>
                        )}

                        {ruleType === 'daily_loss_limit' && (
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Max Daily Loss ($) *
                                </label>
                                <input
                                    type="number"
                                    value={maxDailyLoss}
                                    onChange={(e) => setMaxDailyLoss(Number(e.target.value))}
                                    className="input max-w-xs"
                                    min="1"
                                    required
                                />
                                <p className="text-xs text-slate-500 mt-2">
                                    Triggers if the account loses more than this amount in a single day (realized PnL).
                                </p>
                            </div>
                        )}

                        {ruleType === 'max_open_positions' && (
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Max Open Positions *
                                </label>
                                <input
                                    type="number"
                                    value={maxPositions}
                                    onChange={(e) => setMaxPositions(Number(e.target.value))}
                                    className="input max-w-xs"
                                    min="1"
                                    required
                                />
                                <p className="text-xs text-slate-500 mt-2">
                                    Triggers if the number of concurrent open trades exceeds this limit.
                                </p>
                            </div>
                        )}

                        {ruleType === 'max_drawdown' && (
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Max Drawdown Percentage (%) *
                                </label>
                                <input
                                    type="number"
                                    value={maxDrawdownAmount}
                                    onChange={(e) => setMaxDrawdownAmount(Number(e.target.value))}
                                    className="input max-w-xs"
                                    min="0.1"
                                    step="0.1"
                                    required
                                />
                                <p className="text-xs text-slate-500 mt-2">
                                    Triggers if the total realized loss exceeds this percentage of the initial balance.
                                </p>
                            </div>
                        )}

                        {ruleType === 'risk_per_trade' && (
                            <div>
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Max Risk Per Trade (%) *
                                </label>
                                <input
                                    type="number"
                                    value={maxRiskAmount}
                                    onChange={(e) => setMaxRiskAmount(Number(e.target.value))}
                                    className="input max-w-xs"
                                    min="0.1"
                                    step="0.1"
                                    required
                                />
                                <p className="text-xs text-slate-500 mt-2">
                                    Triggers if risk exceeds this percentage of the current account balance.
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Severity */}
                    <div className="card">
                        <h2 className="text-lg font-semibold text-white mb-6">Severity</h2>
                        <div className="space-y-3">
                            {SEVERITIES.map((sev) => (
                                <button
                                    key={sev.value}
                                    type="button"
                                    onClick={() => setSeverity(sev.value)}
                                    className={`w-full p-4 rounded-lg border text-left transition-all ${severity === sev.value
                                        ? sev.value === 'HARD'
                                            ? 'border-red-500 bg-red-500/10'
                                            : 'border-amber-500 bg-amber-500/10'
                                        : 'border-slate-700 hover:border-slate-600 bg-slate-900/50'
                                        }`}
                                >
                                    <p className={`font-medium ${severity === sev.value
                                        ? sev.value === 'HARD' ? 'text-red-400' : 'text-amber-400'
                                        : 'text-white'
                                        }`}>
                                        {sev.label}
                                    </p>
                                    <p className="text-xs text-slate-500 mt-1">
                                        {sev.description}
                                    </p>
                                </button>
                            ))}
                        </div>

                        {severity === 'SOFT' && (
                            <div className="mt-4 pt-4 border-t border-slate-800">
                                <label className="block text-sm font-medium text-slate-300 mb-2">
                                    Incidents Before Action
                                </label>
                                <input
                                    type="number"
                                    value={incidentLimit}
                                    onChange={(e) => setIncidentLimit(Number(e.target.value))}
                                    className="input"
                                    min="1"
                                    required
                                />
                                <p className="text-xs text-slate-500 mt-2">
                                    Action triggers after {incidentLimit} violations.
                                </p>
                            </div>
                        )}
                    </div>

                    {/* Actions */}
                    <div className="card">
                        <h2 className="text-lg font-semibold text-white mb-6">Actions</h2>
                        {loadingActions ? (
                            <div className="flex items-center justify-center py-8">
                                <Loader2 className="w-6 h-6 text-indigo-500 animate-spin" />
                            </div>
                        ) : availableActions.length === 0 ? (
                            <p className="text-sm text-slate-500 text-center py-4">
                                No actions configured in the system.
                            </p>
                        ) : (
                            <div className="space-y-2">
                                {availableActions.map((action) => (
                                    <button
                                        key={action.id}
                                        type="button"
                                        onClick={() => toggleAction(action.id)}
                                        className={`w-full p-3 rounded-lg border text-left transition-all flex items-center justify-between ${selectedActionIds.includes(action.id)
                                            ? 'border-indigo-500 bg-indigo-500/10'
                                            : 'border-slate-700 hover:border-slate-600 bg-slate-900/50'
                                            }`}
                                    >
                                        <div>
                                            <p className={`text-sm font-medium ${selectedActionIds.includes(action.id)
                                                ? 'text-indigo-400'
                                                : 'text-white'
                                                }`}>
                                                {action.name}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                {action.action_type.replace('_', ' ')}
                                            </p>
                                        </div>
                                        {selectedActionIds.includes(action.id) && (
                                            <div className="w-5 h-5 rounded-full bg-indigo-500 flex items-center justify-center">
                                                <Plus className="w-3 h-3 text-white rotate-45" />
                                            </div>
                                        )}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </form>
    );
}
