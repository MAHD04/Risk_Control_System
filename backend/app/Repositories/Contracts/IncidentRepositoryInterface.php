<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Incident Repository.
 * Abstracts complex incident queries for better testability.
 */
interface IncidentRepositoryInterface
{
    /**
     * Count incidents for a rule and account within a time window.
     *
     * @param int $ruleId
     * @param int $accountId
     * @param int $windowDays Days to look back
     * @return int
     */
    public function countRecentByRuleAndAccount(int $ruleId, int $accountId, int $windowDays = 30): int;

    /**
     * Get incidents by account with unread status.
     *
     * @param int $accountId
     * @return Collection
     */
    public function getUnreadByAccount(int $accountId): Collection;

    /**
     * Get incident statistics grouped by rule.
     *
     * @param int $accountId
     * @return array
     */
    public function getStatsByAccount(int $accountId): array;
}
