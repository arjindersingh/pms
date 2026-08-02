@php
    $auditHistory = $profile->declarations->map(fn ($record) => ['kind' => 'Declaration', 'record' => $record])
        ->concat($profile->consentRecords->map(fn ($record) => ['kind' => 'Consent', 'record' => $record]))
        ->sortByDesc(fn ($item) => $item['record']->created_at)->values();
@endphp
<div class="education-stack">
    <section class="dashboard-card">
        <div class="card-heading"><div><span>DECLARATIONS</span><h2>Candidate declarations</h2><p>Confirm statements about the accuracy and authenticity of your profile.</p></div><span class="badge text-bg-light border">Version 1.0</span></div>
        @foreach($declarationTypes as $type)
            @php($latest = $profile->declarations->where('declaration_type_id', $type->id)->sortByDesc('id')->first())
            <div class="profile-record align-items-center">
                <div><strong>{{ $type->display_name }}</strong><small class="d-block text-muted mt-1">
                    @if($latest)
                        Last response: {{ $latest->is_accepted ? 'Accepted' : 'Not accepted' }} · {{ $latest->created_at->format('M j, Y g:i A') }}
                    @else
                        No response recorded
                    @endif
                </small></div>
                <form method="POST" action="{{ route('talent.profile.declaration') }}" class="d-flex gap-2">@csrf<input type="hidden" name="declaration_type_id" value="{{ $type->id }}"><button class="btn btn-sm btn-outline-secondary" name="is_accepted" value="0">Not accepted</button><button class="btn btn-sm btn-success" name="is_accepted" value="1"><i class="bi bi-check-lg"></i> Accept</button></form>
            </div>
        @endforeach
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>CONSENT</span><h2>Consent records</h2><p>Manage permissions and policy acknowledgements. Every change creates a new audit record.</p></div><span class="badge text-bg-light border">Version 1.0</span></div>
        @foreach($consentTypes as $type)
            @php($latest = $profile->consentRecords->where('consent_type_id', $type->id)->sortByDesc('id')->first())
            <div class="profile-record align-items-center">
                <div><strong>{{ $type->display_name }}</strong><small class="d-block text-muted mt-1">
                    @if($latest)
                        Current response: {{ $latest->is_accepted ? 'Allowed / accepted' : 'Declined / withdrawn' }} · {{ $latest->created_at->format('M j, Y g:i A') }}
                    @else
                        No response recorded
                    @endif
                </small></div>
                <form method="POST" action="{{ route('talent.profile.consent') }}" class="d-flex gap-2">@csrf<input type="hidden" name="consent_type_id" value="{{ $type->id }}"><button class="btn btn-sm btn-outline-secondary" name="is_accepted" value="0">Decline</button><button class="btn btn-sm btn-success" name="is_accepted" value="1"><i class="bi bi-check-lg"></i> Accept</button></form>
            </div>
        @endforeach
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>AUDIT TRAIL</span><h2>Declaration and consent history</h2><p>These records are retained as an immutable history.</p></div><b>{{ $auditHistory->count() }}</b></div>
        @forelse($auditHistory as $item)
            @php($record = $item['record'])
            <div class="profile-record align-items-start">
                <div>
                    <strong>{{ $record->type?->display_name ?? $item['kind'] }}</strong>
                    <span class="d-block small mt-1"><span class="badge {{ $record->is_accepted ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $record->is_accepted ? 'Accepted' : 'Not accepted' }}</span> · Version {{ $record->declaration_version }} · {{ $record->created_at->format('M j, Y g:i:s A') }}</span>
                    <small class="d-block text-muted mt-1">Accepted date: {{ $record->accepted_at?->format('M j, Y g:i:s A') ?? 'Not applicable' }} · IP: {{ $record->ip_address ?: 'Unavailable' }}</small>
                    @if($record->user_agent)<small class="d-block text-muted text-break mt-1">User agent: {{ $record->user_agent }}</small>@endif
                </div>
                <span class="badge text-bg-light border">{{ $item['kind'] }}</span>
            </div>
        @empty
            <p class="text-muted mb-0">No declaration or consent responses recorded yet.</p>
        @endforelse
    </section>
</div>
