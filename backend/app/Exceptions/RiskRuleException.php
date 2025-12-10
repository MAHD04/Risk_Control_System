<?php

namespace App\Exceptions;

/**
 * Exception for risk rule execution errors.
 */
class RiskRuleException extends ApiException
{
    public function __construct(
        string $message,
        string $ruleType,
        ?int $ruleId = null
    ) {
        parent::__construct(
            message: $message,
            errorCode: 'RISK_RULE_ERROR',
            httpStatusCode: 500,
            details: [
                'rule_type' => $ruleType,
                'rule_id' => $ruleId,
            ]
        );
    }
}
