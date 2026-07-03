<?php

namespace Modules\Access\app\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
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
        $filter = $request->only(['q_code', 'q_name', 'q_phone', 'q_email', 'q_status', 'q_role', 'q_page_size', 'page']);
        $result = $this->userService->index($filter);
        $roles = Role::orderBy('name')->get();

        return view('access-module::be.default.users.index', compact('result', 'filter', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('access-module::be.default.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $userId = session('user_id');
        $this->userService->store($request->validated(), $userId);

        return redirect()->route('admin.v1.access.user.index')->with('success', 'Create User Success.');
    }

    public function edit(string $id)
    {
        $user = $this->userService->edit($id);
        $roles = Role::orderBy('name')->get();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        return view('access-module::be.default.users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $userId = session('user_id');
        $this->userService->update($id, $request->validated(), $userId);

        return redirect()->route('admin.v1.access.user.index')->with('success', 'Update User Success.');
    }

    public function delete(string $id)
    {
        $this->userService->delete($id);

        return redirect()->route('admin.v1.access.user.index')->with('success', 'Delete User Success.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->input('selected', []);
        $this->userService->deleteSelected($ids);

        return redirect()->route('admin.v1.access.user.index')->with('success', 'Delete User Success.');
    }
}
