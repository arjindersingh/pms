@php
    $recognitionKinds = ['award' => 'Award', 'honour' => 'Honour', 'scholarship' => 'Scholarship', 'competition' => 'Competition result'];
    $statusLabels = ['pending' => 'Pending verification', 'verified' => 'Verified', 'unverified' => 'Unverified', 'rejected' => 'Verification rejected'];
@endphp

<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>RECOGNITION</span><h2>Add award or achievement</h2><p>Record awards, honours, scholarships, and competition results.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.recognition') }}" enctype="multipart/form-data">
            @csrf
            <div class="profile-form-grid">
                <div><label class="form-label" for="recognition_kind">Record type</label><select class="form-select" id="recognition_kind" name="kind" required><option value="">Select record type</option>@foreach($recognitionKinds as $value => $label)<option value="{{ $value }}" @selected(old('kind') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label class="form-label" for="recognition_title">Award title</label><input class="form-control" id="recognition_title" name="title" value="{{ old('title') }}" required maxlength="200"></div>
                <div><label class="form-label" for="award_type">Award type</label><input class="form-control" id="award_type" name="award_type" value="{{ old('award_type') }}" maxlength="120" placeholder="e.g. Merit, Innovation, Sports"></div>
                <div><label class="form-label" for="issuing_organization">Issuing organization</label><input class="form-control" id="issuing_organization" name="issuing_organization" value="{{ old('issuing_organization') }}" maxlength="200"></div>
                @include('talent.profile.master-select', ['name' => 'recognition_level_id', 'label' => 'Level', 'items' => $recognitionLevels, 'value' => null])
                <div><label class="form-label" for="awarded_on">Award date</label><input class="form-control" id="awarded_on" type="date" name="awarded_on" max="{{ now()->format('Y-m-d') }}" value="{{ old('awarded_on') }}"></div>
                <div><label class="form-label" for="rank_position">Rank / Position</label><input class="form-control" id="rank_position" name="rank_position" value="{{ old('rank_position') }}" maxlength="100" placeholder="e.g. First place, Gold medal"></div>
                <div><label class="form-label" for="recognition_certificate">Certificate</label><input class="form-control" id="recognition_certificate" type="file" name="certificate" accept="application/pdf,image/jpeg,image/png,image/webp"><small class="form-text">PDF or image, up to 10 MB.</small></div>
                <div class="profile-span"><label class="form-label" for="recognition_description">Description</label><textarea class="form-control" id="recognition_description" name="description" rows="4" maxlength="3000">{{ old('description') }}</textarea></div>
                <div class="profile-span"><div class="alert alert-light border mb-0"><i class="bi bi-shield-check me-2"></i>New records start as <strong>Pending verification</strong>. Verified status can only be assigned through the review process.</div></div>
            </div>
            <button class="btn btn-portal mt-4"><i class="bi bi-plus-lg"></i> Add award or achievement</button>
        </form>
    </section>

    @foreach($recognitionKinds as $kind => $heading)
        @php($kindRecords = $records->where('kind', $kind))
        <section class="dashboard-card">
            <div class="card-heading"><div><span>{{ strtoupper(Str::plural($heading)) }}</span><h2>{{ Str::plural($heading) }}</h2></div><b>{{ $kindRecords->count() }}</b></div>
            @forelse($kindRecords as $record)
                <article class="education-record">
                    <div class="education-record-head">
                        <div>
                            <strong>{{ $record->title }}</strong>
                            <span>{{ $record->award_type ?: $heading }} @if($record->level) · {{ $record->level->display_name }} @endif</span>
                            <small>{{ $record->issuing_organization ?: 'Issuing organization not specified' }} @if($record->awarded_on) · {{ $record->awarded_on->format('M j, Y') }} @endif @if($record->rank_position) · {{ $record->rank_position }} @endif</small>
                        </div>
                        <form method="POST" action="{{ route('talent.profile.remove', ['recognition', $record]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $record->title }}"><i class="bi bi-trash"></i></button></form>
                    </div>
                    @if($record->description)<p>{{ $record->description }}</p>@endif
                    <div class="d-flex flex-wrap align-items-center gap-3 mt-2 small">
                        <span class="badge {{ $record->verification_status === 'verified' ? 'text-bg-success' : ($record->verification_status === 'rejected' ? 'text-bg-danger' : 'text-bg-secondary') }}"><i class="bi bi-patch-check me-1"></i>{{ $statusLabels[$record->verification_status] ?? ucfirst($record->verification_status) }}</span>
                        @if($record->certificate_path)<a href="{{ Storage::url($record->certificate_path) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-check"></i> View certificate</a>@endif
                    </div>
                </article>
            @empty
                <p class="text-muted mb-0">No {{ Str::lower(Str::plural($heading)) }} added yet.</p>
            @endforelse
        </section>
    @endforeach
</div>
