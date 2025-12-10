'use client';

import { useState, useEffect, useCallback } from 'react';
import {
    TrendingUp,
    TrendingDown,
    RefreshCw,
    AlertCircle,
    Loader2,
    Search,
    ArrowUpRight,
    ArrowDownRight,
    Clock,
    DollarSign,
    BarChart3,
    ChevronLeft,
    ChevronRight,
    Eye,
    X,
} from 'lucide-react';
import Modal from '@/components/ui/Modal';
import { tradesApi } from '@/services';
import type { Trade, PaginatedResponse } from '@/types';
import { formatDateTime, calculateDuration } from '@/utils/date';

export default function TradesPage() {
    const [trades, setTrades] = useState<Trade[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isInitialLoad, setIsInitialLoad] = useState(true);
    const [selectedTrade, setSelectedTrade] = useState<Trade | null>(null);

    // Pagination
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [totalTrades, setTotalTrades] = useState(0);

    // Filters
    const [statusFilter, setStatusFilter] = useState<'all' | 'OPEN' | 'CLOSED'>('all');
    const [accountFilter, setAccountFilter] = useState('');
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        loadTrades();
    }, [currentPage, statusFilter, accountFilter]);

    const loadTrades = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await tradesApi.getAll({
                page: currentPage,
                per_page: 10,
                status: statusFilter === 'all' ? undefined : statusFilter,
                account_id: accountFilter ? parseInt(accountFilter) : undefined,
            });

            setTrades(response.data);
            setCurrentPage(response.current_page);
            setTotalPages(response.last_page);
            setTotalTrades(response.total);
        } catch (err) {
            console.error('Error loading trades:', err);
            setError('Failed to load trades. Please check your API connection.');
        } finally {
            setLoading(false);
            setIsInitialLoad(false);
        }
    }, [currentPage, statusFilter, accountFilter]);

    const filteredTrades = trades.filter(trade =>
        trade.id.toString().includes(searchTerm) ||
        trade.account_id.toString().includes(searchTerm)
    );

    // Stats
    const openTrades = trades.filter(t => t.status === 'OPEN').length;
    const closedTrades = trades.filter(t => t.status === 'CLOSED').length;
    const buyTrades = trades.filter(t => t.type === 'BUY').length;

    // formatDateTime and calculateDuration now imported from @/utils/date

    const formatPrice = (price: number | string | null) => {
        if (price === null || price === undefined) return '-';
        const numPrice = typeof price === 'string' ? parseFloat(price) : price;
        if (isNaN(numPrice)) return '-';
        return numPrice.toFixed(5);
    };

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
                    <h1 className="text-2xl font-bold text-white">Trades</h1>
                    <p className="mt-1 text-slate-400">
                        Trading operations history
                    </p>
                </div>
                <button
                    onClick={loadTrades}
                    disabled={loading}
                    className="btn btn-secondary"
                >
                    <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
                    Refresh
                </button>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-blue-500/10">
                            <BarChart3 className="w-5 h-5 text-blue-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{totalTrades}</p>
                            <p className="text-sm text-slate-400">Total Trades</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-emerald-500/10">
                            <TrendingUp className="w-5 h-5 text-emerald-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{openTrades}</p>
                            <p className="text-sm text-slate-400">Open</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-slate-500/10">
                            <TrendingDown className="w-5 h-5 text-slate-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{closedTrades}</p>
                            <p className="text-sm text-slate-400">Closed</p>
                        </div>
                    </div>
                </div>
                <div className="card">
                    <div className="flex items-center gap-3">
                        <div className="p-2.5 rounded-lg bg-green-500/10">
                            <ArrowUpRight className="w-5 h-5 text-green-400" />
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-white">{buyTrades}</p>
                            <p className="text-sm text-slate-400">Buy Orders</p>
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
                                placeholder="Search by ID or account..."
                                className="input !pl-12"
                            />
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => {
                                setStatusFilter('all');
                                setCurrentPage(1);
                            }}
                            className={`btn ${statusFilter === 'all' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            All
                        </button>
                        <button
                            onClick={() => {
                                setStatusFilter('OPEN');
                                setCurrentPage(1);
                            }}
                            className={`btn ${statusFilter === 'OPEN' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            Open
                        </button>
                        <button
                            onClick={() => {
                                setStatusFilter('CLOSED');
                                setCurrentPage(1);
                            }}
                            className={`btn ${statusFilter === 'CLOSED' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            Closed
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
                        onClick={loadTrades}
                        className="ml-auto text-sm underline hover:no-underline"
                    >
                        Retry
                    </button>
                </div>
            )}

            {/* Table */}
            {filteredTrades.length === 0 && !error ? (
                <div className="card text-center py-12">
                    <BarChart3 className="w-12 h-12 text-slate-600 mx-auto mb-4" />
                    <h3 className="text-lg font-medium text-white mb-2">No trades found</h3>
                    <p className="text-slate-400">
                        {searchTerm || statusFilter !== 'all' || accountFilter
                            ? 'No trades found with the applied filters.'
                            : 'No trading operations recorded yet.'}
                    </p>
                </div>
            ) : (
                <>
                    <div className="table-container">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Volume</th>
                                    <th>Open Price</th>
                                    <th>Close Price</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredTrades.map((trade) => (
                                    <tr key={trade.id}>
                                        <td>
                                            <span className="font-medium text-white">#{trade.id}</span>
                                        </td>
                                        <td>
                                            <span className="text-slate-300">#{trade.account_id}</span>
                                        </td>
                                        <td>
                                            <span className={`badge ${trade.type === 'BUY' ? 'badge-success' : 'badge-danger'}`}>
                                                {trade.type === 'BUY' ? (
                                                    <><ArrowUpRight className="w-3 h-3 mr-1" /> BUY</>
                                                ) : (
                                                    <><ArrowDownRight className="w-3 h-3 mr-1" /> SELL</>
                                                )}
                                            </span>
                                        </td>
                                        <td>
                                            <span className="text-slate-300">{trade.volume}</span>
                                        </td>
                                        <td>
                                            <span className="font-mono text-slate-300">{formatPrice(trade.open_price)}</span>
                                        </td>
                                        <td>
                                            <span className="font-mono text-slate-300">{formatPrice(trade.close_price)}</span>
                                        </td>
                                        <td>
                                            <div className="flex items-center gap-1 text-slate-400">
                                                <Clock className="w-3 h-3" />
                                                {calculateDuration(trade.open_time, trade.close_time)}
                                            </div>
                                        </td>
                                        <td>
                                            <span className={`badge ${trade.status === 'OPEN' ? 'badge-success' : 'badge-info'}`}>
                                                {trade.status === 'OPEN' ? 'OPEN' : 'CLOSED'}
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                onClick={() => setSelectedTrade(trade)}
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
                                Page {currentPage} of {totalPages} ({totalTrades} trades)
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

            {/* Trade Detail Modal */}
            {selectedTrade && (
                <div className="modal-overlay" onClick={() => setSelectedTrade(null)}>
                    <div className="modal-content max-w-lg" onClick={e => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-xl font-bold text-white">Trade Details #{selectedTrade.id}</h2>
                            <button
                                onClick={() => setSelectedTrade(null)}
                                className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="space-y-4">
                            {/* Type & Status */}
                            <div className="flex gap-2">
                                <span className={`badge ${selectedTrade.type === 'BUY' ? 'badge-success' : 'badge-danger'}`}>
                                    {selectedTrade.type}
                                </span>
                                <span className={`badge ${selectedTrade.status === 'OPEN' ? 'badge-success' : 'badge-info'}`}>
                                    {selectedTrade.status}
                                </span>
                            </div>

                            {/* Details Grid */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="p-3 bg-slate-800/50 rounded-lg">
                                    <label className="text-xs text-slate-500 uppercase">Account</label>
                                    <p className="text-white font-medium">#{selectedTrade.account_id}</p>
                                </div>
                                <div className="p-3 bg-slate-800/50 rounded-lg">
                                    <label className="text-xs text-slate-500 uppercase">Volume</label>
                                    <p className="text-white font-medium">{selectedTrade.volume} lots</p>
                                </div>
                            </div>

                            {/* Prices */}
                            <div className="p-3 bg-slate-800/50 rounded-lg">
                                <div className="flex items-center gap-2 mb-2">
                                    <DollarSign className="w-4 h-4 text-emerald-400" />
                                    <label className="text-xs text-slate-500 uppercase">Prices</label>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <span className="text-slate-400 text-sm">Open:</span>
                                        <p className="text-white font-mono">{formatPrice(selectedTrade.open_price)}</p>
                                    </div>
                                    <div>
                                        <span className="text-slate-400 text-sm">Close:</span>
                                        <p className="text-white font-mono">{formatPrice(selectedTrade.close_price)}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Times */}
                            <div className="p-3 bg-slate-800/50 rounded-lg">
                                <div className="flex items-center gap-2 mb-2">
                                    <Clock className="w-4 h-4 text-blue-400" />
                                    <label className="text-xs text-slate-500 uppercase">Times</label>
                                </div>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-slate-400">Open:</span>
                                        <span className="text-white">{formatDateTime(selectedTrade.open_time)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-slate-400">Close:</span>
                                        <span className="text-white">{formatDateTime(selectedTrade.close_time)}</span>
                                    </div>
                                    <div className="flex justify-between border-t border-slate-700 pt-2">
                                        <span className="text-slate-400">Duration:</span>
                                        <span className="text-indigo-400 font-medium">
                                            {calculateDuration(selectedTrade.open_time, selectedTrade.close_time)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-6 flex justify-end">
                            <button
                                onClick={() => setSelectedTrade(null)}
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
