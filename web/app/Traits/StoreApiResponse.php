<?php

namespace App\Traits;

use App\Helpers\LanguageHelper;
use App\Logging\ErrorChannelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Trait StoreApiResponse
 * 
 * Provides standardized API response methods for Store controllers
 * with multi-language support using LanguageHelper.
 */
trait StoreApiResponse
{
    /**
     * Translate message using LanguageHelper
     * If message is a translation key, it will be translated.
     * Otherwise, return the raw message.
     *
     * @param string|null $message
     * @param array $replace
     * @return string|null
     */
    protected function translateMessage(?string $message, array $replace = []): ?string
    {
        if (empty($message)) {
            return null;
        }

        // Try to translate using LanguageHelper
        $translated = LanguageHelper::apiMessage($message, $replace);
        
        // If translation returns the key itself or is empty, return original message
        if (empty($translated) || $translated === $message) {
            return $message;
        }

        return $translated;
    }

    /**
     * Return a successful response
     *
     * @param mixed $data
     * @param string|null $messageKey Translation key or raw message
     * @param int $statusCode
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        ?string $messageKey = null,
        int $statusCode = 200,
        array $replace = []
    ): JsonResponse {
        $response = [
            'status' => 'success',
        ];

        if ($messageKey !== null) {
            $response['message'] = $this->translateMessage($messageKey, $replace);
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a created response (HTTP 201)
     *
     * @param mixed $data
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function createdResponse(
        mixed $data = null,
        ?string $messageKey = 'created',
        array $replace = []
    ): JsonResponse {
        return $this->successResponse($data, $messageKey, 201, $replace);
    }

    /**
     * Return an updated response
     *
     * @param mixed $data
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function updatedResponse(
        mixed $data = null,
        ?string $messageKey = 'updated',
        array $replace = []
    ): JsonResponse {
        return $this->successResponse($data, $messageKey, 200, $replace);
    }

    /**
     * Return a deleted response
     *
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function deletedResponse(
        ?string $messageKey = 'deleted',
        array $replace = []
    ): JsonResponse {
        return $this->successResponse(null, $messageKey, 200, $replace);
    }

    /**
     * Return an error response
     *
     * @param string $messageKey Translation key or raw message
     * @param mixed $errors
     * @param int $statusCode
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function errorResponse(
        string $messageKey,
        mixed $errors = null,
        int $statusCode = 400,
        array $replace = []
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $this->translateMessage($messageKey, $replace),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a not found response (HTTP 404)
     *
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function notFoundResponse(
        ?string $messageKey = 'not_found',
        array $replace = []
    ): JsonResponse {
        return $this->errorResponse($messageKey ?? 'not_found', null, 404, $replace);
    }

    /**
     * Return a validation error response (HTTP 422)
     *
     * @param mixed $errors
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        mixed $errors,
        ?string $messageKey = 'validation_failed',
        array $replace = []
    ): JsonResponse {
        return $this->errorResponse($messageKey ?? 'validation_failed', $errors, 422, $replace);
    }

    /**
     * Return an unauthorized response (HTTP 401)
     *
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function unauthorizedResponse(
        ?string $messageKey = 'unauthorized',
        array $replace = []
    ): JsonResponse {
        return $this->errorResponse($messageKey ?? 'unauthorized', null, 401, $replace);
    }

    /**
     * Return a forbidden response (HTTP 403)
     *
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function forbiddenResponse(
        ?string $messageKey = 'forbidden',
        array $replace = []
    ): JsonResponse {
        return $this->errorResponse($messageKey ?? 'forbidden', null, 403, $replace);
    }

    /**
     * Return server error response (HTTP 500)
     *
     * @param string|null $messageKey Translation key or raw message
     * @param \Throwable|null $exception
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function serverErrorResponse(
        ?string $messageKey = 'error',
        ?\Throwable $exception = null,
        array $replace = []
    ): JsonResponse {
        // Handle ModelNotFoundException (Route Model Binding failures) as 404
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse();
        }

        // Handle custom NotFoundException as 404
        if ($exception instanceof \App\Exceptions\NotFoundException) {
            return $this->notFoundResponse();
        }

        // Log exception to appropriate error channel if provided
        if ($exception) {
            try {
                $channel = ErrorChannelResolver::getChannel($exception);
                Log::channel($channel)->error($exception->getMessage(), [
                    'exception' => $exception,
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            } catch (\Throwable $e) {
                // Fallback to default logging if ErrorChannelResolver fails
                Log::error($exception->getMessage(), [
                    'exception' => $exception,
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }
        }

        return $this->errorResponse($messageKey ?? 'error', null, 500, $replace);
    }

    /**
     * Return a generic error response
     *
     * @param int $statusCode
     * @param \Throwable|null $exception
     * @return JsonResponse
     */
    protected function genericErrorResponse(
        int $statusCode = 500,
        ?\Throwable $exception = null
    ): JsonResponse {
        return $this->serverErrorResponse('error', $exception);
    }

    /**
     * Return a paginated response
     *
     * @param LengthAwarePaginator $paginator
     * @param string|null $messageKey Translation key or raw message
     * @param array $replace Replacement parameters for translation
     * @return JsonResponse
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        ?string $messageKey = null,
        array $replace = []
    ): JsonResponse {
        $response = [
            'status' => 'success',
            'data' => [
                'items' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ],
            ],
        ];

        if ($messageKey !== null) {
            $response['message'] = $this->translateMessage($messageKey, $replace);
        }

        return response()->json($response);
    }
}
