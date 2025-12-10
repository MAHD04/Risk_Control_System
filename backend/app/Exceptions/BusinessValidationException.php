<?php

namespace App\Exceptions;

/**
 * Exception for business logic validation errors.
 */
class BusinessValidationException extends ApiException
{
    public function __construct(
        string $message,
        array $violations = []
    ) {
        parent::__construct(
            message: $message,
            errorCode: 'BUSINESS_VALIDATION_ERROR',
            httpStatusCode: 422,
            details: ['violations' => $violations]
        );
    }
}
