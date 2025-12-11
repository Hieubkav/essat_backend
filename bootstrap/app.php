<?php

use App\Exceptions\Handler as AppExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware (CORS)
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // Middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // Chỉ disable redirect cho API requests, giữ nguyên cho web (Filament)
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('api/*')) {
                return null;
            }
            return route('filament.admin.auth.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Luôn trả JSON cho API (tránh redirect tới route 'login')
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->is('api/*');
        });

        // Xử lý AuthenticationException cho API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'errors' => null,
                ], 401);
            }

            return null; // dùng mặc định cho request khác
        });
    })
    ->withSingletons([
        ExceptionHandler::class => AppExceptionHandler::class,
    ])
    ->create();
