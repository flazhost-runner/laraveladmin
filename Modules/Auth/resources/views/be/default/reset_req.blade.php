@extends('layouts.be.default.full-width')
@section('title', 'Forgot Password')
@section('content')
<div class="tw-card w-full max-w-md overflow-hidden">
    <div class="p-8">
        <div class="mb-6 text-center">
            <img src="{{ getFile($setting?->logo) }}" alt="Logo" class="h-14 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold" style="color:var(--primary)">Forgot Password</h1>
            <p class="text-sm text-gray-500 mt-1">Enter your Email to continue</p>
        </div>
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('admin.v1.auth.reset.request') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="Email address" value="{{ old('email') }}">
            </div>
            <button type="submit" class="btn btn-primary-tw w-100 py-2 mb-3">Send OTP</button>
            <p class="text-center text-sm text-gray-500">
                <a href="{{ route('web.auth.login') }}" class="text-primary-tw text-decoration-none">back?</a>
            </p>
        </form>
    </div>
</div>
@endsection
