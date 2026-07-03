<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName === null) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = $request->attributes->get('auth_user');

        if ($user === null) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized.',
                    'data' => null,
                ], 401);
            }

            return redirect()->route('web.auth.login');
        }

        if ($user->hasRole('Administrator')) {
            return $next($request);
        }

        $method = $request->method();

        if (! $user->hasPermission($routeName, $method)) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden.',
                    'data' => null,
                ], 403);
            }

            return redirect()
                ->back()
                ->with('error', 'Unauthorized.');
        }

        return $next($request);
    }
}
