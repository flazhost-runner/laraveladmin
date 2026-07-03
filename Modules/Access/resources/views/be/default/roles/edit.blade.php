@extends('layouts.be.default.main')
@section('title', 'Edit Role')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Role Management</h1>
</div>

<div class="tw-card p-6">
    <h2 class="text-lg font-bold" style="color:var(--primary)">Role Form</h2>
    <form method="POST" action="{{ route('admin.v1.access.role.update', $role->id) }}?_method=PUT">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Name</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $role->name) }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                <option value="Active" {{ old('status', $role->status) === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ old('status', $role->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="desc" class="form-label fw-semibold">Description</label>
            <input id="desc" type="text" class="form-control @error('desc') is-invalid @enderror" name="desc" value="{{ old('desc', $role->desc) }}">
            @error('desc')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary-tw px-4 py-2"><i class="fas fa-save"></i> Save</button>
            <a href="{{ route('admin.v1.access.role.index') }}" class="btn btn-danger px-4 py-2 text-white">Back</a>
        </div>
    </form>
</div>
@endsection
