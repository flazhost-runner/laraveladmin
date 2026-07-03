<?php

namespace Modules\Access\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\app\Http\Requests\StorePermissionRequest;
use Modules\Access\app\Http\Requests\UpdatePermissionRequest;
use Modules\Access\app\Interfaces\IPermissionService;

class PermissionController extends Controller
{
    public function __construct(
        private IPermissionService $permissionService,
    ) {}

    public function index(Request $request)
    {
        try {
            $filter = $request->only(['q_name', 'q_method', 'q_status', 'q_desc', 'q_page_size', 'page']);
            $result = $this->permissionService->index($filter);

            return response()->json(['status' => true, 'message' => 'OK', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 500);
        }
    }

    public function store(StorePermissionRequest $request)
    {
        try {
            $userId = session('user_id') ?? 'system';
            $permission = $this->permissionService->store($request->validated(), $userId);

            return response()->json(['status' => true, 'message' => 'Create Permission Success.', 'data' => $permission], 201);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $permission = $this->permissionService->edit($id);

            return response()->json(['status' => true, 'message' => 'OK', 'data' => $permission]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 404);
        }
    }

    public function update(UpdatePermissionRequest $request, string $id)
    {
        try {
            $userId = session('user_id') ?? 'system';
            $permission = $this->permissionService->update($id, $request->validated(), $userId);

            return response()->json(['status' => true, 'message' => 'Update Permission Success.', 'data' => $permission]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->permissionService->delete($id);

            return response()->json(['status' => true, 'message' => 'Delete Permission Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function deleteSelected(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $this->permissionService->deleteSelected($ids);

            return response()->json(['status' => true, 'message' => 'Delete Permission Success.', 'data' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 400);
        }
    }

    public function syncFromRoutes()
    {
        try {
            $count = $this->permissionService->syncFromRoutes();

            return response()->json(['status' => true, 'message' => 'Sync Permission Success.', 'data' => ['synced' => $count]]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => null], $e->getCode() ?: 500);
        }
    }
}
