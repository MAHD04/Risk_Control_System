<?php

namespace App\Services;

use App\Contracts\RuleStrategy;
use App\Models\Incident;
use App\Models\RiskRule;
use App\Models\Trade;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for evaluating trades against all active risk rules.
 */
class RiskEvaluationService
{
    protected ActionExecutionService $actionService;

    /**
     * Map of rule_type identifiers to their strategy classes.
     */
    protected array $ruleStrategies = [
        'min_duration' => \App\Rules\MinDurationRule::class,
        'volume_consistency' => \App\Rules\VolumeConsistencyRule::class,
        'trade_frequency' => \App\Rules\TradeFrequencyRule::class,
        'daily_loss_limit' => \App\Rules\DailyLossLimitRule::class,
        'max_open_positions' => \App\Rules\MaxOpenPositionsRule::class,
        'max_drawdown' => \App\Rules\MaxDrawdownRule::class,
        'risk_per_trade' => \App\Rules\RiskPerTradeRule::class,
    ];

    public function __construct(ActionExecutionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Evaluate a trade against all active risk rules.
     *
     * @param Trade $trade The trade to evaluate.
     * @return array List of incidents created (if any).
     */
    public function evaluate(Trade $trade): array
    {
        $incidents = [];

        // Fetch all active rules
        $activeRules = RiskRule::where('is_active', true)->get();

        foreach ($activeRules as $rule) {
            $this->evaluateRule($rule, $trade, $incidents);
        }

        return $incidents;
    }

    /**
     * Evaluate a specific account against all active risk rules (Periodic Check).
     *
     * @param \App\Models\Account $account
     * @return array List of incidents created.
     */
    public function evaluateAccount(\App\Models\Account $account): array
    {
        $incidents = [];
        $activeRules = RiskRule::where('is_active', true)->get();

        foreach ($activeRules as $rule) {
            // Some rules might only make sense with a specific trade (like MinDuration),
            // but our check() method in strategies handles Account objects now.
            $this->evaluateRule($rule, $account, $incidents);
        }

        return $incidents;
    }

    /**
     * Helper to evaluate a single rule against an entity (Trade or Account).
     */
    protected function evaluateRule(RiskRule $rule, mixed $entity, array &$incidents): void
    {
        $strategyClass = $this->ruleStrategies[$rule->rule_type] ?? null;

        if (!$strategyClass) {
            // Log::warning("Unknown rule type: {$rule->rule_type}");
            return;
        }

        /** @var RuleStrategy $strategy */
        $strategy = new $strategyClass();

        // Check if the rule is violated
        // Strategies must now accept Trade OR Account
        $isViolated = $strategy->check($entity, $rule->parameters ?? []);

        if ($isViolated) {
            // If entity is Account, we might not have a specific trade_id for the incident
            $trade = ($entity instanceof Trade) ? $entity : null;
            
            // Avoid duplicate incidents for the same rule/account in a short window?
            // For now, we just create it.
            $incident = $this->createIncident($entity, $rule, $trade);
            $incidents[] = $incident;

            Log::info("Rule violated", [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'account_id' => $entity instanceof Trade ? $entity->account_id : $entity->id,
                'severity' => $rule->severity,
            ]);

            $this->processRuleSeverity($rule, $incident, $entity instanceof Trade ? $entity : $entity); // Pass Account if no Trade
        }
    }

    /**
     * Process rule based on its severity (HARD or SOFT).
     * Modified to accept Account or Trade.
     */
    protected function processRuleSeverity(RiskRule $rule, Incident $incident, mixed $entity): void
    {
        $account = ($entity instanceof Trade) ? $entity->account : $entity;

        if ($rule->isHard()) {
            // HARD rule: Execute action immediately
            Log::info("HARD rule triggered - executing actions immediately", [
                'rule_id' => $rule->id,
                'account_id' => $account->id,
            ]);

            $this->actionService->executeActionsForRule($rule, $incident, $account);
        } else {
            // SOFT rule: Check if incident limit has been reached
            $incidentCount = $this->countRecentIncidents($rule, $account->id);

            if ($incidentCount >= $rule->incident_limit) {
                Log::info("SOFT rule limit reached - executing actions", [
                    'rule_id' => $rule->id,
                    'account_id' => $account->id,
                ]);

                $this->actionService->executeActionsForRule($rule, $incident, $account);
            }
        }
    }

    /**
     * Count recent incidents for a specific rule and account.
     * Uses a configurable time window (default 30 days) from rule parameters.
     */
    protected function countRecentIncidents(RiskRule $rule, int $accountId): int
    {
        $windowDays = $rule->getParameter('incident_window_days', 30);

        return Incident::where('risk_rule_id', $rule->id)
            ->where('account_id', $accountId)
            ->where('triggered_at', '>=', now()->subDays($windowDays))
            ->count();
    }

    /**
     * Create an incident record for a rule violation.
     */
    protected function createIncident(mixed $entity, RiskRule $rule, ?Trade $trade = null): Incident
    {
        $accountId = ($entity instanceof Trade) ? $entity->account_id : $entity->id;
        
        return Incident::create([
            'trade_id' => $trade?->id, // Can be null for account-level violations
            'risk_rule_id' => $rule->id,
            'account_id' => $accountId,
            'triggered_at' => now(),
            'details' => [
                'rule_type' => $rule->rule_type,
                'rule_parameters' => $rule->parameters,
                'source' => $trade ? 'trade_event' : 'periodic_check',
            ],
        ]);
    }

    /**
     * Register a new rule strategy dynamically.
     */
    public function registerStrategy(string $type, string $strategyClass): void
    {
        $this->ruleStrategies[$type] = $strategyClass;
    }
}
