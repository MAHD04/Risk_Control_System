<?php

namespace App\Repositories\Contracts;

use App\Models\Trade;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Trade Repository.
 * Abstracts complex trade queries for better testability.
 */
interface TradeRepositoryInterface
{
    /**
     * Find trades by account with optional filters.
     *
     * @param int $accountId
     * @param array $filters ['status' => 'OPEN|CLOSED', 'limit' => int]
     * @return Collection<Trade>
     */
    public function findByAccount(int $accountId, array $filters = []): Collection;

    /**
     * Get recent closed trades for an account.
     *
     * @param int $accountId
     * @param int $limit
     * @return Collection<Trade>
     */
    public function getRecentClosedByAccount(int $accountId, int $limit = 10): Collection;

    /**
     * Count open trades for an account within a time window.
     *
     * @param int $accountId
     * @param int $windowMinutes Minutes to look back
     * @return int
     */
    public function countRecentOpenTrades(int $accountId, int $windowMinutes = 60): int;

    /**
     * Calculate average volume for recent trades.
     *
     * @param int $accountId
     * @param int $tradeCount Number of trades to consider
     * @return float
     */
    public function getAverageVolume(int $accountId, int $tradeCount = 10): float;
}
