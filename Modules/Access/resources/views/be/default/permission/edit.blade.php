@extends('layouts.be.default.main')
@section('title', 'Edit Permission')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Permission Management</h1>
</div>

<div class="tw-card p-6">
    <h2 class="text-lg font-bold mb-4" style="color:var(--primary)">Permission Form</h2>
    <form method="POST" action="{{ route('admin.v1.access.permission.update', $permission->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Name</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $permission->name) }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="guard_name" class="form-label fw-semibold">Guard</label>
            <select id="guard_name" name="guard_name" class="form-control">
                <option value="web" {{ old('guard_name', $permission->guard_name) === 'web' ? 'selected' : '' }}>web</option>
                <option value="api" {{ old('guard_name', $permission->guard_name) === 'api' ? 'selected' : '' }}>api</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="method" class="form-label fw-semibold">Method</label>
            <select id="method" name="method" class="form-control @error('method') is-invalid @enderror">
                <option value="">-- Select Method --</option>
                <option value="GET"    {{ old('method', $permission->method) === 'GET'    ? 'selected' : '' }}>GET</option>
                <option value="POST"   {{ old('method', $permission->method) === 'POST'   ? 'selected' : '' }}>POST</option>
                <option value="PUT"    {{ old('method', $permission->method) === 'PUT'    ? 'selected' : '' }}>PUT</option>
                <option value="PATCH"  {{ old('method', $permission->method) === 'PATCH'  ? 'selected' : '' }}>PATCH</option>
                <option value="DELETE" {{ old('method', $permission->method) === 'DELETE' ? 'selected' : '' }}>DELETE</option>
            </select>
            @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                <option value="Active" {{ old('status', $permission->status) === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ old('status', $permission->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="desc" class="form-label fw-semibold">Description</label>
            <input id="desc" type="text" class="form-control @error('desc') is-invalid @enderror" name="desc" value="{{ old('desc', $permission->desc) }}">
            @error('desc')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary-tw px-4 py-2"><i class="fas fa-save me-1"></i> Save</button>
            <a href="{{ route('admin.v1.access.permission.index') }}" class="btn btn-danger px-4 py-2 text-white">Back</a>
        </div>
    </form>
</div>
@endsection
