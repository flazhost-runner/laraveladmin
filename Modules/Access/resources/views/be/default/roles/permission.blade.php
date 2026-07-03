@extends('layouts.be.default.main')
@section('title', 'Role Permissions')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Permission Management</h1>
</div>

<div class="tw-card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color:var(--primary)">Permission List</h2>
        <div class="btn-group btn-sm">
            <button type="submit" form="selection"
                    formaction="{{ route('admin.v1.access.role.permission.assign_selected', $id) }}"
                    data-confirm="Confirm Assign" class="btn btn-info btn-sm">
                <i class="fas fa-check"></i> Assign Selected
            </button>
            <button type="submit" form="selection"
                    formaction="{{ route('admin.v1.access.role.permission.unassign_selected', $id) }}"
                    data-confirm="Confirm Unassign" class="btn btn-danger btn-sm">
                <i class="fas fa-times"></i> Unassign Selected
            </button>
        </div>
    </div>
    <div class="p-4" style="overflow-x:auto">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <form id="searchform" method="GET" action="{{ route('admin.v1.access.role.permission', $id) }}">
                <tr>
                    <th width="2%"></th>
                    <th width="7%">
                        <select name="q_page_size" id="q_page_size" class="form-control">
                            @foreach([10,20,50,100] as $sz)
                            <option value="{{ $sz }}" {{ ($filter['q_page_size'] ?? 10) == $sz ? 'selected' : '' }}>{{ $sz }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th width="20%"><input id="q_name" type="text" name="q_name" class="form-control" value="{{ $filter['q_name'] ?? '' }}"></th>
                    <th width="10%">
                        <select name="q_status" id="q_status" class="form-control">
                            <option disabled {{ ($filter['q_status'] ?? '') === '' ? 'selected' : '' }}>Select</option>
                            <option value="Active" {{ ($filter['q_status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ ($filter['q_status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </th>
                    <th width="15%"><input id="q_desc" type="text" name="q_desc" class="form-control" value="{{ $filter['q_desc'] ?? '' }}"></th>
                    <th width="5%" class="text-center align-middle">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-search"></i></button>
                            <a href="{{ route('admin.v1.access.role.permission', $id) }}" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                        </div>
                    </th>
                </tr>
                </form>
                <tr>
                    <th width="5%"><input type="checkbox" id="checkall"></th>
                    <th width="5%">No</th>
                    <th width="20%">Name</th>
                    <th width="15%">Status</th>
                    <th width="10%">Description</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody>
                <form id="selection" method="POST" action="{{ route('admin.v1.access.role.permission.assign_selected', $id) }}">
                    @csrf
                    @forelse($result['data'] as $i => $perm)
                    @php $isAssigned = in_array($perm['id'], $result['assigned_ids']); @endphp
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="{{ $perm['id'] }}"></td>
                        <td>{{ ($result['meta']['current_page'] - 1) * $result['meta']['per_page'] + $i + 1 }}</td>
                        <td>{{ $perm['name'] }}</td>
                        <td class="text-left">
                            @if($isAssigned)
                                <i class="fas fa-check-circle text-green-500 text-xl" title="Assigned"></i>
                            @else
                                <i class="fas fa-times-circle text-gray-300 text-xl" title="Not assigned"></i>
                            @endif
                        </td>
                        <td>{{ $perm['desc'] ?? '' }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle-dd aria-expanded="false">Action</button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="{{ route('admin.v1.access.role.permission.assign', [$id, $perm['id']]) }}" class="dropdown-item">
                                        <i class="fas fa-check"></i> Assign
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('admin.v1.access.role.permission.unassign', [$id, $perm['id']]) }}" class="dropdown-item danger">
                                        <i class="fas fa-times"></i> Unassign
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">No permissions found</td></tr>
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
