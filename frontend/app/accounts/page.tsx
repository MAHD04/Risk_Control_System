'use client';

import { useState, useEffect, useCallback } from 'react';
import {
    Users,
    RefreshCw,
    AlertCircle,
    Loader2,
    Search,
    ShieldCheck,
    ShieldX,
    TrendingUp,
    TrendingDown,
    RotateCcw,
    ChevronLeft,
    ChevronRight,
    Eye,
    X,
} from 'lucide-react';
import { accountsApi } from '@/services';
import type { Account, PaginatedResponse } from '@/types';
import { formatDateTime } from '@/utils/date';

export default function AccountsPage() {
    const [accounts, setAccounts] = useState<Account[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [actionLoading, setActionLoading] = useState<number | null>(null);
    const [selectedAccount, setSelectedAccount] = useState<Account | null>(null);

    const [isInitialLoad, setIsInitialLoad] = useState(true);

    // Pagination
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [totalAccounts, setTotalAccounts] = useState(0);

    // Filters
    const [statusFilter, setStatusFilter] = useState<'all' | 'enable' | 'disable'>('all');
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        loadAccounts();
    }, [currentPage, statusFilter]);

    const loadAccounts = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await accountsApi.getAll({
                page: currentPage,
                per_page: 10,
                status: statusFilter === 'all' ? undefined : statusFilter,
            });

            setAccounts(response.data);
            setCurrentPage(response.current_page);
            setTotalPages(response.last_page);
            setTotalAccounts(response.total);
        } catch (err) {
            console.error('Error loading accounts:', err);
            setError('Failed to load accounts.');
        } finally {
            setLoading(false);
            setIsInitialLoad(false);
        }
    }, [currentPage, statusFilter]);

    const handleRestore = async (accountId: number) => {
        if (!confirm('Restore this account? Status and trading will be enabled.')) return;

        try {
            setActionLoading(accountId);
            await accountsApi.restore(accountId);
            await loadAccounts();
        } catch (err) {
            console.error('Error restoring account:', err);
            setError('Failed to restore account.');
        } finally {
            setActionLoading(null);
        }
    };

    const filteredAccounts = accounts.filter(account =>
        account.login.toString().includes(searchTerm)
    );

    // Stats
    const enabledAccounts = accounts.filter(a => a.status === 'enable').length;
    const disabledAccounts = accounts.filter(a => a.status === 'disable').length;
    const tradingDisabled = accounts.filter(a => a.trading_status === 'disable').length;

    if (isInitialLoad) {
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
                    <h1 className="text-2xl font-bold text-white">Accounts</h1>
                    <p className="mt-1 text-slate-400">
                        Prop firm trading accounts management
                    </p>
                </div>
                <button
                    onClick={loadAccounts}
                    disabled={loading}
                    className="btn btn-secondary"
                >
                    <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
                    Refresh
                </button>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-emerald-500/10">
                            <ShieldCheck className="w-5 h-5 text-emerald-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{enabledAccounts}</p>
                            <p className="text-sm text-slate-400">Active Accounts</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-red-500/10">
                            <ShieldX className="w-5 h-5 text-red-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{disabledAccounts}</p>
                            <p className="text-sm text-slate-400">Disabled Accounts</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-amber-500/10">
                            <TrendingDown className="w-5 h-5 text-amber-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{tradingDisabled}</p>
                            <p className="text-sm text-slate-400">Trading Disabled</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Filters */}
            <div className="card">
                <div className="flex flex-col md:flex-row gap-4">
                    <div className="flex-1">
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search className="w-4 h-4 text-slate-500" />
                            </div>
                            <input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder="Search by account number..."
                                className="input !pl-12"
                            />
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => setStatusFilter('all')}
                            className={`btn ${statusFilter === 'all' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            All
                        </button>
                        <button
                            onClick={() => setStatusFilter('enable')}
                            className={`btn ${statusFilter === 'enable' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            Active
                        </button>
                        <button
                            onClick={() => setStatusFilter('disable')}
                            className={`btn ${statusFilter === 'disable' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            Disabled
                        </button>
                    </div>
                </div>
            </div>

            {/* Error */}
            {error && (
                <div className="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400">
                    <AlertCircle className="w-5 h-5 flex-shrink-0" />
                    <p>{error}</p>
                    <button
                        onClick={loadAccounts}
                        className="ml-auto text-sm underline hover:no-underline"
                    >
                        Retry
                    </button>
                </div>
            )}

            {/* Table */}
            {filteredAccounts.length === 0 && !error ? (
                <div className="card text-center py-12">
                    <Users className="w-12 h-12 text-slate-600 mx-auto mb-4" />
                    <h3 className="text-lg font-medium text-white mb-2">No accounts found</h3>
                    <p className="text-slate-400">
                        {searchTerm || statusFilter !== 'all'
                            ? 'No accounts found with the applied filters.'
                            : 'No accounts registered in the system yet.'}
                    </p>
                </div>
            ) : (
                <>
                    <div className="table-container">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>Login</th>
                                    <th>Account Status</th>
                                    <th>Trading Status</th>
                                    <th>Creation Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredAccounts.map((account) => (
                                    <tr key={account.id}>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                <Users className="w-4 h-4 text-slate-500" />
                                                <span className="font-medium text-white">
                                                    #{account.login}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span className={`badge ${account.status === 'enable'
                                                ? 'badge-success'
                                                : 'badge-danger'
                                                }`}>
                                                {account.status === 'enable' ? (
                                                    <><ShieldCheck className="w-3 h-3 mr-1" /> Active</>
                                                ) : (
                                                    <><ShieldX className="w-3 h-3 mr-1" /> Disabled</>
                                                )}
                                            </span>
                                        </td>
                                        <td>
                                            <span className={`badge ${account.trading_status === 'enable'
                                                ? 'badge-success'
                                                : 'badge-warning'
                                                }`}>
                                                {account.trading_status === 'enable' ? (
                                                    <><TrendingUp className="w-3 h-3 mr-1" /> Enabled</>
                                                ) : (
                                                    <><TrendingDown className="w-3 h-3 mr-1" /> Disabled</>
                                                )}
                                            </span>
                                        </td>
                                        <td>
                                            <span className="text-slate-400">
                                                {new Date(account.created_at).toLocaleDateString('en-US')}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="flex items-center gap-2">
                                                <button
                                                    onClick={() => setSelectedAccount(account)}
                                                    className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                                    title="View details"
                                                >
                                                    <Eye className="w-4 h-4" />
                                                </button>
                                                {(account.status === 'disable' || account.trading_status === 'disable') && (
                                                    <button
                                                        onClick={() => handleRestore(account.id)}
                                                        disabled={actionLoading === account.id}
                                                        className="btn btn-secondary text-sm py-1.5 px-3"
                                                        title="Restore account"
                                                    >
                                                        {actionLoading === account.id ? (
                                                            <Loader2 className="w-4 h-4 animate-spin" />
                                                        ) : (
                                                            <RotateCcw className="w-4 h-4" />
                                                        )}
                                                        Restore
                                                    </button>
                                                )}
                                            </div>
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
                                Page {currentPage} of {totalPages} ({totalAccounts} accounts)
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

            {/* Account Detail Modal */}
            {selectedAccount && (
                <div className="modal-overlay" onClick={() => setSelectedAccount(null)}>
                    <div className="modal-content max-w-lg" onClick={e => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-xl font-bold text-white">Account Details</h2>
                            <button
                                onClick={() => setSelectedAccount(null)}
                                className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="space-y-4">
                            <div className="p-4 bg-slate-800/50 rounded-lg text-center">
                                <div className="w-16 h-16 bg-indigo-500/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <Users className="w-8 h-8 text-indigo-400" />
                                </div>
                                <h3 className="text-2xl font-bold text-white">#{selectedAccount.login}</h3>
                                <p className="text-slate-400 text-sm">Internal ID: {selectedAccount.id}</p>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="p-3 bg-slate-800/50 rounded-lg">
                                    <label className="text-xs text-slate-500 uppercase tracking-wider block mb-2">Account Status</label>
                                    <span className={`badge ${selectedAccount.status === 'enable' ? 'badge-success' : 'badge-danger'}`}>
                                        {selectedAccount.status === 'enable' ? 'Active' : 'Disabled'}
                                    </span>
                                </div>
                                <div className="p-3 bg-slate-800/50 rounded-lg">
                                    <label className="text-xs text-slate-500 uppercase tracking-wider block mb-2">Trading</label>
                                    <span className={`badge ${selectedAccount.trading_status === 'enable' ? 'badge-success' : 'badge-warning'}`}>
                                        {selectedAccount.trading_status === 'enable' ? 'Enabled' : 'Disabled'}
                                    </span>
                                </div>
                            </div>

                            <div className="p-3 bg-slate-800/50 rounded-lg">
                                <label className="text-xs text-slate-500 uppercase tracking-wider block mb-2">Creation Date</label>
                                <div className="flex items-center gap-2 text-white">
                                    <Users className="w-4 h-4 text-slate-400" />
                                    {formatDateTime(selectedAccount.created_at)}
                                </div>
                            </div>
                        </div>

                        <div className="mt-6 flex justify-end">
                            <button
                                onClick={() => setSelectedAccount(null)}
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
