@extends('layouts.administrator')
@section('title', 'Google Ads')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">MONETIZATION</span><h1>Google Ads</h1><p>Connect Google AdSense and control the responsive ad placements shown on the public home page.</p></div><a class="btn btn-outline-secondary" href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>Preview home page</a></div>

@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

<form method="POST" action="{{ route('admin.ads.update') }}">
    @csrf @method('PUT')
    <div class="ad-settings-grid">
        <div>
            <section class="dashboard-card mb-4">
                <div class="card-heading"><div><span>ADSENSE ACCOUNT</span><h2>Connection settings</h2></div><span class="account-status account-status--{{ $ads->enabled ? 'active' : 'archived' }}"><i></i>{{ $ads->enabled ? 'Enabled' : 'Disabled' }}</span></div>
                <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" role="switch" id="ads-enabled" name="enabled" value="1" @checked(old('enabled', $ads->enabled))><label class="form-check-label fw-semibold" for="ads-enabled">Serve Google ads on the home page</label></div>
                <label class="form-label" for="publisher-id">Publisher ID</label>
                <input class="form-control @error('publisher_id') is-invalid @enderror" id="publisher-id" name="publisher_id" value="{{ old('publisher_id', $ads->publisher_id) }}" placeholder="ca-pub-1234567890123456" autocomplete="off">
                @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Copy this from AdSense → Account → Settings → Account information.</div>
                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" role="switch" id="auto-ads" name="auto_ads_enabled" value="1" @checked(old('auto_ads_enabled', $ads->auto_ads_enabled))><label class="form-check-label" for="auto-ads"><strong>Enable Auto ads</strong><small class="d-block text-secondary">Google may add placements beyond the three manual units below.</small></label></div>
                <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" role="switch" id="placeholders" name="show_placeholders" value="1" @checked(old('show_placeholders', $ads->show_placeholders))><label class="form-check-label" for="placeholders"><strong>Show placeholders for incomplete slots</strong><small class="d-block text-secondary">Useful while designing; turn this off before launch if slots are intentionally empty.</small></label></div>
            </section>

            <section class="dashboard-card">
                <div class="card-heading"><div><span>MANUAL AD UNITS</span><h2>Home page placements</h2></div></div>
                @foreach([
                    ['homepage_top', 'Top banner', 'Below the hero section', 'bi-layout-text-window-reverse'],
                    ['homepage_middle', 'Content break', 'Between the process and audience sections', 'bi-layout-split'],
                    ['homepage_bottom', 'Bottom banner', 'Above the final community call-to-action', 'bi-layout-text-window'],
                ] as [$key, $name, $description, $icon])
                    <div class="ad-placement-row">
                        <span class="ad-placement-icon"><i class="bi {{ $icon }}"></i></span>
                        <div class="ad-placement-copy"><strong>{{ $name }}</strong><small>{{ $description }}</small></div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="{{ $key }}-enabled" name="{{ $key }}_enabled" value="1" @checked(old($key.'_enabled', $ads->{$key.'_enabled'}))><label class="visually-hidden" for="{{ $key }}-enabled">Enable {{ $name }}</label></div>
                        <div><label class="visually-hidden" for="{{ $key }}-slot">{{ $name }} slot ID</label><input class="form-control @error($key.'_slot') is-invalid @enderror" id="{{ $key }}-slot" name="{{ $key }}_slot" value="{{ old($key.'_slot', $ads->{$key.'_slot'}) }}" placeholder="Numeric slot ID">@error($key.'_slot')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                @endforeach
            </section>
        </div>

        <aside class="dashboard-card ad-help-card">
            <div class="card-heading"><div><span>SETUP CHECKLIST</span><h2>Before enabling ads</h2></div></div>
            <ol class="ad-checklist"><li><span>1</span><div><strong>Approve the domain</strong><small>Add your production domain under Sites in AdSense and wait until it is ready.</small></div></li><li><span>2</span><div><strong>Create display units</strong><small>Create three responsive Display ad units with clear placement names.</small></div></li><li><span>3</span><div><strong>Copy IDs only</strong><small>Enter the ca-pub publisher ID and each numeric data-ad-slot value. Never paste arbitrary scripts.</small></div></li><li><span>4</span><div><strong>Review consent</strong><small>Configure Google’s consent requirements for the regions where your visitors live.</small></div></li></ol>
            <div class="alert alert-warning small mb-0"><i class="bi bi-shield-exclamation me-1"></i>Ads appear only after Google approves the site and has inventory. Never click your own ads.</div>
        </aside>
    </div>
    <div class="permission-savebar"><span><i class="bi bi-info-circle"></i>Settings affect the public home page immediately.</span><button class="btn btn-portal" type="submit"><i class="bi bi-check2-circle me-1"></i>Save Google Ads settings</button></div>
</form>
@endsection
