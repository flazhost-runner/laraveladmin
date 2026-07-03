@include('layouts.be.default.head')
<body>
    @include('layouts.be.default.sidebar')

    <div class="md:ml-64 min-h-screen">
        <div class="p-4 md:p-6">
            @include('layouts.be.default.topbar')

            <!-- Flash messages → Toast -->
            @if(session('success'))
                <div id="toast-success" class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div id="toast-error" class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @include('layouts.be.default.foot')
</body>
</html>
