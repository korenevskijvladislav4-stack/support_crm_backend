<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: file_exists(__DIR__.'/../routes/web.php') ? __DIR__.'/../routes/web.php' : null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Добавляем CORS middleware для всех API запросов
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Добавляем CORS middleware для web запросов (если нужно)
        $middleware->web(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Обработка исключений для API маршрутов - всегда возвращаем JSON
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
        
        // Форматирование ответа для API
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                // Безопасное получение сообщения об ошибке с проверкой UTF-8
                $getSafeMessage = function($message) {
                    if (empty($message)) {
                        return 'Internal Server Error';
                    }
                    // Проверяем и очищаем от некорректных UTF-8 символов
                    $cleaned = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
                    if ($cleaned === false || !mb_check_encoding($cleaned, 'UTF-8')) {
                        return 'An error occurred';
                    }
                    return $cleaned;
                };
                
                $message = $getSafeMessage($e->getMessage());
                
                // Для ошибок валидации
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], 422, [], JSON_UNESCAPED_UNICODE);
                }
                
                // Для ошибок БД
                if ($e instanceof \PDOException || $e instanceof \Illuminate\Database\QueryException) {
                    \Log::error('Database error: ' . $e->getMessage());
                    $errorMessage = config('app.debug') ? $getSafeMessage($e->getMessage()) : 'Unable to connect to database';
                    return response()->json([
                        'message' => 'Database connection error',
                        'error' => $errorMessage,
                    ], 500, [], JSON_UNESCAPED_UNICODE);
                }
                
                $errorMessage = config('app.debug') ? $getSafeMessage($e->getMessage()) : 'An error occurred';
                return response()->json([
                    'message' => $message,
                    'error' => $errorMessage,
                ], $status, [], JSON_UNESCAPED_UNICODE);
            }
        });
    })->create();
