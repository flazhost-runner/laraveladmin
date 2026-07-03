@extends('layouts.be.default.full-width')
@section('title', 'Register')
@section('content')
<div class="tw-card w-full max-w-5xl overflow-hidden grid md:grid-cols-2">
    <!-- Left: image panel -->
    <div class="sidebar-gradient hidden md:flex items-center justify-center p-10">
        <img src="{{ asset('modules/setting/login-image.png') }}" alt="Register" class="max-h-64 object-contain">
    </div>
    <!-- Right: form -->
    <div class="p-8">
        <div class="mb-6 text-center">
            <img src="{{ $setting?->logo }}" alt="Logo" class="h-14 mx-auto object-contain mb-4">
            <h1 class="text-2xl font-bold" style="color:var(--primary)">Create Account</h1>
            <p class="text-sm text-gray-500 mt-1">Fill the form to register</p>
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
        <form method="POST" action="{{ route('web.auth.register.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name') }}" autocomplete="name">
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email') }}" autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" autocomplete="password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary-tw w-100 py-2 mb-3">Create Account</button>
            <hr class="my-4">
            <p class="text-center text-sm text-gray-500">
                Already have an account?
                <a href="{{ route('web.auth.login') }}" class="text-primary-tw text-decoration-none fw-semibold">login here</a>
            </p>
        </form>
    </div>
</div>
@endsection
