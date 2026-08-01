@php
    $portalArea ??= auth()->user()->userType->category->value;
    $portalTheme = config("portal.themes.{$portalArea}", config('portal.themes.administrator'));
    $portalNavigation = app(\App\Services\PortalAccess::class)->navigation(auth()->user());
    $errorSettings = (object) array_merge(config('error-display.defaults'), auth()->user()->errorSetting?->only(array_keys(config('error-display.defaults'))) ?? []);
    $thirdLevelMenus = $portalNavigation->flatMap(function ($module) {
        $secondLevelIds = $module->menus->whereNotNull('parent_id')->pluck('id');
        return $module->menus->whereIn('parent_id', $secondLevelIds);
    })->values();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-portal-area="{{ $portalArea }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · PlaceFlow</title>
    @fonts
    @livewireStyles
    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body" style="--portal-accent:{{ $portalTheme['accent'] }};--portal-accent-rgb:{{ $portalTheme['accent_rgb'] }};--portal-accent-dark:{{ $portalTheme['accent_dark'] }};--portal-sidebar:{{ $portalTheme['sidebar'] }};--portal-canvas:{{ $portalTheme['canvas'] }};--portal-font-size:{{ $portalTheme['font_size'] }};--portal-radius:{{ $portalTheme['radius'] }}"
      x-data="portalShell"
      x-init="applyTheme(theme)"
      :class="{ 'sidebar-is-open': sidebarOpen, 'sidebar-is-collapsed': sidebarCollapsed }">
    @include('portal.header')
    @include('portal.sidebar')

    <div class="portal-overlay" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"></div>

    <div class="portal-workspace">
        @if(($errors->any() || session()->has('error')) && $errorSettings->placement !== 'above_footer')
            @include('portal.error-display', ['settings' => $errorSettings])
        @endif
        <main class="portal-main">
            <div class="portal-content container-fluid">
                @if($thirdLevelMenus->isNotEmpty())
                    @include('portal.shortcuts', ['shortcuts' => $thirdLevelMenus])
                @endif
                @yield('content')
            </div>
        </main>
        @if(($errors->any() || session()->has('error')) && $errorSettings->placement === 'above_footer')
            @include('portal.error-display', ['settings' => $errorSettings])
        @endif
        @include('portal.footer')
    </div>
</body>
</html>
