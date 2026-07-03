@extends('layouts.be.default.full-width')
@section('title', 'Reset Password')
@section('content')
<div class="tw-card w-full max-w-md overflow-hidden">
    <div class="p-8">
        <div class="mb-6 text-center">
            <img src="{{ $setting?->logo }}" alt="Logo" class="h-14 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold" style="color:var(--primary)">Reset Password</h1>
            <p class="text-sm text-gray-500 mt-1">Enter Your New Password</p>
        </div>
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('admin.v1.auth.reset.process') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="otp">OTP Code</label>
                <input type="text" id="otp" name="otp" class="form-control"
                       value="{{ old('otp') }}" maxlength="6" placeholder="6-digit OTP">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary-tw w-100 py-2 mb-3">Reset Password</button>
            <p class="text-center text-sm text-gray-500">
                <a href="{{ route('admin.v1.auth.reset.req') }}" class="text-primary-tw text-decoration-none">back?</a>
            </p>
        </form>
    </div>
</div>
@endsection
