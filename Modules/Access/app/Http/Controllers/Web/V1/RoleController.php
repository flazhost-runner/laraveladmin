<?php

namespace Modules\Access\app\Http\Controllers\Web\V1;

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
        $filter = $request->only(['q_name', 'q_status', 'q_desc', 'q_page_size', 'page']);
        $result = $this->roleService->index($filter);

        return view('access-module::be.default.roles.index', compact('result', 'filter'));
    }

    public function create()
    {
        return view('access-module::be.default.roles.create');
    }

    public function store(StoreRoleRequest $request)
    {
        $userId = session('user_id');
        $this->roleService->store($request->validated(), $userId);

        return redirect()->route('admin.v1.access.role.index')->with('success', 'Create Role Success.');
    }

    public function edit(string $id)
    {
        $role = $this->roleService->edit($id);

        return view('access-module::be.default.roles.edit', compact('role'));
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        $userId = session('user_id');
        $this->roleService->update($id, $request->validated(), $userId);

        return redirect()->route('admin.v1.access.role.index')->with('success', 'Update Role Success.');
    }

    public function delete(string $id)
    {
        $this->roleService->delete($id);

        return redirect()->route('admin.v1.access.role.index')->with('success', 'Delete Role Success.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('selected', []);
        $this->roleService->deleteSelected($ids);

        return redirect()->route('admin.v1.access.role.index')->with('success', 'Delete Role Success.');
    }
}
