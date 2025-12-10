<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ConfiguredAction;
use App\Models\Incident;
use App\Models\RiskRule;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for executing risk actions when rules are violated.
 */
class ActionExecutionService
{
    /**
     * Map of action_type identifiers to their handler methods.
     */
    protected array $actionHandlers = [
        'NOTIFY_EMAIL' => 'handleNotifyEmail',
        'NOTIFY_SLACK' => 'handleNotifySlack',
        'DISABLE_ACCOUNT' => 'handleDisableAccount',
        'DISABLE_TRADING' => 'handleDisableTrading',
    ];

    /**
     * Execute all actions associated with a rule for a given incident.
     */
    public function executeActionsForRule(RiskRule $rule, Incident $incident, Account $account): void
    {
        $actions = $rule->actions;

        if ($actions->isEmpty()) {
            Log::debug("No actions configured for rule: {$rule->name}");
            return;
        }

        foreach ($actions as $action) {
            $this->executeAction($action, $incident, $account);
        }
    }

    /**
     * Execute a single action with error handling.
     */
    protected function executeAction(ConfiguredAction $action, Incident $incident, Account $account): void
    {
        $handler = $this->actionHandlers[$action->action_type] ?? null;

        if (!$handler || !method_exists($this, $handler)) {
            Log::warning("Unknown action type: {$action->action_type}");
            return;
        }

        try {
            Log::debug("Executing action", [
                'action_id' => $action->id,
                'action_type' => $action->action_type,
                'account_id' => $account->id,
                'incident_id' => $incident->id,
            ]);

            $this->$handler($action, $incident, $account);
        } catch (\Exception $e) {
            Log::error("Action execution failed", [
                'action_id' => $action->id,
                'action_type' => $action->action_type,
                'account_id' => $account->id,
                'incident_id' => $incident->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Real: Notify via Email.
     */
    protected function handleNotifyEmail(ConfiguredAction $action, Incident $incident, Account $account): void
    {
        $recipient = $action->getConfig('recipient', 'admin@example.com');
        $subject = $action->getConfig('subject', 'Risk Rule Violation Alert');
        $body = "Account #{$account->login} violated rule. Incident ID: {$incident->id}";

        try {
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject($subject);
            });

            Log::info("[EMAIL SENT] Notification sent", ['to' => $recipient]);
        } catch (\Exception $e) {
            Log::error("Failed to send email", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Real: Notify via Slack (or generic Webhook).
     */
    protected function handleNotifySlack(ConfiguredAction $action, Incident $incident, Account $account): void
    {
        $webhookUrl = $action->getConfig('webhook_url');

        if (!$webhookUrl) {
            Log::warning("Slack action configured but missing webhook_url", ['action_id' => $action->id]);
            return;
        }

        $message = "⚠️ *Risk Alert*: Account #{$account->login} violated a rule.\nIncident ID: {$incident->id}";

        try {
            \Illuminate\Support\Facades\Http::post($webhookUrl, [
                'text' => $message,
            ]);

            Log::info("[SLACK SENT] Notification sent to webhook");
        } catch (\Exception $e) {
            Log::error("Failed to send slack notification", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Real: Disable the entire account (user cannot access system).
     */
    protected function handleDisableAccount(ConfiguredAction $action, Incident $incident, Account $account): void
    {
        $account->disableAccount();

        Log::channel('stack')->info("[ACTION EXECUTED] Account disabled", [
            'account_id' => $account->id,
            'account_login' => $account->login,
            'new_status' => 'disable',
        ]);
    }

    /**
     * Real: Disable trading for the account (user can access but not trade).
     */
    protected function handleDisableTrading(ConfiguredAction $action, Incident $incident, Account $account): void
    {
        $account->disableTrading();

        Log::channel('stack')->info("[ACTION EXECUTED] Trading disabled", [
            'account_id' => $account->id,
            'account_login' => $account->login,
            'new_trading_status' => 'disable',
        ]);
    }
}
