<?php

namespace App\Exceptions;

use Exception;

/**
 * Base API Exception for Risk Control System.
 * Provides consistent error responses across the API.
 */
class ApiException extends Exception
{
    protected string $errorCode;
    protected array $details;

    public function __construct(
        string $message,
        string $errorCode = 'GENERAL_ERROR',
        int $httpStatusCode = 400,
        array $details = []
    ) {
        parent::__construct($message, $httpStatusCode);
        $this->errorCode = $errorCode;
        $this->details = $details;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getHttpStatusCode(): int
    {
        return $this->getCode();
    }

    /**
     * Convert exception to API response array.
     */
    public function toArray(): array
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
            ],
        ];

        if (!empty($this->details)) {
            $response['error']['details'] = $this->details;
        }

        return $response;
    }
}
