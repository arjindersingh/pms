<section class="content-shortcuts" aria-label="Quick actions">
    <div class="shortcut-heading"><div><span class="eyebrow">QUICK ACCESS</span><h2>Continue where you left off</h2></div><button class="btn btn-sm shortcut-customize" type="button"><i class="bi bi-sliders2 me-1"></i>Customize</button></div>
    <div class="shortcut-grid">
        @foreach($shortcuts as $shortcut)
            @php($available = $shortcut->route_name && Route::has($shortcut->route_name))
            <a class="shortcut-tile {{ $available ? '' : 'disabled' }}" href="{{ $available ? route($shortcut->route_name) : '#' }}"><span class="shortcut-icon"><i class="bi {{ $shortcut->icon }}"></i></span><span><strong>{{ $shortcut->name }}</strong><small>{{ $shortcut->parent?->name ?? 'Quick action' }}</small></span><i class="bi bi-arrow-up-right"></i></a>
        @endforeach
    </div>
</section>
