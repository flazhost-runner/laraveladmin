<?php

namespace Modules\Access\app\Http\Controllers\Web\V1;

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
        // Auto-discover permissions from routes on each index load
        $this->permissionService->syncFromRoutes();

        $filter = $request->only(['q_name', 'q_guard', 'q_method', 'q_status', 'q_desc', 'q_page_size', 'q_page']);
        $result = $this->permissionService->index($filter);

        return view('access-module::be.default.permission.index', compact('result', 'filter'));
    }

    public function create()
    {
        return view('access-module::be.default.permission.create');
    }

    public function store(StorePermissionRequest $request)
    {
        $userId = session('user_id');
        $this->permissionService->store($request->validated(), $userId);

        return redirect()->route('admin.v1.access.permission.index')->with('success', 'Create Permission Success.');
    }

    public function edit(string $id)
    {
        $permission = $this->permissionService->edit($id);

        return view('access-module::be.default.permission.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, string $id)
    {
        $userId = session('user_id');
        $this->permissionService->update($id, $request->validated(), $userId);

        return redirect()->route('admin.v1.access.permission.index')->with('success', 'Update Permission Success.');
    }

    public function delete(string $id)
    {
        $this->permissionService->delete($id);

        return redirect()->route('admin.v1.access.permission.index')->with('success', 'Delete Permission Success.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('selected', []);
        $this->permissionService->deleteSelected($ids);

        return redirect()->route('admin.v1.access.permission.index')->with('success', 'Delete Permission Success.');
    }

    public function syncFromRoutes()
    {
        $this->permissionService->syncFromRoutes();

        return redirect()->route('admin.v1.access.permission.index')->with('success', 'Sync Permission Success.');
    }
}
