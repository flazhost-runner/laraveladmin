@extends('layouts.be.default.full-width')
@section('title', 'Login')
@section('content')
<div class="tw-card w-full max-w-5xl overflow-hidden grid md:grid-cols-2">
    <!-- Left: image panel -->
    <div class="sidebar-gradient hidden md:flex items-center justify-center p-10">
        <img src="{{ asset('modules/setting/login-image.png') }}" alt="Login" class="max-h-64 object-contain">
    </div>
    <!-- Right: form -->
    <div class="p-8">
        <div class="mb-6 text-center">
            <img src="{{ $setting?->logo }}" alt="Logo" class="h-14 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold" style="color:var(--primary)">Hello, Welcome Back!</h1>
            <p class="text-sm text-gray-500 mt-1">Enter your credentials to continue</p>
        </div>
        @if(!empty($errorMessages))
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errorMessages as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('web.auth.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="Email address" value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary-tw w-100 py-2 mb-3">Login</button>
            <div class="d-flex justify-content-between small mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Keep me logged in</label>
                </div>
                <a href="{{ route('admin.v1.auth.reset.req') }}" class="text-primary-tw text-decoration-none">Forgot password</a>
            </div>
            <hr class="my-4">
            <p class="text-center text-sm text-gray-500">
                Don't have an account?
                <a href="{{ route('web.auth.register') }}" class="text-primary-tw text-decoration-none fw-semibold">create here</a>
            </p>
        </form>
    </div>
</div>
@endsection
