@extends('layouts.be.default.main')
@section('title', 'Role')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Role Management</h1>
</div>

<div class="tw-card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color:var(--primary)">Role List</h2>
        <div class="btn-group btn-sm">
            <a href="{{ route('admin.v1.access.role.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Add Data
            </a>
            <button type="submit" form="selection"
                    formaction="{{ route('admin.v1.access.role.delete_selected') }}"
                    data-confirm="Confirm Delete" class="btn btn-danger btn-sm">
                <i class="fas fa-times"></i> Delete Selected
            </button>
        </div>
    </div>
    <div class="p-4" style="overflow-x:auto">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <form id="searchform" method="GET" action="{{ route('admin.v1.access.role.index') }}">
                <tr>
                    <th width="2%"></th>
                    <th width="7%">
                        <select name="q_page_size" id="q_page_size" class="form-control">
                            @foreach([10,20,50,100] as $sz)
                            <option value="{{ $sz }}" {{ ($filter['q_page_size'] ?? 10) == $sz ? 'selected' : '' }}>{{ $sz }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th width="24%"><input id="q_name" type="text" name="q_name" class="form-control" value="{{ $filter['q_name'] ?? '' }}"></th>
                    <th width="12%">
                        <select name="q_status" id="q_status" class="form-control">
                            <option disabled {{ ($filter['q_status'] ?? '') === '' ? 'selected' : '' }}>Select</option>
                            <option value="Active" {{ ($filter['q_status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ ($filter['q_status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </th>
                    <th width="13%"><input id="q_desc" type="text" name="q_desc" class="form-control" value="{{ $filter['q_desc'] ?? '' }}"></th>
                    <th width="5%" class="text-center align-middle">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-search"></i></button>
                            <a href="{{ route('admin.v1.access.role.index') }}" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                        </div>
                    </th>
                </tr>
                </form>
                <tr>
                    <th width="5%"><input type="checkbox" id="checkall"></th>
                    <th width="5%">No</th>
                    <th width="24%">Name</th>
                    <th width="15%">Status</th>
                    <th width="13%">Description</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody>
                <form id="selection" method="POST" action="{{ route('admin.v1.access.role.delete_selected') }}">
                    @csrf
                    @forelse($result['data'] as $i => $role)
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="{{ $role['id'] }}"></td>
                        <td>{{ ($result['meta']['current_page'] - 1) * $result['meta']['per_page'] + $i + 1 }}</td>
                        <td>{{ $role['name'] }}</td>
                        <td class="text-left">
                            @if($role['status'] === 'Active')
                                <i class="fas fa-check-circle text-green-500 text-xl" title="Active"></i>
                            @else
                                <i class="fas fa-times-circle text-red-500 text-xl" title="Inactive"></i>
                            @endif
                        </td>
                        <td>{{ $role['desc'] ?? '' }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle-dd aria-expanded="false">Action</button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="{{ route('admin.v1.access.role.permission', $role['id']) }}" class="dropdown-item">
                                        <i class="fas fa-key"></i> Permission
                                    </a>
                                    <a href="{{ route('admin.v1.access.role.edit', $role['id']) }}" class="dropdown-item">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.v1.access.role.delete', $role['id']) }}?_method=DELETE" class="m-0">
                                        @csrf
                                        <button type="submit" data-confirm="Confirm Delete" class="dropdown-item danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">No roles found</td></tr>
                    @endforelse
                </form>
            </tbody>
        </table>

        @if($result['meta']['last_page'] > 1)
        <div class="d-flex justify-content-end mt-4">
            <nav>
                <ul class="pagination">
                    @if($result['meta']['has_prev'])
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $result['meta']['current_page'] - 1]) }}">Previous</a></li>
                    @endif
                    @foreach($result['meta']['page_numbers'] as $pn)
                        @if($pn === '...')
                            <li class="page-item"><span class="page-link">…</span></li>
                        @else
                            <li class="page-item {{ $pn == $result['meta']['current_page'] ? 'active' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pn]) }}">{{ $pn }}</a>
                            </li>
                        @endif
                    @endforeach
                    @if($result['meta']['has_next'])
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $result['meta']['current_page'] + 1]) }}">Next</a></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<script>$("#checkall").click(function(){ $('input:checkbox').not(this).prop('checked', this.checked); });</script>
@endsection
