<header class="portal-header">
    <div class="header-left">
        <button class="header-action d-lg-none" type="button" @click="sidebarOpen = true" aria-label="Open navigation"><i class="bi bi-list"></i></button>
        <button class="header-action d-none d-lg-grid" type="button" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('portal-sidebar-collapsed', sidebarCollapsed)" aria-label="Collapse navigation"><i class="bi bi-layout-sidebar-inset"></i></button>
        <a class="portal-brand" href="{{ route(auth()->user()->dashboardRoute()) }}"><span class="portal-brand-mark"><i class="bi bi-mortarboard-fill"></i></span><span class="portal-brand-copy"><strong>PlaceFlow</strong><small>{{ $portalTheme['label'] }}</small></span></a>
    </div>

    <div class="header-center d-none d-md-flex">
        <i class="bi bi-search"></i><input type="search" placeholder="Search anything…" aria-label="Search"><kbd>⌘ K</kbd>
    </div>

    <div class="header-right">
        <div class="dropdown">
            <button class="header-action" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Change theme" title="Theme"><i class="bi bi-palette"></i></button>
            <div class="dropdown-menu dropdown-menu-end theme-menu border-0 shadow-lg p-2">
                <div class="dropdown-heading"><strong>Interface theme</strong><small>Saved on this device</small></div>
                <template x-for="option in themeOptions" :key="option.key">
                    <button class="dropdown-item theme-option" type="button" @click="setTheme(option.key)" :class="{ 'active': theme === option.key }">
                        <span class="theme-swatch" :style="`--swatch:${option.accent};--swatch-dark:${option.sidebar}`"></span>
                        <span><strong x-text="option.name"></strong><small x-text="option.description"></small></span>
                        <i class="bi bi-check-lg ms-auto" x-show="theme === option.key"></i>
                    </button>
                </template>
            </div>
        </div>
        <div class="dropdown">
            <button class="header-action module-launcher" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open modules"><i class="bi bi-grid-3x3-gap-fill"></i></button>
            <div class="dropdown-menu dropdown-menu-end module-menu border-0 shadow-lg p-3">
                <div class="dropdown-heading"><strong>Modules</strong><small>Your available workspaces</small></div>
                <div class="module-grid">
                    @foreach($portalNavigation as $module)
                        @php($moduleRoute = $module->menus->first(fn ($menu) => $menu->route_name && Route::has($menu->route_name))?->route_name)
                        <a class="module-tile {{ $moduleRoute ? '' : 'disabled' }}" href="{{ $moduleRoute ? route($moduleRoute) : '#' }}">
                            <span><i class="bi {{ $module->icon }}"></i></span><small>{{ $module->name }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <button class="header-action position-relative" type="button" aria-label="Notifications"><i class="bi bi-bell"></i><span class="notification-dot"></span></button>
        <div class="dropdown">
            <button class="profile-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="profile-avatar">{{ collect(explode(' ', auth()->user()->name))->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('') }}</span>
                <span class="profile-copy d-none d-md-block"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->userType->name }}</small></span>
                <i class="bi bi-chevron-down d-none d-md-inline"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end profile-menu border-0 shadow-lg p-2">
                <div class="profile-menu-head"><span class="profile-avatar large">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div></div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('account.profile') }}"><i class="bi bi-person"></i>Profile</a>
                <a class="dropdown-item" href="{{ route('account.settings') }}"><i class="bi bi-gear"></i>Account settings</a>
                <a class="dropdown-item" href="{{ route('account.password') }}"><i class="bi bi-key"></i>Change password</a>
                <a class="dropdown-item" href="{{ route('account.error-settings') }}"><i class="bi bi-exclamation-diamond"></i>Error settings</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i>Sign out</button></form>
            </div>
        </div>
    </div>
</header>
