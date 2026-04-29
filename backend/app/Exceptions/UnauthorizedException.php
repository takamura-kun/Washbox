<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct($message = "You are not authorized to perform this action")
    {
        parent::__construct($message, 403, "UNAUTHORIZED");
    }
}
