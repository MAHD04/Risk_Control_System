<?php

namespace App\Repositories;

use App\Models\Trade;
use App\Repositories\Contracts\TradeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of Trade Repository.
 */
class TradeRepository implements TradeRepositoryInterface
{
    /**
     * Find trades by account with optional filters.
     */
    public function findByAccount(int $accountId, array $filters = []): Collection
    {
        $query = Trade::where('account_id', $accountId);

        if (isset($filters['status'])) {
            $query->where('status', strtoupper($filters['status']));
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get recent closed trades for an account.
     */
    public function getRecentClosedByAccount(int $accountId, int $limit = 10): Collection
    {
        return Trade::where('account_id', $accountId)
            ->where('status', 'CLOSED')
            ->orderBy('close_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Count open trades for an account within a time window.
     */
    public function countRecentOpenTrades(int $accountId, int $windowMinutes = 60): int
    {
        return Trade::where('account_id', $accountId)
            ->where('status', 'OPEN')
            ->where('open_time', '>=', now()->subMinutes($windowMinutes))
            ->count();
    }

    /**
     * Calculate average volume for recent trades.
     */
    public function getAverageVolume(int $accountId, int $tradeCount = 10): float
    {
        $trades = $this->getRecentClosedByAccount($accountId, $tradeCount);

        if ($trades->isEmpty()) {
            return 0.0;
        }

        return (float) $trades->avg('volume');
    }
}
