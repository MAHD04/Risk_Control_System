<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        // Active rules count
        $activeRules = RiskRule::where('is_active', true)->count();

        // Total incidents count
        $totalIncidents = Incident::count();

        // Incidents today
        $incidentsToday = Incident::whereDate('triggered_at', Carbon::today())->count();

        // Active accounts (status = 'enable')
        $activeAccounts = Account::where('status', 'enable')->count();

        // Disabled accounts (status = 'disable')
        $disabledAccounts = Account::where('status', 'disable')->count();

        // Open trades
        $openTrades = Trade::where('status', 'OPEN')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'active_rules' => $activeRules,
                'total_incidents' => $totalIncidents,
                'incidents_today' => $incidentsToday,
                'active_accounts' => $activeAccounts,
                'disabled_accounts' => $disabledAccounts,
                'open_trades' => $openTrades,
            ],
        ]);
    }

    /**
     * Get incident activity data for chart (grouped by hour).
     */
    public function incidentActivity(): JsonResponse
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Get incidents grouped by hour for today
        // Using strftime for SQLite compatibility (works with both SQLite and MySQL)
        $driver = DB::connection()->getDriverName();
        $hourExpression = $driver === 'sqlite' 
            ? "cast(strftime('%H', incidents.triggered_at) as integer)" 
            : 'HOUR(incidents.triggered_at)';
        
        $incidentsByHour = Incident::whereBetween('triggered_at', [$today, $tomorrow])
            ->join('risk_rules', 'incidents.risk_rule_id', '=', 'risk_rules.id')
            ->select(
                DB::raw("$hourExpression as hour"),
                DB::raw('COUNT(*) as incidents'),
                DB::raw('SUM(CASE WHEN risk_rules.severity = "HARD" THEN 1 ELSE 0 END) as severity')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Create data points for all 24 hours (or specific hours if needed)
        $data = [];
        $hourLabels = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '23:59'];
        $hourMap = [0, 4, 8, 12, 16, 20, 23];

        foreach ($hourMap as $index => $hour) {
            $incidentData = $incidentsByHour->firstWhere('hour', $hour);
            $data[] = [
                'name' => $hourLabels[$index],
                'incidents' => $incidentData ? (int)$incidentData->incidents : 0,
                'severity' => $incidentData ? (int)$incidentData->severity : 0,
            ];
        }

        // Calculate percentage change (comparing today with yesterday)
        $incidentsToday = Incident::whereBetween('triggered_at', [$today, $tomorrow])->count();
        $yesterday = Carbon::yesterday();
        $incidentsYesterday = Incident::whereBetween('triggered_at', [$yesterday, $today])->count();
        $percentageChange = $incidentsYesterday > 0 
            ? (($incidentsToday - $incidentsYesterday) / $incidentsYesterday) * 100 
            : ($incidentsToday > 0 ? 100 : 0);

        return response()->json([
            'success' => true,
            'data' => [
                'chart_data' => $data,
                'percentage_change' => round($percentageChange, 1),
            ],
        ]);
    }

    /**
     * Get recent incidents for dashboard.
     */
    public function recentIncidents(): JsonResponse
    {
        $recentIncidents = Incident::with(['riskRule', 'account'])
            ->orderBy('triggered_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'rule' => $incident->riskRule ? $incident->riskRule->name : 'Unknown Rule',
                    'account' => $incident->account ? (string)$incident->account->login : 'Unknown',
                    'severity' => $incident->riskRule ? $incident->riskRule->severity : 'SOFT',
                    'time' => $incident->triggered_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recentIncidents,
        ]);
    }
    /**
     * Get system status.
     */
    public function systemStatus(): JsonResponse
    {
        // API Connection is implicitly true if we are here
        
        // Rule Engine Status (Check if there are active rules)
        $activeRulesCount = RiskRule::where('is_active', true)->count();
        $ruleEngineStatus = $activeRulesCount > 0 ? 'Active' : 'Inactive';

        // Event Listener Status (Check database connection as proxy)
        try {
            DB::connection()->getPdo();
            $eventListenerStatus = 'Running';
        } catch (\Exception $e) {
            $eventListenerStatus = 'Stopped';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'api_connection' => 'Connected',
                'rule_engine' => $ruleEngineStatus,
                'event_listener' => $eventListenerStatus,
                'last_sync' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }
}

