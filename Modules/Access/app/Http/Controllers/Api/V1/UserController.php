<?php

namespace Modules\Access\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\app\Http\Requests\StoreUserRequest;
use Modules\Access\app\Http\Requests\UpdateUserRequest;
use Modules\Access\app\Interfaces\IUserService;

class UserController extends Controller
{
    public function __construct(
        private IUserService $userService,
    ) {}

    public function index(Request $request)
    {
        try {
            $filter = $request->only(['q_code', 'q_name', 'q_phone', 'q_email', 'q_status', 'q_role', 'q_page_size', 'page']);
            $result = $this->userService->index($filter);

            return response()->json(['status' => true, 'message' => 'OK', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $userId = session('user_id') ?? 'system';
            $user = $this->userService->store($request->validated(), $userId);

            return response()->json(['status' => true, 'message' => 'Create User Success.', 'data' => $user], 201);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $user = $this->userService->edit($id);

            return response()->json(['status' => true, 'message' => 'OK', 'data' => $user]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 404);
        }
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $userId = session('user_id') ?? 'system';
            $user = $this->userService->update($id, $request->validated(), $userId);

            return response()->json(['status' => true, 'message' => 'Update User Success.', 'data' => $user]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->userService->delete($id);

            return response()->json(['status' => true, 'message' => 'Delete User Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function deleteSelected(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $count = $this->userService->deleteSelected($ids);

            return response()->json(['status' => true, 'message' => 'Delete User Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }
}
