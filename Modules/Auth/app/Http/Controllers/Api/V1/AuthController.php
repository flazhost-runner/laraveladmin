<?php

namespace Modules\Auth\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\app\Interfaces\IAuthService;

class AuthController extends Controller
{
    public function __construct(private IAuthService $authService, private JwtService $jwt) {}

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'OK',
                'data' => [
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                    'user' => $result['user'],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 401);
        }
    }

    public function me(Request $request)
    {
        $user = auth_user();

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $user]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        $userId = $this->jwt->getUserId($token);
        $this->authService->logout($userId, $token);

        return response()->json(['status' => true, 'message' => 'Logout Success.', 'data' => null]);
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Register Success.',
                'data' => [
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                    'user' => $result['user'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }
}
