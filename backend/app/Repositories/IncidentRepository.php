<?php

namespace App\Repositories;

use App\Models\Incident;
use App\Models\RiskRule;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent implementation of Incident Repository.
 */
class IncidentRepository implements IncidentRepositoryInterface
{
    /**
     * Count incidents for a rule and account within a time window.
     */
    public function countRecentByRuleAndAccount(int $ruleId, int $accountId, int $windowDays = 30): int
    {
        return Incident::where('risk_rule_id', $ruleId)
            ->where('account_id', $accountId)
            ->where('triggered_at', '>=', now()->subDays($windowDays))
            ->count();
    }

    /**
     * Get incidents by account with unread status.
     */
    public function getUnreadByAccount(int $accountId): Collection
    {
        return Incident::where('account_id', $accountId)
            ->where('is_read', false)
            ->with(['riskRule', 'trade'])
            ->orderBy('triggered_at', 'desc')
            ->get();
    }

    /**
     * Get incident statistics grouped by rule.
     */
    public function getStatsByAccount(int $accountId): array
    {
        $incidentsByRule = Incident::where('account_id', $accountId)
            ->select('risk_rule_id', DB::raw('count(*) as count'))
            ->groupBy('risk_rule_id')
            ->get();

        $incidentsBySeverity = Incident::where('account_id', $accountId)
            ->join('risk_rules', 'incidents.risk_rule_id', '=', 'risk_rules.id')
            ->select('risk_rules.severity', DB::raw('count(*) as count'))
            ->groupBy('risk_rules.severity')
            ->get()
            ->pluck('count', 'severity')
            ->toArray();

        $ruleStats = [];
        foreach ($incidentsByRule as $item) {
            $rule = RiskRule::find($item->risk_rule_id);
            if ($rule) {
                $ruleStats[$rule->name] = $item->count;
            }
        }

        return [
            'account_id' => $accountId,
            'total_incidents' => array_sum($ruleStats),
            'incidents_by_severity' => [
                'SOFT' => $incidentsBySeverity['SOFT'] ?? 0,
                'HARD' => $incidentsBySeverity['HARD'] ?? 0,
            ],
            'incidents_by_rule' => $ruleStats,
        ];
    }
}
