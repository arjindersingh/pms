@extends('layouts.administrator')
@section('title', 'Session Details')
@section('content')
<div class="dashboard-heading"><div><a class="account-back" href="{{ route('admin.sessions.index') }}"><i class="bi bi-arrow-left"></i> Session reports</a><span class="dashboard-kicker d-block mt-3">SESSION #{{ $session->id }}</span><h1>{{ $session->user?->name ?? 'Deleted user' }}</h1><p>{{ $session->logged_in_at->format('M j, Y H:i:s') }} · {{ $session->displayDuration() }}</p></div><span class="session-status session-status--{{ Str::slug($session->statusLabel()) }}"><i></i>{{ $session->statusLabel() }}</span></div>

<div class="session-detail-grid">
    <div>
        <section class="dashboard-card mb-4">
            <div class="card-heading"><div><span>CONNECTION</span><h2>Session details</h2></div></div>
            <dl class="session-detail-list">
                @foreach([
                    ['User', ($session->user?->name ?? 'Deleted user').' · '.($session->user?->email ?? '')],
                    ['IP address', $session->ip_address ?? 'Unknown'],
                    ['Browser', trim($session->browser.' '.$session->browser_version)],
                    ['Operating system', $session->operating_system],
                    ['Device', ucfirst($session->device_type).($session->device_name ? ' · '.$session->device_name : '')],
                    ['Locale', $session->locale ?? 'Unknown'],
                    ['Login method', ucfirst($session->login_method).($session->remembered ? ' · Remember me' : '')],
                    ['Referrer', $session->referrer_host ?? 'Direct / unavailable'],
                    ['Last route', $session->last_route ?? $session->last_path ?? 'None'],
                    ['End reason', $session->ended_reason ? ucfirst($session->ended_reason) : ($session->isActive() ? 'Still active' : 'Idle timeout')],
                ] as [$term, $value])<div><dt>{{ $term }}</dt><dd>{{ $value }}</dd></div>@endforeach
            </dl>
        </section>
        <section class="dashboard-card">
            <div class="card-heading"><div><span>USER AGENT</span><h2>Raw client identification</h2></div></div>
            <code class="session-user-agent">{{ $session->user_agent ?? 'Not supplied' }}</code>
        </section>
    </div>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>REQUEST HISTORY</span><h2>Activity timeline</h2></div><small>{{ number_format($session->request_count) }} total</small></div>
        <div class="session-activity-list">
            @forelse($activities as $activity)
                <div><span class="request-method request-method--{{ strtolower($activity->method) }}">{{ $activity->method }}</span><div><strong>{{ $activity->route_name ?? $activity->path }}</strong><small>{{ $activity->path }}</small></div><div><span class="response-code">{{ $activity->response_status }}</span><time>{{ $activity->occurred_at->format('M j, H:i:s') }}</time></div></div>
            @empty<div class="account-empty"><i class="bi bi-list"></i><strong>No request activity</strong></div>@endforelse
        </div>
        <div class="account-pagination">{{ $activities->links() }}</div>
    </section>
</div>
@endsection
