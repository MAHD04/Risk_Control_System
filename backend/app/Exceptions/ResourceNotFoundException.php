<?php

namespace App\Exceptions;

/**
 * Exception for when a requested resource is not found.
 */
class ResourceNotFoundException extends ApiException
{
    public function __construct(
        string $resourceType,
        int|string $resourceId
    ) {
        parent::__construct(
            message: "{$resourceType} with ID {$resourceId} not found.",
            errorCode: 'RESOURCE_NOT_FOUND',
            httpStatusCode: 404,
            details: [
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
            ]
        );
    }
}
