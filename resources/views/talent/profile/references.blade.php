<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>ENDORSEMENTS</span><h2>Add reference</h2><p>Only add people who have agreed to be listed and contacted as a reference.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.reference') }}">
            @csrf
            <div class="profile-form-grid">
                @include('talent.profile.master-select', ['name' => 'reference_type_id', 'label' => 'Reference type', 'items' => $referenceTypes, 'value' => null])
                <div><label class="form-label" for="reference_name">Name</label><input class="form-control" id="reference_name" name="name" value="{{ old('name') }}" required maxlength="150"></div>
                <div><label class="form-label" for="reference_designation">Designation</label><input class="form-control" id="reference_designation" name="designation" value="{{ old('designation') }}" maxlength="150"></div>
                <div><label class="form-label" for="reference_organization">Organization</label><input class="form-control" id="reference_organization" name="organization" value="{{ old('organization') }}" maxlength="200"></div>
                <div><label class="form-label" for="reference_relationship">Relationship with candidate</label><input class="form-control" id="reference_relationship" name="relationship_to_candidate" value="{{ old('relationship_to_candidate') }}" maxlength="150" placeholder="e.g. Former manager, Professor"></div>
                <div><label class="form-label" for="reference_years_known">Years known</label><input class="form-control" id="reference_years_known" type="number" min="0" max="100" step=".5" name="years_known" value="{{ old('years_known') }}"></div>
                <div><label class="form-label" for="reference_email">Email</label><input class="form-control" id="reference_email" type="email" name="email" value="{{ old('email') }}" maxlength="255"></div>
                <div><label class="form-label" for="reference_mobile">Mobile number</label><input class="form-control" id="reference_mobile" type="tel" name="mobile" value="{{ old('mobile') }}" maxlength="30"></div>
                <div class="profile-span profile-switches">
                    @include('talent.profile.switch', ['name' => 'consent_received', 'label' => 'Consent received', 'checked' => false])
                    @include('talent.profile.switch', ['name' => 'permission_to_contact', 'label' => 'Permission to contact', 'checked' => false])
                    @include('talent.profile.switch', ['name' => 'is_primary', 'label' => 'Primary reference', 'checked' => false])
                </div>
                <div class="profile-span"><div class="alert alert-light border mb-0"><i class="bi bi-shield-lock me-2"></i>Contact permission can only be enabled after confirming that consent was received.</div></div>
            </div>
            <button class="btn btn-portal mt-4"><i class="bi bi-person-plus"></i> Add reference</button>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>REFERENCE LIST</span><h2>Candidate references</h2></div><b>{{ $records->count() }}</b></div>
        @forelse($records as $reference)
            <article class="education-record">
                <div class="education-record-head">
                    <div>
                        <strong>{{ $reference->name }} @if($reference->is_primary)<span class="badge text-bg-primary ms-1">Primary</span>@endif</strong>
                        <span>{{ $reference->designation ?: $reference->type?->display_name ?? 'Reference' }} @if($reference->organization) · {{ $reference->organization }} @endif</span>
                        <small>{{ $reference->type?->display_name ?? 'Unspecified type' }} @if($reference->relationship_to_candidate) · {{ $reference->relationship_to_candidate }} @endif @if($reference->years_known !== null) · Known {{ (float) $reference->years_known }} years @endif</small>
                    </div>
                    <form method="POST" action="{{ route('talent.profile.remove', ['reference', $reference]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $reference->name }}"><i class="bi bi-trash"></i></button></form>
                </div>
                <div class="d-flex flex-wrap gap-3 small">
                    @if($reference->email)<span><i class="bi bi-envelope"></i> {{ $reference->email }}</span>@endif
                    @if($reference->mobile)<span><i class="bi bi-telephone"></i> {{ $reference->mobile }}</span>@endif
                    <span class="badge {{ $reference->consent_received ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $reference->consent_received ? 'Consent received' : 'Consent not recorded' }}</span>
                    <span class="badge {{ $reference->permission_to_contact ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $reference->permission_to_contact ? 'Contact permitted' : 'Do not contact' }}</span>
                </div>
            </article>
        @empty
            <p class="text-muted mb-0">No references added yet.</p>
        @endforelse
    </section>
</div>
