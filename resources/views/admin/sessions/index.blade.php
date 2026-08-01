@extends('layouts.administrator')
@section('title', 'Session Reports')
@section('content')
@php
    $formatDuration = fn (int $seconds) => $seconds < 60 ? $seconds.' sec' : ($seconds < 3600 ? intdiv($seconds, 60).' min' : intdiv($seconds, 3600).' hr '.intdiv($seconds % 3600, 60).' min');
@endphp
<div class="dashboard-heading"><div><span class="dashboard-kicker">SECURITY & ACTIVITY</span><h1>User session reports</h1><p>Review sign-ins, active sessions, devices, durations, and page activity across the portal.</p></div></div>

<div class="row g-3 mb-4">
    @foreach([
        ['All sessions', $summary['total'], 'bi-clock-history'],
        ['Active now', $summary['active'], 'bi-broadcast-pin'],
        ['Unique users', $summary['users'], 'bi-people'],
        ['Today', $summary['today'], 'bi-calendar-check'],
        ['Average duration', $formatDuration($summary['average_duration']), 'bi-stopwatch'],
    ] as [$label, $value, $icon])
        <div class="col-6 col-xl"><div class="metric-card account-stat"><span class="metric-icon"><i class="bi {{ $icon }}"></i></span><div><strong>{{ $value }}</strong><small>{{ $label }}</small></div></div></div>
    @endforeach
</div>

<div class="session-report-grid">
    <section class="dashboard-card">
        <form class="session-filters" method="GET">
            <div class="account-search"><i class="bi bi-search"></i><input name="search" value="{{ request('search') }}" placeholder="Name, email, or IP address…" aria-label="Search sessions"></div>
            <select class="form-select" name="user"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) request('user') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select>
            <select class="form-select" name="status"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="logged_out" @selected(request('status') === 'logged_out')>Logged out</option><option value="expired" @selected(request('status') === 'expired')>Expired</option></select>
            <select class="form-select" name="device"><option value="">All devices</option>@foreach(['desktop','mobile','tablet','bot'] as $device)<option value="{{ $device }}" @selected(request('device') === $device)>{{ ucfirst($device) }}</option>@endforeach</select>
            <select class="form-select" name="browser"><option value="">All browsers</option>@foreach($browsers as $browser)<option @selected(request('browser') === $browser)>{{ $browser }}</option>@endforeach</select>
            <select class="form-select" name="os"><option value="">All systems</option>@foreach($systems as $system)<option @selected(request('os') === $system)>{{ $system }}</option>@endforeach</select>
            <input class="form-control" type="date" name="from" value="{{ request('from') }}" aria-label="From date">
            <input class="form-control" type="date" name="to" value="{{ request('to') }}" aria-label="To date">
            <button class="btn btn-portal" type="submit">Apply</button>
            @if(request()->query())<a class="btn btn-portal-light" href="{{ route('admin.sessions.index') }}">Clear</a>@endif
        </form>

        <div class="table-responsive mt-3">
            <table class="table portal-table session-table align-middle">
                <thead><tr><th>User</th><th>Session started</th><th>Device</th><th>IP address</th><th>Duration</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td><div class="member-cell"><span>{{ collect(explode(' ', $session->user?->name ?? '?'))->map(fn($word) => mb_substr($word,0,1))->take(2)->implode('') }}</span><div><strong>{{ $session->user?->name ?? 'Deleted user' }}</strong><small>{{ $session->user?->email }}</small></div></div></td>
                        <td><strong>{{ $session->logged_in_at->format('M j, Y') }}</strong><small class="d-block text-secondary">{{ $session->logged_in_at->format('H:i:s') }} · {{ $session->logged_in_at->diffForHumans() }}</small></td>
                        <td><span class="session-device"><i class="bi {{ $session->device_type === 'mobile' ? 'bi-phone' : ($session->device_type === 'tablet' ? 'bi-tablet' : 'bi-display') }}"></i><span><strong>{{ $session->browser }} {{ $session->browser_version }}</strong><small>{{ $session->operating_system }} · {{ ucfirst($session->device_type) }}</small></span></span></td>
                        <td><code>{{ $session->ip_address ?? 'Unknown' }}</code></td>
                        <td>{{ $session->displayDuration() }}<small class="d-block text-secondary">{{ number_format($session->request_count) }} requests</small></td>
                        <td><span class="session-status session-status--{{ Str::slug($session->statusLabel()) }}"><i></i>{{ $session->statusLabel() }}</span></td>
                        <td class="text-end"><a class="btn btn-sm btn-portal-light" href="{{ route('admin.sessions.show', $session) }}">Details <i class="bi bi-arrow-right"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="account-empty"><i class="bi bi-clock-history"></i><strong>No sessions found</strong><span>Session history will appear after users sign in.</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="account-pagination">{{ $sessions->links() }}</div>
    </section>

    <aside class="dashboard-card session-breakdown">
        <div class="card-heading"><div><span>DEVICE MIX</span><h2>Sessions by device</h2></div></div>
        @php($deviceTotal = max(1, $deviceBreakdown->sum()))
        @forelse($deviceBreakdown as $device => $count)
            <div class="session-breakdown-row"><div><span>{{ ucfirst($device) }}</span><strong>{{ $count }}</strong></div><div class="progress"><div class="progress-bar" style="width:{{ round($count / $deviceTotal * 100) }}%"></div></div></div>
        @empty<p class="text-secondary small">No device data yet.</p>@endforelse
        <div class="session-privacy-note"><i class="bi bi-shield-check"></i><p><strong>Privacy-aware logging</strong><small>Passwords, cookies, request bodies, and URL query strings are never stored.</small></p></div>
    </aside>
</div>
@endsection
