@extends('layouts.be.default.main')
@section('title', 'Permission')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Permission Management</h1>
</div>

<div class="tw-card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color:var(--primary)">Permission List</h2>
        <div class="btn-group btn-sm">
            <a href="{{ route('admin.v1.access.permission.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-fw fa-plus"></i> Add Data
            </a>
            <button type="submit" form="selection"
                    formaction="{{ route('admin.v1.access.permission.delete_selected') }}"
                    data-confirm="Confirm Delete" class="btn btn-danger btn-sm">
                <i class="fas fa-fw fa-times"></i> Delete Selected
            </button>
        </div>
    </div>
    <div class="p-4" style="overflow-x:auto">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <form id="searchform" method="GET" action="{{ route('admin.v1.access.permission.index') }}">
                <tr>
                    <th width="2%"></th>
                    <th width="7%">
                        <select name="q_page_size" class="form-control">
                            @foreach([10,20,50,100] as $sz)
                            <option value="{{ $sz }}" {{ ($filter['q_page_size'] ?? 10) == $sz ? 'selected' : '' }}>{{ $sz }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th width="18%"><input type="text" name="q_name" class="form-control" value="{{ $filter['q_name'] ?? '' }}"></th>
                    <th width="9%">
                        <select name="q_guard" class="form-control">
                            <option disabled {{ ($filter['q_guard'] ?? '') === '' ? 'selected' : '' }}>Select</option>
                            <option value="web" {{ ($filter['q_guard'] ?? '') === 'web' ? 'selected' : '' }}>web</option>
                            <option value="api" {{ ($filter['q_guard'] ?? '') === 'api' ? 'selected' : '' }}>api</option>
                        </select>
                    </th>
                    <th width="15%">
                        <select name="q_method" class="form-control">
                            <option disabled {{ ($filter['q_method'] ?? '') === '' ? 'selected' : '' }}>Select</option>
                            @foreach(['GET','POST','PATCH','PUT','DELETE'] as $m)
                            <option value="{{ $m }}" {{ ($filter['q_method'] ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th width="10%">
                        <select name="q_status" class="form-control">
                            <option disabled {{ ($filter['q_status'] ?? '') === '' ? 'selected' : '' }}>Select</option>
                            <option value="Active" {{ ($filter['q_status'] ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ ($filter['q_status'] ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </th>
                    <th width="15%"><input type="text" name="q_desc" class="form-control" value="{{ $filter['q_desc'] ?? '' }}"></th>
                    <th width="5%" class="text-center align-middle">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-fw fa-search"></i></button>
                            <a href="{{ route('admin.v1.access.permission.index') }}" class="btn btn-sm btn-danger"><i class="fas fa-fw fa-times"></i></a>
                        </div>
                    </th>
                </tr>
                </form>
                <tr>
                    <th width="5%"><input type="checkbox" id="checkall"></th>
                    <th width="5%">No</th>
                    <th width="18%">Name</th>
                    <th width="9%">Guard</th>
                    <th width="15%">Method</th>
                    <th width="10%">Status</th>
                    <th width="15%">Description</th>
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <form id="selection" method="POST" action="{{ route('admin.v1.access.permission.delete_selected') }}">
                @csrf
            <tbody>
                @forelse($result['data'] as $i => $perm)
                <tr>
                    <td><input type="checkbox" name="selected[]" value="{{ $perm['id'] }}"></td>
                    <td>{{ ($result['meta']['current_page'] - 1) * $result['meta']['per_page'] + $i + 1 }}</td>
                    <td>{{ $perm['name'] }}</td>
                    <td><span class="badge text-bg-primary">{{ $perm['guard_name'] ?? 'web' }}</span></td>
                    <td>{{ $perm['method'] }}</td>
                    <td class="text-left">
                        @if(($perm['status'] ?? '') === 'Active')
                            <i class="fas fa-check-circle text-green-500 text-xl" title="Active"></i>
                        @else
                            <i class="fas fa-times-circle text-red-500 text-xl" title="Inactive"></i>
                        @endif
                    </td>
                    <td>{{ $perm['desc'] ?? '' }}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle-dd aria-expanded="false">Action</button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="{{ route('admin.v1.access.permission.edit', $perm['id']) }}" class="dropdown-item">
                                    <i class="fas fa-pen fa-fw"></i> Edit
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('admin.v1.access.permission.delete', $perm['id']) }}?_method=DELETE" class="m-0">
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
                <tr><td colspan="8" class="text-center py-8 text-gray-400">No permissions found</td></tr>
                @endforelse
            </tbody>
            </form>
        </table>

        @if($result['meta']['last_page'] > 1)
        <div class="d-flex justify-content-end mt-4">
            <nav>
                <ul class="pagination">
                    @if($result['meta']['has_prev'])
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['q_page' => $result['meta']['current_page'] - 1]) }}">Previous</a></li>
                    @endif
                    @foreach($result['meta']['page_numbers'] as $pn)
                        @if($pn === '...')
                            <li class="page-item"><span class="page-link">…</span></li>
                        @else
                            <li class="page-item {{ $pn == $result['meta']['current_page'] ? 'active' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['q_page' => $pn]) }}">{{ $pn }}</a>
                            </li>
                        @endif
                    @endforeach
                    @if($result['meta']['has_next'])
                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['q_page' => $result['meta']['current_page'] + 1]) }}">Next</a></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<script>
    $("#checkall").click(function(){ $('input:checkbox').not(this).prop('checked', this.checked); });
</script>
@endsection
