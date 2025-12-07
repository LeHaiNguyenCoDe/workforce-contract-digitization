<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidationException extends Exception
{
    protected $code = 422;

    /**
     * Create a new validation exception instance.
     *
     * @param  string  $message
     * @param  mixed  $errors
     */
    public function __construct(string $message = 'Validation failed', protected $errors = null)
    {
        parent::__construct($message);
    }

    /**
     * Get validation errors
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->getCode());
    }
}

