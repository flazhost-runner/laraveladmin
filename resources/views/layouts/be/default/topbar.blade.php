<div class="tw-card px-4 py-3 flex items-center justify-between mb-6 sticky top-0 z-20">
    <!-- Hamburger (mobile) -->
    <button id="tw-sidebar-toggle" class="md:hidden p-2 rounded hover:bg-gray-100">
        <i class="fas fa-bars text-gray-600"></i>
    </button>
    <!-- Home link -->
    <a href="{{ route('web.home.root') }}" class="hidden md:flex items-center gap-2 text-gray-500 hover:text-gray-700 text-sm">
        <i class="fas fa-home"></i> Home
    </a>

    <!-- User dropdown -->
    <div class="dropdown ml-auto">
        <button class="dropdown-toggle flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900" data-toggle-dd="user-dd">
            @if(auth_user()?->picture)
                <img src="{{ getFile(auth_user()->picture) }}" alt="user" class="w-8 h-8 rounded-full object-cover">
            @else
                <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:var(--primary)">
                    {{ strtoupper(substr(auth_user()?->name ?? 'U', 0, 1)) }}
                </span>
            @endif
            <span>Welcome, {{ auth_user()?->name ?? 'User' }}</span>
            <i class="fas fa-chevron-down text-xs"></i>
        </button>
        <div id="user-dd" class="dropdown-menu" style="min-width:180px">
            <a href="{{ route('admin.v1.profile.index') }}" class="dropdown-item">
                <i class="fas fa-user fa-fw me-2"></i> Profile
            </a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('web.auth.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt fa-fw me-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
