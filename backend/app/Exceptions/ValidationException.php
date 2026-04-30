<?php

namespace App\Exceptions;

class ValidationException extends ApiException
{
    protected $errors;

    public function __construct($message = "Validation failed", $errors = [])
    {
        parent::__construct($message, 422, "VALIDATION_ERROR", $errors);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->statusCode);
    }
}
