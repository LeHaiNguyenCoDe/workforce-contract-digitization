<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationException extends Exception
{
    protected $message = 'Authentication failed';
    protected $code = 401;

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
        ], $this->getCode());
    }
}

