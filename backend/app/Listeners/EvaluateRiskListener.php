<?php

namespace App\Listeners;

use App\Events\TradeSaved;
use App\Services\RiskEvaluationService;
use Illuminate\Support\Facades\Log;

/**
 * Listener that evaluates risk rules when a trade is saved.
 */
class EvaluateRiskListener
{
    protected RiskEvaluationService $riskService;

    /**
     * Create the event listener.
     */
    public function __construct(RiskEvaluationService $riskService)
    {
        $this->riskService = $riskService;
    }

    /**
     * Handle the event.
     */
    public function handle(TradeSaved $event): void
    {
        $trade = $event->trade;

        Log::info("EvaluateRiskListener triggered", [
            'trade_id' => $trade->id,
            'account_id' => $trade->account_id,
            'status' => $trade->status,
        ]);

        // Only evaluate closed trades (as per the requirements)
        if (!$trade->isClosed()) {
            Log::info("Trade is not closed, skipping risk evaluation", [
                'trade_id' => $trade->id,
            ]);
            return;
        }

        // Evaluate the trade against all active risk rules
        $incidents = $this->riskService->evaluate($trade);

        Log::info("Risk evaluation completed", [
            'trade_id' => $trade->id,
            'incidents_created' => count($incidents),
        ]);
    }
}
