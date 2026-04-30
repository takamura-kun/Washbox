<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $statusCode;
    protected $errorCode;
    protected $details;

    public function __construct($message = "", $statusCode = 500, $errorCode = "INTERNAL_ERROR", $details = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->details = $details;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
            'details' => config('app.debug') ? $this->details : [],
        ], $this->statusCode);
    }
}
