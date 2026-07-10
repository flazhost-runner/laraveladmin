@extends('layouts.be.default.main')
@section('title', 'Profile')
@section('content')
{{-- Replika 1:1 NodeAdmin src/modules/profile/views/be/default/profile.ejs:
     satu kartu "User Form" — code/name/phone/email/timezone/password/confirm/
     status/picture(file + preview 90×90 + live preview). --}}
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Profile</h1>
</div>

<div class="tw-card p-6">
    <h2 class="text-lg font-bold mb-4" style="color:var(--primary)">User Form</h2>
    <form method="POST" action="{{ route('admin.v1.profile.update') }}?_method=PUT" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="code" class="form-label fw-semibold">Code</label>
            <input type="text" id="code" name="code"
                   class="form-control @if(getError('code')) is-invalid @endif"
                   value="{{ old('code', $user->code) }}">
            @if(getError('code'))<div class="invalid-feedback">{{ getError('code') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Name</label>
            <input type="text" id="name" name="name"
                   class="form-control @if(getError('name')) is-invalid @endif"
                   value="{{ old('name', $user->name) }}">
            @if(getError('name'))<div class="invalid-feedback">{{ getError('name') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label fw-semibold">Phone Number</label>
            <input type="text" id="phone" name="phone"
                   class="form-control @if(getError('phone')) is-invalid @endif"
                   value="{{ old('phone', $user->phone) }}">
            @if(getError('phone'))<div class="invalid-feedback">{{ getError('phone') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input type="email" id="email" name="email"
                   class="form-control @if(getError('email')) is-invalid @endif"
                   value="{{ old('email', $user->email) }}">
            @if(getError('email'))<div class="invalid-feedback">{{ getError('email') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="timezone" class="form-label fw-semibold">Timezone</label>
            <select id="timezone" name="timezone"
                    class="form-control @if(getError('timezone')) is-invalid @endif">
                @foreach(($timezones ?? []) as $tz)
                <option value="{{ $tz }}" @if(old('timezone', $user->timezone) === $tz) selected @endif>{{ $tz }}</option>
                @endforeach
            </select>
            @if(getError('timezone'))<div class="invalid-feedback">{{ getError('timezone') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <input type="password" id="password" name="password" value=""
                   autocomplete="off"
                   class="form-control @if(getError('password')) is-invalid @endif">
            @if(getError('password'))<div class="invalid-feedback">{{ getError('password') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label fw-semibold">Password Confirm</label>
            <input type="password" id="password_confirmation" name="password_confirmation" value=""
                   autocomplete="off"
                   class="form-control @if(getError('password_confirmation')) is-invalid @endif">
            @if(getError('password_confirmation'))<div class="invalid-feedback">{{ getError('password_confirmation') }}</div>@endif
        </div>

        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select id="status" name="status" required
                    class="form-control @if(getError('status')) is-invalid @endif">
                <option value="Active" @if(old('status', $user->status) === 'Active') selected @endif>Active</option>
                <option value="Inactive" @if(old('status', $user->status) === 'Inactive') selected @endif>Inactive</option>
            </select>
            @if(getError('status'))<div class="invalid-feedback">{{ getError('status') }}</div>@endif
        </div>

        <div class="mb-4">
            <label for="picture" class="form-label fw-semibold">Picture</label>
            <div class="d-flex align-items-center gap-3">
                <img id="picturePreview"
                     src="{{ getFile($user->picture ?? 'modules/access/user/user.png') }}"
                     width="90" height="90" class="rounded border p-1"
                     style="object-fit:contain;background:#f8fafc"
                     onerror="this.style.visibility='hidden'">
                <input type="file" id="picture" name="picture" accept="image/*"
                       class="form-control @if(getError('picture')) is-invalid @endif">
            </div>
            @if(getError('picture'))<div class="text-danger small mt-1">{{ getError('picture') }}</div>@endif
        </div>

        <button type="submit" class="btn btn-primary-tw px-4 py-2"><i class="fas fa-save me-1"></i> Save</button>
    </form>
</div>

<script>
// Live preview avatar — paritas NodeAdmin profile.ejs.
document.getElementById('picture').addEventListener('change', function () {
    var f = this.files && this.files[0];
    var prev = document.getElementById('picturePreview');
    if (f && f.type.indexOf('image/') === 0) {
        prev.src = URL.createObjectURL(f);
        prev.style.visibility = 'visible';
    }
});
</script>
@endsection
