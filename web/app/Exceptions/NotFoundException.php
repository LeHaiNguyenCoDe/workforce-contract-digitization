<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotFoundException extends Exception
{
    protected $message = 'Resource not found';
    protected $code = 404;

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

