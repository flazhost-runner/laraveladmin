<?php

namespace Modules\Access\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Access\app\Interfaces\IRoleService;

class RolePermissionController extends Controller
{
    public function __construct(
        private IRoleService $roleService,
    ) {}

    public function index(Request $request, string $id)
    {
        $filter = $request->only(['q_name', 'q_status', 'q_desc', 'q_method', 'q_page_size', 'page']);
        $result = $this->roleService->listPermissions($id, $filter);

        return view('access-module::be.default.roles.permission', compact('result', 'filter', 'id'));
    }

    public function assign(string $id, string $permission_id)
    {
        $this->roleService->assignPermission($id, $permission_id);

        return redirect()->route('admin.v1.access.role.permission', $id)->with('success', 'Assign Permission Success.');
    }

    public function assignSelected(Request $request, string $id)
    {
        $permIds = $request->input('selected', []);
        $this->roleService->assignSelected($id, $permIds);

        return redirect()->route('admin.v1.access.role.permission', $id)->with('success', 'Assign Permission Success.');
    }

    public function unassign(string $id, string $permission_id)
    {
        $this->roleService->unassignPermission($id, $permission_id);

        return redirect()->route('admin.v1.access.role.permission', $id)->with('success', 'Unassign Permission Success.');
    }

    public function unassignSelected(Request $request, string $id)
    {
        $permIds = $request->input('selected', []);
        $this->roleService->unassignSelected($id, $permIds);

        return redirect()->route('admin.v1.access.role.permission', $id)->with('success', 'Unassign Permission Success.');
    }
}
