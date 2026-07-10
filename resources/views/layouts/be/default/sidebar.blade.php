<!-- Sidebar -->
<div id="tw-sidebar" class="sidebar-gradient fixed left-0 top-0 h-full w-64 z-40 transition-transform duration-300 -translate-x-full md:translate-x-0 flex flex-col">
    <!-- Brand -->
    <div class="px-4 py-4 border-b border-white/10">
        <a href="{{ route('admin.v1.dashboard.index') }}" class="flex items-center gap-2">
            @if($setting?->logo)
                <img src="{{ getFile($setting->logo) }}" alt="Logo" class="h-8 w-8 rounded object-cover">
            @else
                <i class="fas fa-chart-line text-white text-xl"></i>
            @endif
            <span class="text-white font-bold text-lg truncate">{{ $setting?->name ?? 'LaravelAdmin' }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('admin.v1.dashboard.index') }}"
           class="nav-link-tw {{ request()->routeIs('admin.v1.dashboard.index') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt fa-fw"></i> Dashboard
        </a>

        <!-- UI Components -->
        @if(hasAccess('admin.v1.components.index', 'GET'))
        <a href="{{ route('admin.v1.components.index') }}"
           class="nav-link-tw {{ request()->routeIs('admin.v1.components.index') ? 'active' : '' }}">
            <i class="fas fa-cubes fa-fw"></i> UI Components
        </a>
        @endif

        <!-- Maintenance section -->
        @php
            $showMaint = hasAccess('admin.v1.access.permission.index', 'GET')
                      || hasAccess('admin.v1.access.role.index', 'GET')
                      || hasAccess('admin.v1.access.user.index', 'GET')
                      || hasAccess('admin.v1.setting.index', 'GET');
        @endphp
        @if($showMaint)
        <div class="mt-4 mb-1 px-2 text-xs text-white/50 uppercase tracking-wider font-semibold">Maintenance</div>
        @endif

        @if(hasAccess('admin.v1.access.permission.index', 'GET'))
        <a href="{{ route('admin.v1.access.permission.index') }}"
           class="nav-link-tw {{ request()->routeIs('admin.v1.access.permission.*') ? 'active' : '' }}">
            <i class="fas fa-key fa-fw"></i> Permission
        </a>
        @endif

        @if(hasAccess('admin.v1.access.role.index', 'GET'))
        <a href="{{ route('admin.v1.access.role.index') }}"
           class="nav-link-tw {{ request()->routeIs('admin.v1.access.role.*') ? 'active' : '' }}">
            <i class="fas fa-user-shield fa-fw"></i> Role
        </a>
        @endif

        @if(hasAccess('admin.v1.access.user.index', 'GET'))
        <a href="{{ route('admin.v1.access.user.index') }}"
           class="nav-link-tw {{ request()->routeIs('admin.v1.access.user.*') ? 'active' : '' }}">
            <i class="fas fa-users fa-fw"></i> User
        </a>
        @endif

        @if(hasAccess('admin.v1.setting.index', 'GET'))
        <a href="{{ route('admin.v1.setting.index') }}"
           class="nav-link-tw {{ request()->routeIs('admin.v1.setting.index') ? 'active' : '' }}">
            <i class="fas fa-cog fa-fw"></i> Setting
        </a>
        @endif
    </nav>

    <!-- Footer -->
    <div class="px-4 py-3 border-t border-white/10 text-xs text-white/50">
        &copy; {{ date('Y') }} {{ $setting?->copyright ?? $setting?->name ?? 'LaravelAdmin' }}
    </div>
</div>

<!-- Sidebar overlay (mobile) -->
<div id="tw-sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden"></div>
