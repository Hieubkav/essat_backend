<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * The inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Convert authentication exception to JSON for API, redirect to Filament login for admin.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ], 401);
        }

        // Redirect to Filament admin login if accessing admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        return redirect()->guest('/');
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            return $this->handleApiException($e);
        });
    }

    /**
     * Handle API exceptions with consistent JSON format.
     */
    protected function handleApiException(Throwable $e)
    {
        // Laravel's built-in AuthenticationException (from auth middleware)
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ], 401);
        }

        // Custom AuthenticationException (login failed, wrong password, etc.)
        if ($e instanceof \App\Exceptions\AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getCode() ?: 401);
        }

        // Custom ApiException
        if ($e instanceof ApiException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getData(),
            ], $e->getCode() ?: 400);
        }

        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Model not found (e.g., findOrFail)
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return response()->json([
                'success' => false,
                'message' => "{$model} not found",
                'errors' => null,
            ], 404);
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'errors' => null,
            ], 404);
        }

        // Other HTTP exceptions
        if ($e instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'HTTP Error',
                'errors' => null,
            ], $e->getStatusCode());
        }

        // Generic exceptions - hide details in production
        $message = config('app.debug') ? $e->getMessage() : 'Internal server error';

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => config('app.debug') ? [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] : null,
        ], 500);
    }
}
