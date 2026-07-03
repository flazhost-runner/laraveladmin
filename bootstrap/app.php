<?php

use App\Exceptions\AppException;
use App\Exceptions\NotFoundAppException;
use App\Exceptions\UnauthorizedException;
use App\Http\Middleware\Authorize;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\MethodOverride;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Method override BEFORE everything
        $middleware->prepend(MethodOverride::class);

        // Aliases
        $middleware->alias([
            'auth.app' => EnsureAuthenticated::class,
            'authorize' => Authorize::class,
        ]);

        // Web group additions
        $middleware->web(append: [
            StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // NotFoundAppException — must be registered BEFORE parent AppException
        $exceptions->renderable(function (NotFoundAppException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 404);
            }

            return redirect()->back()->with('error', $e->getMessage());
        });

        // UnauthorizedException — registered before AppException catch-all
        $exceptions->renderable(function (UnauthorizedException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        });

        // AppException catch-all → HTTP response
        $exceptions->renderable(function (AppException $e, Request $request) {
            $status = $e->getCode() ?: 500;
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => $status,
                ], $status);
            }

            // Web: flash + redirect back
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        });
    })
    ->create();
