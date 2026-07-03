<?php

namespace Modules\Access\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\app\Http\Requests\StoreRoleRequest;
use Modules\Access\app\Http\Requests\UpdateRoleRequest;
use Modules\Access\app\Interfaces\IRoleService;

class RoleController extends Controller
{
    public function __construct(
        private IRoleService $roleService,
    ) {}

    public function index(Request $request)
    {
        try {
            $filter = $request->only(['q_name', 'q_status', 'q_desc', 'q_page_size', 'page']);
            $result = $this->roleService->index($filter);

            return response()->json(['status' => true, 'message' => 'OK', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 500);
        }
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $userId = session('user_id') ?? 'system';
            $role = $this->roleService->store($request->validated(), $userId);

            return response()->json(['status' => true, 'message' => 'Create Role Success.', 'data' => $role], 201);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $role = $this->roleService->edit($id);

            return response()->json(['status' => true, 'message' => 'OK', 'data' => $role]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 404);
        }
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        try {
            $userId = session('user_id') ?? 'system';
            $role = $this->roleService->update($id, $request->validated(), $userId);

            return response()->json(['status' => true, 'message' => 'Update Role Success.', 'data' => $role]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->roleService->delete($id);

            return response()->json(['status' => true, 'message' => 'Delete Role Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function deleteSelected(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $this->roleService->deleteSelected($ids);

            return response()->json(['status' => true, 'message' => 'Delete Role Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }
}
