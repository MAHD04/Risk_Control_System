'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { Plus, Edit2, Trash2, ToggleLeft, ToggleRight, AlertCircle, Loader2 } from 'lucide-react';
import Modal from '@/components/ui/Modal';
import { riskRulesApi } from '@/services';
import type { RiskRule } from '@/types';
import { getRuleTypeBadge, getRuleTypeLabel } from '@/constants/rules';

export default function RulesPage() {
    const [rules, setRules] = useState<RiskRule[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [actionLoading, setActionLoading] = useState<number | null>(null);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [ruleToDelete, setRuleToDelete] = useState<number | null>(null);

    useEffect(() => {
        loadRules();
    }, []);

    const loadRules = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await riskRulesApi.getAll();
            setRules(response.data);
        } catch (err) {
            console.error('Error loading rules:', err);
            setError('Failed to load rules. Please check your API connection.');
        } finally {
            setLoading(false);
        }
    };

    const handleToggleActive = async (rule: RiskRule) => {
        try {
            setActionLoading(rule.id);
            await riskRulesApi.update(rule.id, { is_active: !rule.is_active });
            setRules(rules.map(r =>
                r.id === rule.id ? { ...r, is_active: !r.is_active } : r
            ));
        } catch (err) {
            console.error('Error toggling rule:', err);
        } finally {
            setActionLoading(null);
        }
    };

    const confirmDelete = (ruleId: number) => {
        setRuleToDelete(ruleId);
        setDeleteModalOpen(true);
    };

    const handleDelete = async () => {
        if (!ruleToDelete) return;

        try {
            setActionLoading(ruleToDelete);
            await riskRulesApi.delete(ruleToDelete);
            setRules(rules.filter(r => r.id !== ruleToDelete));
            setDeleteModalOpen(false);
            setRuleToDelete(null);
        } catch (err) {
            console.error('Error deleting rule:', err);
        } finally {
            setActionLoading(null);
        }
    };

    if (loading) {
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
                    <h1 className="text-2xl font-bold text-white">Risk Rules</h1>
                    <p className="mt-1 text-slate-400">
                        Configure and manage your trading risk rules
                    </p>
                </div>
                <Link href="/rules/create" className="btn btn-primary">
                    <Plus className="w-4 h-4" />
                    Create Rule
                </Link>
            </div>

            {/* Error Message */}
            {error && (
                <div className="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400">
                    <AlertCircle className="w-5 h-5 flex-shrink-0" />
                    <p>{error}</p>
                    <button
                        onClick={loadRules}
                        className="ml-auto text-sm underline hover:no-underline"
                    >
                        Retry
                    </button>
                </div>
            )}

            {/* Rules Table */}
            {rules.length === 0 && !error ? (
                <div className="card text-center py-12">
                    <AlertCircle className="w-12 h-12 text-slate-600 mx-auto mb-4" />
                    <h3 className="text-lg font-medium text-white mb-2">No rules configured</h3>
                    <p className="text-slate-400 mb-6">Get started by creating your first risk rule.</p>
                    <Link href="/rules/create" className="btn btn-primary">
                        <Plus className="w-4 h-4" />
                        Create Your First Rule
                    </Link>
                </div>
            ) : (
                <div className="table-container">
                    <table className="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Incident Limit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {rules.map((rule) => (
                                <tr key={rule.id}>
                                    <td>
                                        <div>
                                            <p className="font-medium text-white">{rule.name}</p>
                                            {rule.description && (
                                                <p className="text-xs text-slate-500 mt-0.5">{rule.description}</p>
                                            )}
                                        </div>
                                    </td>
                                    <td>
                                        <span className={`badge ${getRuleTypeBadge(rule.rule_type)}`}>
                                            {getRuleTypeLabel(rule.rule_type)}
                                        </span>
                                    </td>
                                    <td>
                                        <span className={`badge ${rule.severity === 'HARD' ? 'badge-danger' : 'badge-warning'
                                            }`}>
                                            {rule.severity}
                                        </span>
                                    </td>
                                    <td>
                                        <span className={`badge ${rule.is_active ? 'badge-success' : 'badge-danger'
                                            }`}>
                                            {rule.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td>
                                        <span className="text-slate-400">
                                            {rule.incident_limit}
                                        </span>
                                    </td>
                                    <td>
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={() => handleToggleActive(rule)}
                                                disabled={actionLoading === rule.id}
                                                className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors disabled:opacity-50"
                                                title={rule.is_active ? 'Deactivate' : 'Activate'}
                                            >
                                                {rule.is_active ? (
                                                    <ToggleRight className="w-5 h-5 text-emerald-400" />
                                                ) : (
                                                    <ToggleLeft className="w-5 h-5" />
                                                )}
                                            </button>
                                            <Link
                                                href={`/rules/${rule.id}`}
                                                className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                                title="Edit"
                                            >
                                                <Edit2 className="w-4 h-4" />
                                            </Link>
                                            <button
                                                onClick={() => confirmDelete(rule.id)}
                                                disabled={actionLoading === rule.id}
                                                className="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800 transition-colors disabled:opacity-50"
                                                title="Delete"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            <Modal
                isOpen={deleteModalOpen}
                onClose={() => setDeleteModalOpen(false)}
                title="Delete Rule"
            >
                <div className="space-y-4">
                    <p className="text-slate-300">
                        Are you sure you want to delete this rule? This action cannot be undone.
                    </p>
                    <div className="flex items-center justify-end gap-3 pt-4">
                        <button
                            onClick={() => setDeleteModalOpen(false)}
                            className="btn btn-secondary"
                        >
                            Cancel
                        </button>
                        <button
                            onClick={handleDelete}
                            className="btn btn-danger"
                            disabled={actionLoading === ruleToDelete}
                        >
                            {actionLoading === ruleToDelete ? (
                                <Loader2 className="w-4 h-4 animate-spin mr-2" />
                            ) : (
                                <Trash2 className="w-4 h-4 mr-2" />
                            )}
                            Delete Rule
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    );
}
