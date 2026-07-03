<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function __construct(private JwtService $jwtService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            return $this->handleApi($request, $next);
        }

        return $this->handleWeb($request, $next);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function handleWeb(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if (empty($userId)) {
            return redirect()->route('web.auth.login');
        }

        $user = User::find($userId);

        if ($user === null) {
            return redirect()->route('web.auth.login');
        }

        $request->attributes->set('auth_user', $user);

        return $next($request);
    }

    private function handleApi(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (! str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing or malformed Authorization header',
            ], 401);
        }

        $token = substr($authHeader, 7);

        try {
            if ($this->jwtService->isBlacklisted($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has been revoked',
                ], 401);
            }

            $userId = $this->jwtService->getUserId($token);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }

        $user = User::find($userId);

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 401);
        }

        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
