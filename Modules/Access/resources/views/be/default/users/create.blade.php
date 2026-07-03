@extends('layouts.be.default.main')
@section('title', 'Create User')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
</div>

<div class="tw-card">
    <h2 class="text-lg font-bold mb-4" style="color:var(--primary)">User Form</h2>
    <form method="POST" action="{{ route('admin.v1.access.user.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="code">Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code') }}" placeholder="User code (optional)">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="name">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="email">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="timezone">Timezone</label>
                <select id="timezone" name="timezone" class="form-control @error('timezone') is-invalid @enderror">
                    <option value="">— Select Timezone —</option>
                    @foreach(['Asia/Jakarta','UTC','America/New_York','Europe/London','Asia/Singapore','Asia/Tokyo','Asia/Shanghai'] as $tz)
                    <option value="{{ $tz }}" {{ old('timezone', 'Asia/Jakarta') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
                @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="Phone number (optional)">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="password">Password <span class="text-red-500">*</span></label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="password_confirmation">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>
            <div>
                <label class="form-label" for="status">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label" for="picture">Picture</label>
                <input type="file" class="form-control @error('picture') is-invalid @enderror"
                    id="picture" name="picture" accept="image/*" onchange="previewImage(this)">
                <div id="picture-preview" class="mt-2" style="{{ old('picture') ? '' : 'display:none' }}">
                    <img id="picture-img" src="" alt="Preview" style="max-height:120px; border-radius:0.375rem;">
                </div>
                <div class="text-danger small mt-1">@error('picture'){{ $message }}@enderror</div>
            </div>
            <div class="md:col-span-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="blocked" name="blocked" value="1" class="form-check-input"
                           {{ old('blocked') ? 'checked' : '' }}
                           onchange="document.getElementById('blocked-reason-wrap').style.display = this.checked ? '' : 'none'">
                    <label class="form-label mb-0" for="blocked">Blocked</label>
                </div>
                <div id="blocked-reason-wrap" style="{{ old('blocked') ? '' : 'display:none' }}" class="mt-2">
                    <label class="form-label" for="blocked_reason">Blocked Reason</label>
                    <input type="text" id="blocked_reason" name="blocked_reason"
                           class="form-control @error('blocked_reason') is-invalid @enderror"
                           value="{{ old('blocked_reason') }}" placeholder="Reason for blocking...">
                    @error('blocked_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="form-label">Roles</label>
            <div class="flex flex-wrap gap-3">
                @foreach($roles as $role)
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                    <span>{{ $role->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save fa-fw"></i> Save
            </button>
            <a href="{{ route('admin.v1.access.user.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

@endsection
