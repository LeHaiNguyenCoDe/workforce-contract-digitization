<?php

namespace App\Traits;

use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;

trait TranslatableResponse
{
    /**
     * Return success response with translated message
     *
     * @param  string  $messageKey
     * @param  mixed  $data
     * @param  int  $statusCode
     * @return JsonResponse
     */
    protected function successResponse(string $messageKey, $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => LanguageHelper::apiMessage($messageKey),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response with translated message
     *
     * @param  string  $messageKey
     * @param  mixed  $errors
     * @param  int  $statusCode
     * @param  \Throwable|null  $exception
     * @return JsonResponse
     */
    protected function errorResponse(string $messageKey, $errors = null, int $statusCode = 400, ?\Throwable $exception = null): JsonResponse
    {
        // Log exception to appropriate error channel if provided and status code >= 500
        if ($exception && $statusCode >= 500) {
            $channel = \App\Logging\ErrorChannelResolver::getChannel($exception);
            \Log::channel($channel)->error($exception->getMessage(), [
                'exception' => $exception,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        $response = [
            'status' => 'error',
            'message' => LanguageHelper::apiMessage($messageKey),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return generic error response
     *
     * @param  int  $statusCode
     * @param  \Throwable|null  $exception
     * @return JsonResponse
     */
    protected function genericErrorResponse(int $statusCode = 500, ?\Throwable $exception = null): JsonResponse
    {
        // Log exception to appropriate error channel if provided
        if ($exception) {
            $channel = \App\Logging\ErrorChannelResolver::getChannel($exception);
            \Log::channel($channel)->error($exception->getMessage(), [
                'exception' => $exception,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        return $this->errorResponse('error', null, $statusCode);
    }
}

