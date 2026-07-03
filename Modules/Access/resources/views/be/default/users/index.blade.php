@extends('layouts.be.default.main')
@section('title', 'User')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
</div>

<div class="tw-card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color:var(--primary)">User List</h2>
        <div class="btn-group btn-sm">
            <a href="{{ route('admin.v1.access.user.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-fw fa-plus"></i> Add Data
            </a>
            <button type="submit" form="selection"
                    formaction="{{ route('admin.v1.access.user.delete_selected') }}"
                    data-confirm="Delete selected users?" class="btn btn-danger btn-sm">
                <i class="fas fa-fw fa-times"></i> Delete Selected
            </button>
        </div>
    </div>
    <div class="p-4" style="overflow-x:auto">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <form id="searchform" method="GET" action="{{ route('admin.v1.access.user.index') }}">
                <tr>
                    <th width="2%"></th>
                    <th width="7%">
                        <select name="q_page_size" class="form-control" onchange="document.getElementById('searchform').submit()">
                            @foreach([10,20,50,100] as $sz)
                            <option value="{{ $sz }}" {{ ($filter['q_page_size'] ?? 10) == $sz ? 'selected' : '' }}>{{ $sz }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th><input type="text" name="q_code" class="form-control" value="{{ $filter['q_code'] ?? '' }}" placeholder="Code"></th>
                    <th><input type="text" name="q_name" class="form-control" value="{{ $filter['q_name'] ?? '' }}" placeholder="Name"></th>
                    <th><input type="text" name="q_phone" class="form-control" value="{{ $filter['q_phone'] ?? '' }}" placeholder="Phone"></th>
                    <th><input type="text" name="q_email" class="form-control" value="{{ $filter['q_email'] ?? '' }}" placeholder="Email"></th>
                    <th>
                        <select name="q_status" class="form-control">
                            <option value="">Select</option>
                            <option value="Active" {{ ($filter['q_status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ ($filter['q_status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </th>
                    <th></th>
                    <th>
                        <select name="q_role" class="form-control">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ ($filter['q_role'] ?? '') === $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-search"></i></button>
                            <a href="{{ route('admin.v1.access.user.index') }}" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                        </div>
                    </th>
                </tr>
                </form>
                <tr>
                    <th><input type="checkbox" id="checkall"></th>
                    <th>No</th><th>Code</th><th>Name</th><th>Phone</th><th>Email</th>
                    <th>Status</th><th>Picture</th><th>Roles</th><th>Action</th>
                </tr>
            </thead>
            <form id="selection" method="POST" action="{{ route('admin.v1.access.user.delete_selected') }}">
                @csrf
            <tbody>
                @forelse($result['data'] as $i => $user)
                <tr>
                    <td><input type="checkbox" name="selected[]" value="{{ $user['id'] }}"></td>
                    <td>{{ ($result['meta']['current_page'] - 1) * $result['meta']['per_page'] + $i + 1 }}</td>
                    <td>{{ $user['code'] ?? '-' }}</td>
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $user['phone'] ?? '-' }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>
                        @if($user['status'] === 'Active')
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        @else
                            <i class="fas fa-times-circle text-red-500 text-xl"></i>
                        @endif
                    </td>
                    <td>
                        <img src="{{ $user['picture'] ?? '' }}" alt="user avatar" style="max-width:48px;max-height:48px;border-radius:4px" onerror="this.style.display='none'">
                    </td>
                    <td>
                        @foreach($user['roles'] ?? [] as $role)
                            <span class="badge text-bg-primary">{{ $role['name'] }}</span>
                        @endforeach
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-primary" data-toggle-dd="dd-user-{{ $user['id'] }}">
                                Action <i class="fas fa-chevron-down ms-1"></i>
                            </button>
                            <div id="dd-user-{{ $user['id'] }}" class="dropdown-menu">
                                <a href="{{ route('admin.v1.access.user.edit', $user['id']) }}" class="dropdown-item">
                                    <i class="fas fa-edit fa-fw"></i> Edit
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('admin.v1.access.user.delete', $user['id']) }}?_method=DELETE">
                                    @csrf
                                    <button type="submit" class="dropdown-item danger" data-confirm="Confirm Delete">
                                        <i class="fas fa-trash fa-fw"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-8 text-gray-400">No users found</td></tr>
                @endforelse
            </tbody>
            </form>
        </table>

        @if($result['meta']['last_page'] > 1)
        <nav class="mt-4 flex items-center gap-1">
            @if($result['meta']['has_prev'])
            <a href="{{ request()->fullUrlWithQuery(['page' => $result['meta']['current_page'] - 1]) }}" class="page-link">« Previous</a>
            @endif
            @foreach($result['meta']['page_numbers'] as $pn)
                @if($pn === '...')
                    <span class="page-link">…</span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pn]) }}"
                       class="page-link {{ $pn == $result['meta']['current_page'] ? 'active' : '' }}">{{ $pn }}</a>
                @endif
            @endforeach
            @if($result['meta']['has_next'])
            <a href="{{ request()->fullUrlWithQuery(['page' => $result['meta']['current_page'] + 1]) }}" class="page-link">Next »</a>
            @endif
        </nav>
        @endif
    </div>
</div>
@endsection
