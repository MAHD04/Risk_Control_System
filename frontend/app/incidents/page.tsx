'use client';

import { useState, useEffect, useCallback } from 'react';
import {
    AlertTriangle,
    Filter,
    RefreshCw,
    AlertCircle,
    Loader2,
    Clock,
    User,
    ShieldAlert,
    TrendingUp,
    ChevronLeft,
    ChevronRight,
    Eye,
    X
} from 'lucide-react';
import { incidentsApi, riskRulesApi, accountsApi } from '@/services';
import type { Incident, RiskRule, Account, PaginatedResponse } from '@/types';
import { formatDateTime, getRelativeTime } from '@/utils/date';
import { getRuleTypeBadge, getRuleTypeFullLabel } from '@/constants/rules';

export default function IncidentsPage() {
    // Data state
    const [incidents, setIncidents] = useState<Incident[]>([]);
    const [rules, setRules] = useState<RiskRule[]>([]);
    const [accounts, setAccounts] = useState<Account[]>([]);

    // UI state
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [selectedIncident, setSelectedIncident] = useState<Incident | null>(null);

    // Pagination state
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [totalIncidents, setTotalIncidents] = useState(0);

    // Filter state
    const [filters, setFilters] = useState({
        account_id: '',
        risk_rule_id: '',
    });
    const [showFilters, setShowFilters] = useState(false);

    // Load initial data
    useEffect(() => {
        loadInitialData();
    }, []);

    // Load incidents when filters or page change
    useEffect(() => {
        loadIncidents();
    }, [currentPage, filters.account_id, filters.risk_rule_id]);

    const loadInitialData = async () => {
        try {
            // Load rules and accounts for filter dropdowns
            const [rulesResponse, accountsResponse] = await Promise.all([
                riskRulesApi.getAll(),
                accountsApi.getAll(),
            ]);
            setRules(rulesResponse.data);
            setAccounts(accountsResponse.data);
        } catch (err) {
            console.error('Error loading filter data:', err);
        }
    };

    const loadIncidents = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await incidentsApi.getAll({
                page: currentPage,
                per_page: 10,
                account_id: filters.account_id ? parseInt(filters.account_id) : undefined,
                risk_rule_id: filters.risk_rule_id ? parseInt(filters.risk_rule_id) : undefined,
            });

            setIncidents(response.data);
            setCurrentPage(response.current_page);
            setTotalPages(response.last_page);
            setTotalIncidents(response.total);
        } catch (err) {
            console.error('Error loading incidents:', err);
            setError('Failed to load incidents. Please check your API connection.');
        } finally {
            setLoading(false);
        }
    }, [currentPage, filters]);

    const handleFilterChange = (key: string, value: string) => {
        setFilters(prev => ({ ...prev, [key]: value }));
        setCurrentPage(1); // Reset to first page when filtering
    };

    const clearFilters = () => {
        setFilters({ account_id: '', risk_rule_id: '' });
        setCurrentPage(1);
    };

    // formatDate and getRelativeTime now imported from @/utils/date

    // Note: getRuleTypeBadge and getRuleTypeFullLabel imported from @/constants/rules
    // Using getRuleTypeFullLabel as getRuleTypeLabel for full labels like "Minimum Duration"
    const getRuleTypeLabel = getRuleTypeFullLabel;

    // Loading state
    if (loading && incidents.length === 0) {
        return (
            <div className="flex items-center justify-center h-96">
                <Loader2 className="w-8 h-8 text-indigo-500 animate-spin" />
            </div>
        );
    }

    return (
        <div className="animate-fade-in space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-white">Incidents</h1>
                    <p className="mt-1 text-slate-400">
                        Risk rule violations history
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        onClick={() => setShowFilters(!showFilters)}
                        className={`btn btn-secondary ${showFilters ? 'bg-slate-700' : ''}`}
                    >
                        <Filter className="w-4 h-4" />
                        Filters
                    </button>
                    <button
                        onClick={loadIncidents}
                        disabled={loading}
                        className="btn btn-secondary"
                    >
                        <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
                        Refresh
                    </button>
                </div>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-amber-500/10">
                            <AlertTriangle className="w-5 h-5 text-amber-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{totalIncidents}</p>
                            <p className="text-sm text-slate-400">Total Incidents</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-red-500/10">
                            <ShieldAlert className="w-5 h-5 text-red-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">
                                {incidents.filter(i => i.details?.severity === 'HARD').length}
                            </p>
                            <p className="text-sm text-slate-400">HARD Rules (Current Page)</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-yellow-500/10">
                            <AlertCircle className="w-5 h-5 text-yellow-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">
                                {incidents.filter(i => i.details?.severity === 'SOFT').length}
                            </p>
                            <p className="text-sm text-slate-400">SOFT Rules (Current Page)</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Filters Panel */}
            {showFilters && (
                <div className="card animate-fade-in">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="font-medium text-white">Filter Incidents</h3>
                        {(filters.account_id || filters.risk_rule_id) && (
                            <button
                                onClick={clearFilters}
                                className="text-sm text-indigo-400 hover:text-indigo-300"
                            >
                                Clear filters
                            </button>
                        )}
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-300 mb-2">
                                Account
                            </label>
                            <select
                                value={filters.account_id}
                                onChange={(e) => handleFilterChange('account_id', e.target.value)}
                                className="select"
                            >
                                <option value="">All accounts</option>
                                {accounts.map((account) => (
                                    <option key={account.id} value={account.id}>
                                        #{account.login}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-300 mb-2">
                                Risk Rule
                            </label>
                            <select
                                value={filters.risk_rule_id}
                                onChange={(e) => handleFilterChange('risk_rule_id', e.target.value)}
                                className="select"
                            >
                                <option value="">All rules</option>
                                {rules.map((rule) => (
                                    <option key={rule.id} value={rule.id}>
                                        {rule.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>
            )}

            {/* Error Message */}
            {error && (
                <div className="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400">
                    <AlertCircle className="w-5 h-5 flex-shrink-0" />
                    <p>{error}</p>
                    <button
                        onClick={loadIncidents}
                        className="ml-auto text-sm underline hover:no-underline"
                    >
                        Retry
                    </button>
                </div>
            )}

            {/* Incidents Table */}
            {incidents.length === 0 && !error ? (
                <div className="card text-center py-12">
                    <AlertCircle className="w-12 h-12 text-slate-600 mx-auto mb-4" />
                    <h3 className="text-lg font-medium text-white mb-2">No incidents found</h3>
                    <p className="text-slate-400">
                        {filters.account_id || filters.risk_rule_id
                            ? 'No incidents found with the selected filters.'
                            : 'No rule violations have been recorded yet.'}
                    </p>
                </div>
            ) : (
                <>
                    <div className="table-container">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Violated Rule</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {incidents.map((incident) => (
                                    <tr key={incident.id}>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                <Clock className="w-4 h-4 text-slate-500" />
                                                <div>
                                                    <p className="font-medium text-white">
                                                        {formatDateTime(incident.triggered_at)}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        {getRelativeTime(incident.triggered_at)}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                <User className="w-4 h-4 text-slate-500" />
                                                <span className="text-slate-300">
                                                    #{incident.account?.login || incident.account_id}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <p className="font-medium text-white">
                                                    {incident.risk_rule?.name || incident.details?.rule_name || 'Unknown Rule'}
                                                </p>
                                                {incident.risk_rule?.description && (
                                                    <p className="text-xs text-slate-500 mt-0.5 truncate max-w-xs">
                                                        {incident.risk_rule.description}
                                                    </p>
                                                )}
                                            </div>
                                        </td>
                                        <td>
                                            <span className={`badge ${getRuleTypeBadge(
                                                incident.risk_rule?.rule_type || incident.details?.rule_type || ''
                                            )}`}>
                                                {getRuleTypeLabel(
                                                    incident.risk_rule?.rule_type || incident.details?.rule_type || ''
                                                )}
                                            </span>
                                        </td>
                                        <td>
                                            <span className={`badge ${(incident.risk_rule?.severity || incident.details?.severity) === 'HARD'
                                                ? 'badge-danger'
                                                : 'badge-warning'
                                                }`}>
                                                {incident.risk_rule?.severity || incident.details?.severity || 'N/A'}
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                onClick={() => setSelectedIncident(incident)}
                                                className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                                title="View details"
                                            >
                                                <Eye className="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {totalPages > 1 && (
                        <div className="flex items-center justify-between">
                            <p className="text-sm text-slate-400">
                                Showing page {currentPage} of {totalPages} ({totalIncidents} total)
                            </p>
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                                    disabled={currentPage === 1}
                                    className="btn btn-secondary disabled:opacity-50"
                                >
                                    <ChevronLeft className="w-4 h-4" />
                                    Previous
                                </button>
                                <button
                                    onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                                    disabled={currentPage === totalPages}
                                    className="btn btn-secondary disabled:opacity-50"
                                >
                                    Next
                                    <ChevronRight className="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    )}
                </>
            )}

            {/* Incident Detail Modal */}
            {selectedIncident && (
                <div className="modal-overlay" onClick={() => setSelectedIncident(null)}>
                    <div className="modal-content max-w-lg" onClick={e => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-xl font-bold text-white">Incident Details</h2>
                            <button
                                onClick={() => setSelectedIncident(null)}
                                className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="space-y-4">
                            {/* Basic Info */}
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="text-xs text-slate-500 uppercase tracking-wider">ID</label>
                                    <p className="text-white font-medium">#{selectedIncident.id}</p>
                                </div>
                                <div>
                                    <label className="text-xs text-slate-500 uppercase tracking-wider">Date</label>
                                    <p className="text-white">{formatDateTime(selectedIncident.triggered_at)}</p>
                                </div>
                            </div>

                            {/* Account */}
                            <div className="p-3 bg-slate-800/50 rounded-lg">
                                <div className="flex items-center gap-2 mb-2">
                                    <User className="w-4 h-4 text-indigo-400" />
                                    <label className="text-xs text-slate-500 uppercase tracking-wider">Affected Account</label>
                                </div>
                                <p className="text-white font-medium">
                                    #{selectedIncident.account?.login || selectedIncident.account_id}
                                </p>
                                {selectedIncident.account && (
                                    <div className="flex gap-2 mt-2">
                                        <span className={`badge ${selectedIncident.account.status === 'enable' ? 'badge-success' : 'badge-danger'}`}>
                                            Status: {selectedIncident.account.status}
                                        </span>
                                        <span className={`badge ${selectedIncident.account.trading_status === 'enable' ? 'badge-success' : 'badge-danger'}`}>
                                            Trading: {selectedIncident.account.trading_status}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Rule Info */}
                            <div className="p-3 bg-slate-800/50 rounded-lg">
                                <div className="flex items-center gap-2 mb-2">
                                    <ShieldAlert className="w-4 h-4 text-amber-400" />
                                    <label className="text-xs text-slate-500 uppercase tracking-wider">Violated Rule</label>
                                </div>
                                <p className="text-white font-medium">
                                    {selectedIncident.risk_rule?.name || selectedIncident.details?.rule_name || 'Unknown'}
                                </p>
                                <div className="flex gap-2 mt-2">
                                    <span className={`badge ${getRuleTypeBadge(
                                        selectedIncident.risk_rule?.rule_type || selectedIncident.details?.rule_type || ''
                                    )}`}>
                                        {getRuleTypeLabel(selectedIncident.risk_rule?.rule_type || selectedIncident.details?.rule_type || '')}
                                    </span>
                                    <span className={`badge ${(selectedIncident.risk_rule?.severity || selectedIncident.details?.severity) === 'HARD'
                                        ? 'badge-danger'
                                        : 'badge-warning'
                                        }`}>
                                        {selectedIncident.risk_rule?.severity || selectedIncident.details?.severity}
                                    </span>
                                </div>
                            </div>

                            {/* Trade Info */}
                            {selectedIncident.trade && (
                                <div className="p-3 bg-slate-800/50 rounded-lg">
                                    <div className="flex items-center gap-2 mb-2">
                                        <TrendingUp className="w-4 h-4 text-emerald-400" />
                                        <label className="text-xs text-slate-500 uppercase tracking-wider">Related Trade</label>
                                    </div>
                                    <div className="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span className="text-slate-400">Type:</span>
                                            <span className={`ml-2 ${selectedIncident.trade.type === 'BUY' ? 'text-emerald-400' : 'text-red-400'}`}>
                                                {selectedIncident.trade.type}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="text-slate-400">Volume:</span>
                                            <span className="ml-2 text-white">{selectedIncident.trade.volume}</span>
                                        </div>
                                        <div>
                                            <span className="text-slate-400">Open price:</span>
                                            <span className="ml-2 text-white">{selectedIncident.trade.open_price}</span>
                                        </div>
                                        <div>
                                            <span className="text-slate-400">Status:</span>
                                            <span className={`ml-2 badge ${selectedIncident.trade.status === 'OPEN' ? 'badge-success' : 'badge-info'}`}>
                                                {selectedIncident.trade.status}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Violation Details */}
                            {selectedIncident.details?.violation_details && (
                                <div className="p-3 bg-slate-800/50 rounded-lg">
                                    <label className="text-xs text-slate-500 uppercase tracking-wider block mb-2">
                                        Violation Details
                                    </label>
                                    <pre className="text-xs text-slate-300 overflow-x-auto">
                                        {JSON.stringify(selectedIncident.details.violation_details, null, 2)}
                                    </pre>
                                </div>
                            )}

                            {/* Actions Taken */}
                            {selectedIncident.details?.actions_taken && selectedIncident.details.actions_taken.length > 0 && (
                                <div className="p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                                    <label className="text-xs text-red-400 uppercase tracking-wider block mb-2">
                                        Executed Actions
                                    </label>
                                    <ul className="space-y-1">
                                        {selectedIncident.details.actions_taken.map((action, idx) => (
                                            <li key={idx} className="flex items-center gap-2 text-sm text-red-300">
                                                <span className="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                                {action}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>

                        <div className="mt-6 flex justify-end">
                            <button
                                onClick={() => setSelectedIncident(null)}
                                className="btn btn-secondary"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
