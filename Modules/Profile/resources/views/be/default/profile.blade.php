@extends('layouts.be.default.main')
@section('title', 'My Profile')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
</div>

<div class="row">
    {{-- ═══════════════════════════════════════════════════════
         Section 1: Profile Info Form
    ═══════════════════════════════════════════════════════ --}}
    <div class="col-md-6">
        <div class="tw-card p-6 mb-6">
            <h2 class="text-lg font-bold mb-4" style="color:var(--primary)">
                <i class="fas fa-user fa-fw me-2"></i>Profile Information
            </h2>

            {{-- Avatar --}}
            <div class="flex justify-center mb-4">
                @if($user?->picture)
                    <img id="avatar-preview"
                         src="{{ $user->picture }}"
                         alt="user avatar"
                         class="rounded-full object-cover"
                         style="width:96px;height:96px;border:3px solid var(--primary)"
                         onerror="this.style.display='none'">
                @else
                    <span id="avatar-initials"
                          class="rounded-full flex items-center justify-center text-white text-3xl font-bold"
                          style="width:96px;height:96px;background:var(--primary)">
                        {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.v1.profile.update') }}?_method=PUT" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4">
                    <div>
                        <label class="form-label" for="name">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user?->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" class="form-control" value="{{ $user?->email }}" readonly
                               style="background:#f9fafb;color:#6b7280;cursor:not-allowed">
                        <small class="text-gray-400">Email cannot be changed from this page.</small>
                    </div>

                    <div>
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user?->phone) }}"
                               placeholder="e.g. +62 812 3456 7890">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="picture">Picture URL</label>
                        <input type="text" id="picture" name="picture"
                               class="form-control @error('picture') is-invalid @enderror"
                               value="{{ old('picture', $user?->picture) }}"
                               placeholder="https://...">
                        @error('picture')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-gray-400">Enter a URL to update your avatar preview above.</small>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 2: Change Password Form
    ═══════════════════════════════════════════════════════ --}}
    <div class="col-md-6">
        <div class="tw-card p-6 mb-6">
            <h2 class="text-lg font-bold mb-4" style="color:var(--primary)">
                <i class="fas fa-lock fa-fw me-2"></i>Change Password
            </h2>

            <form method="POST" action="{{ route('admin.v1.profile.change_password') }}?_method=PUT">
                @csrf
                <div class="grid gap-4">
                    <div>
                        <label class="form-label" for="current_password">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="form-label" for="password">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required minlength="8">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-gray-400">Minimum 8 characters.</small>
                    </div>

                    <div>
                        <label class="form-label" for="password_confirmation">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key fa-fw"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var picInput   = document.getElementById('picture');
    var avatarPrev = document.getElementById('avatar-preview');
    var initials   = document.getElementById('avatar-initials');

    if (picInput) {
        picInput.addEventListener('input', function () {
            var url = this.value.trim();
            if (url) {
                if (!avatarPrev) {
                    avatarPrev = document.createElement('img');
                    avatarPrev.id = 'avatar-preview';
                    avatarPrev.className = 'rounded-full object-cover';
                    avatarPrev.style.cssText = 'width:96px;height:96px;border:3px solid var(--primary)';
                    avatarPrev.onerror = function () { this.style.display = 'none'; };
                    if (initials && initials.parentNode) {
                        initials.parentNode.insertBefore(avatarPrev, initials);
                    }
                }
                avatarPrev.src = url;
                avatarPrev.style.display = '';
                if (initials) initials.style.display = 'none';
            } else {
                if (avatarPrev) avatarPrev.style.display = 'none';
                if (initials)   initials.style.display = '';
            }
        });
    }
})();
</script>
@endsection
