<?php

namespace App\Exceptions;

class ResourceNotFoundException extends ApiException
{
    public function __construct($resourceName = "Resource", $resourceId = null)
    {
        $message = $resourceId 
            ? "{$resourceName} with ID {$resourceId} not found"
            : "{$resourceName} not found";
            
        parent::__construct($message, 404, "NOT_FOUND");
    }
}
