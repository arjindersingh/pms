@php($membershipStatuses = ['active' => 'Active', 'pending' => 'Pending', 'expired' => 'Expired', 'suspended' => 'Suspended', 'inactive' => 'Inactive'])
<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>PROFESSIONAL AFFILIATIONS</span><h2>Add membership</h2><p>Record memberships in professional bodies, associations, councils, and institutes.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.membership') }}" enctype="multipart/form-data">
            @csrf
            <div class="profile-form-grid">
                <div><label class="form-label" for="membership_organization">Organization name</label><input class="form-control" id="membership_organization" name="organization_name" value="{{ old('organization_name') }}" required maxlength="200"></div>
                <div><label class="form-label" for="membership_type">Membership type</label><input class="form-control" id="membership_type" name="membership_type" value="{{ old('membership_type') }}" maxlength="150" placeholder="e.g. Fellow, Associate, Student"></div>
                <div><label class="form-label" for="membership_number">Membership number</label><input class="form-control" id="membership_number" name="membership_number" value="{{ old('membership_number') }}" maxlength="150"></div>
                <div><label class="form-label" for="membership_role">Candidate role</label><input class="form-control" id="membership_role" name="candidate_role" value="{{ old('candidate_role') }}" maxlength="150" placeholder="e.g. Member, Committee chair"></div>
                <div><label class="form-label" for="membership_started_on">Start date</label><input class="form-control" id="membership_started_on" type="date" name="started_on" value="{{ old('started_on') }}"></div>
                <div><label class="form-label" for="membership_expires_on">Expiry date</label><input class="form-control" id="membership_expires_on" type="date" name="expires_on" value="{{ old('expires_on') }}"></div>
                <div><label class="form-label" for="membership_status">Membership status</label><select class="form-select" id="membership_status" name="membership_status" required>@foreach($membershipStatuses as $value => $label)<option value="{{ $value }}" @selected(old('membership_status', 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label class="form-label" for="membership_document">Supporting document</label><input class="form-control" id="membership_document" type="file" name="supporting_document" accept="application/pdf,image/jpeg,image/png,image/webp,.doc,.docx"><small class="form-text">PDF, image, or Word document up to 10 MB.</small></div>
                <div class="profile-span">@include('talent.profile.switch', ['name' => 'is_lifetime', 'label' => 'Lifetime membership (no expiry date)', 'checked' => false])</div>
            </div>
            <button class="btn btn-portal mt-4"><i class="bi bi-plus-lg"></i> Add professional membership</button>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>AFFILIATIONS</span><h2>Professional memberships</h2></div><b>{{ $records->count() }}</b></div>
        @forelse($records as $membership)
            <article class="education-record">
                <div class="education-record-head">
                    <div>
                        <strong>{{ $membership->organization_name }}</strong>
                        <span>{{ $membership->membership_type ?: 'Membership' }} @if($membership->candidate_role) · {{ $membership->candidate_role }} @endif</span>
                        <small>@if($membership->membership_number)No. {{ $membership->membership_number }} · @endif{{ $membership->started_on?->format('M Y') ?? 'Start date not set' }} – {{ $membership->is_lifetime ? 'Lifetime' : ($membership->expires_on?->format('M Y') ?? 'No expiry date') }}</small>
                    </div>
                    <form method="POST" action="{{ route('talent.profile.remove', ['membership', $membership]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove membership"><i class="bi bi-trash"></i></button></form>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 small"><span class="badge {{ $membership->membership_status === 'active' ? 'text-bg-success' : ($membership->membership_status === 'expired' ? 'text-bg-secondary' : 'text-bg-warning') }}">{{ $membershipStatuses[$membership->membership_status] ?? ucfirst($membership->membership_status) }}</span>@if($membership->supporting_document_path)<a href="{{ Storage::url($membership->supporting_document_path) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> Supporting document</a>@endif</div>
            </article>
        @empty
            <p class="text-muted mb-0">No professional memberships added yet.</p>
        @endforelse
    </section>
</div>
