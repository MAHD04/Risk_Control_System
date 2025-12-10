<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\RiskEvaluationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorRiskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously monitor active accounts for risk rule violations';

    protected RiskEvaluationService $riskService;

    public function __construct(RiskEvaluationService $riskService)
    {
        parent::__construct();
        $this->riskService = $riskService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting risk monitoring...');

        // Fetch active accounts (or accounts with open positions)
        // For efficiency, we might only want accounts with OPEN trades or specific status
        $accounts = Account::where('status', 'active')->get();

        $this->info("Found {$accounts->count()} active accounts.");

        foreach ($accounts as $account) {
            try {
                $this->info("Checking account: {$account->login}");
                
                $incidents = $this->riskService->evaluateAccount($account);

                if (!empty($incidents)) {
                    $this->error("Risk violations found for account {$account->login}: " . count($incidents));
                }
            } catch (\Exception $e) {
                $this->error("Error checking account {$account->login}: " . $e->getMessage());
                Log::error("Risk monitor error", ['error' => $e->getMessage(), 'account_id' => $account->id]);
            }
        }

        $this->info('Risk monitoring cycle completed.');
    }
}
