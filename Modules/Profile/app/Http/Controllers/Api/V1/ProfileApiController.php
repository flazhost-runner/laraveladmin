<?php

namespace Modules\Profile\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Profile\app\Interfaces\IProfileService;

class ProfileApiController extends Controller
{
    public function __construct(
        private IProfileService $profileService,
    ) {}

    public function index(Request $request)
    {
        try {
            $user = $request->attributes->get('auth_user');
            if (! $user) {
                $userId = (string) session('user_id');
                $user = $this->profileService->get($userId);
            }

            return response()->json(['status' => true, 'message' => 'OK', 'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone ?? '',
                'picture' => $user->picture ?? '',
                'status' => $user->status ?? '',
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], 404);
        }
    }
}
