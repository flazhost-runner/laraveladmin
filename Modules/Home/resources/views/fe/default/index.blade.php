<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $setting?->name ?? 'LaravelAdmin' }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-white text-gray-900">

<!-- Header -->
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100 px-6 py-4 flex items-center justify-between">
    <span class="font-bold text-xl text-blue-600">{{ $setting?->name ?? 'LaravelAdmin' }}</span>
    <nav class="hidden md:flex gap-6 text-sm font-medium">
        <a href="#features" class="hover:text-blue-600">Features</a>
        <a href="#about" class="hover:text-blue-600">About</a>
        <a href="#contact" class="hover:text-blue-600">Contact</a>
    </nav>
    <a href="{{ route('web.auth.login') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Login</a>
</header>

<!-- Hero -->
<section class="py-24 px-6 text-center bg-gradient-to-br from-blue-50 to-indigo-100">
    <h1 class="text-5xl font-extrabold text-gray-900 mb-4">{{ $setting?->name ?? 'LaravelAdmin' }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-8">A production-ready admin bootstrap built with Laravel 13 & Tailwind CSS. Modular, secure, and extensible.</p>
    <a href="{{ route('web.auth.login') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-blue-700 transition">Get Started</a>
</section>

<!-- Stats -->
<section class="py-16 px-6 bg-white">
    <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        @foreach([['10+','Modules'],['100%','Laravel Native'],['9','Themes'],['RBAC','Route-Driven']] as $stat)
        <div>
            <div class="text-4xl font-extrabold text-blue-600">{{ $stat[0] }}</div>
            <div class="text-gray-500 mt-1">{{ $stat[1] }}</div>
        </div>
        @endforeach
    </div>
</section>

<!-- Features -->
<section id="features" class="py-16 px-6 bg-gray-50">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold text-center mb-10">Everything You Need</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['fa-shield-halved','RBAC Route-Driven','Permissions synced automatically from route registry.'],
                ['fa-palette','Theme Switcher','9 color themes, DB-driven, no rebuild needed.'],
                ['fa-code','DI + Service Layer','Clean architecture with interfaces and dependency injection.'],
                ['fa-lock','JWT + Session Auth','Dual auth: web sessions + JWT API with token blacklist.'],
                ['fa-layer-group','Modular','nwidart/laravel-modules for feature-per-module isolation.'],
                ['fa-vials','Tested','PHPUnit 12, feature tests for all critical paths.'],
            ] as $f)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <i class="fas {{ $f[0] }} text-blue-600 text-2xl mb-3 block"></i>
                <h3 class="font-bold text-lg mb-2">{{ $f[1] }}</h3>
                <p class="text-gray-500 text-sm">{{ $f[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- About -->
<section id="about" class="py-16 px-6 bg-white">
    <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-4">About</h2>
        @if($setting?->description)
            <div class="text-gray-600 prose mx-auto">{!! $setting->description !!}</div>
        @else
            <p class="text-gray-600">LaravelAdmin is a bootstrapped admin panel built to be a 1:1 conceptual port of NodeAdmin, using 100% Laravel 13 native idioms.</p>
        @endif
    </div>
</section>

<!-- Contact -->
<section id="contact" class="py-16 px-6 bg-gray-50">
    <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-8">Get In Touch</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @if($setting?->email)
            <div class="bg-white rounded-xl p-6 text-center"><i class="fas fa-envelope text-blue-600 text-xl mb-2"></i><p>{{ $setting->email }}</p></div>
            @endif
            @if($setting?->phone)
            <div class="bg-white rounded-xl p-6 text-center"><i class="fas fa-phone text-blue-600 text-xl mb-2"></i><p>{{ $setting->phone }}</p></div>
            @endif
            @if($setting?->address)
            <div class="bg-white rounded-xl p-6 text-center"><i class="fas fa-map-marker-alt text-blue-600 text-xl mb-2"></i><p>{{ $setting->address }}</p></div>
            @endif
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm">
    <p>&copy; {{ date('Y') }} {{ $setting?->copyright ?? $setting?->name ?? 'LaravelAdmin' }}. All rights reserved.</p>
</footer>

</body>
</html>
