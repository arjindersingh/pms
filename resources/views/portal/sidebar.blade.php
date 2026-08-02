@php
    $activeModuleSlug = match ($portalArea) {
        'administrator' => 'administration',
        'recruiter' => 'recruitment',
        'talent' => 'career',
        default => null,
    };
    $sidebarNavigation = $activeModuleSlug
        ? $portalNavigation->where('slug', $activeModuleSlug)->values()
        : $portalNavigation;
@endphp
<aside class="portal-sidebar" @keydown.escape.window="sidebarOpen = false">
    <div class="sidebar-mobile-head d-lg-none"><span>Navigation</span><button type="button" @click="sidebarOpen = false"><i class="bi bi-x-lg"></i></button></div>
    <div class="sidebar-context"><span class="context-icon"><i class="bi {{ $sidebarNavigation->first()?->icon ?? 'bi-grid' }}"></i></span><span class="sidebar-label"><small>Workspace</small><strong>{{ $portalTheme['eyebrow'] }}</strong></span></div>
    <nav class="sidebar-nav">
        @foreach($sidebarNavigation as $module)
            <div class="sidebar-module">
                <div class="sidebar-section-label"><span class="sidebar-label">{{ $module->name }}</span></div>
                @foreach($module->menus->whereNull('parent_id') as $levelOne)
                    @php
                        $levelTwoMenus = $module->menus->where('parent_id', $levelOne->id);
                        $available = $levelOne->route_name && Route::has($levelOne->route_name);
                        $active = $available && request()->routeIs($levelOne->route_name);
                        $menuId = 'menu-'.$levelOne->id;
                    @endphp
                    @if($levelTwoMenus->isEmpty())
                        <a class="sidebar-link {{ $active ? 'active' : '' }} {{ $available ? '' : 'disabled' }}" href="{{ $available ? route($levelOne->route_name) : '#' }}" title="{{ $levelOne->name }}"><i class="bi {{ $levelOne->icon }}"></i><span class="sidebar-label">{{ $levelOne->name }}</span></a>
                    @else
                        <button class="sidebar-link sidebar-parent" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $menuId }}" aria-expanded="true" title="{{ $levelOne->name }}"><i class="bi {{ $levelOne->icon }}"></i><span class="sidebar-label">{{ $levelOne->name }}</span><i class="bi bi-chevron-down sidebar-chevron"></i></button>
                        <div class="collapse show sidebar-submenu" id="{{ $menuId }}">
                            @foreach($levelTwoMenus as $levelTwo)
                                @php($levelTwoAvailable = $levelTwo->route_name && Route::has($levelTwo->route_name))
                                <a class="sidebar-sublink {{ $levelTwoAvailable && request()->routeIs($levelTwo->route_name) ? 'active' : '' }} {{ $levelTwoAvailable ? '' : 'disabled' }}" href="{{ $levelTwoAvailable ? route($levelTwo->route_name) : '#' }}"><span></span><span class="sidebar-label">{{ $levelTwo->name }}</span></a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>
    <div class="sidebar-support"><i class="bi bi-stars"></i><div class="sidebar-label"><strong>Need a hand?</strong><small>Visit the help center</small></div><i class="bi bi-arrow-up-right sidebar-label"></i></div>
</aside>
