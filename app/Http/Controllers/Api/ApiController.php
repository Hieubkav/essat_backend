<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base API Controller
 * 
 * Provides common response methods for all API controllers
 */
class ApiController extends Controller
{
    /**
     * Success response
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Created response
     */
    protected function created(
        mixed $data = null,
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * No content response
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Error response
     */
    protected function error(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Not found response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Validation error response
     */
    protected function validationError(array $errors = []): JsonResponse
    {
        return $this->error('Validation failed', 422, $errors);
    }
}
