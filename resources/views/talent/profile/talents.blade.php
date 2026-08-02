<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>BEYOND THE RÉSUMÉ</span><h2>Add talent</h2><p>Showcase creative, technical, sporting, communication, and other abilities.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.talent') }}">
            @csrf
            <div class="profile-form-grid">
                @include('talent.profile.master-select', ['name' => 'talent_id', 'label' => 'Talent', 'items' => $talents, 'value' => null])
                @include('talent.profile.master-select', ['name' => 'talent_category_id', 'label' => 'Talent category', 'items' => $talentCategories, 'value' => null])
                @include('talent.profile.master-select', ['name' => 'proficiency_level_id', 'label' => 'Proficiency level', 'items' => $proficiencyLevels, 'value' => null])
                <div><label class="form-label" for="years_practised">Years practised</label><input class="form-control" id="years_practised" type="number" min="0" max="100" step=".5" name="years_practised" value="{{ old('years_practised') }}"></div>
                <div class="profile-span"><label class="form-label" for="talent_achievements">Achievements</label><textarea class="form-control" id="talent_achievements" name="achievements" rows="4" maxlength="3000" placeholder="Performances, prizes, milestones, audiences, or other achievements">{{ old('achievements') }}</textarea></div>
                <div class="profile-span"><label class="form-label" for="talent_evidence_url">Evidence URL</label><input class="form-control" id="talent_evidence_url" type="url" name="evidence_url" maxlength="500" value="{{ old('evidence_url') }}" placeholder="https://portfolio.example.com/evidence"></div>
                <div class="profile-span">@include('talent.profile.switch', ['name' => 'is_featured', 'label' => 'Feature this talent on my profile', 'checked' => false])</div>
            </div>
            <button class="btn btn-portal mt-4"><i class="bi bi-plus-lg"></i> Add or update talent</button>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>TALENT PORTFOLIO</span><h2>Added talents</h2></div><b>{{ $records->count() }}</b></div>
        @forelse($records as $talent)
            @php
                $category = $talentCategories->firstWhere('id', $talent->pivot->talent_category_id);
                $proficiency = $proficiencyLevels->firstWhere('id', $talent->pivot->proficiency_level_id);
            @endphp
            <article class="education-record">
                <div class="education-record-head">
                    <div>
                        <strong>{{ $talent->display_name }} @if($talent->pivot->is_featured)<span class="badge text-bg-warning ms-1"><i class="bi bi-star-fill"></i> Featured</span>@endif</strong>
                        <span>{{ $category?->display_name ?? 'Uncategorised' }} @if($proficiency) · {{ $proficiency->display_name }} @endif</span>
                        @if($talent->pivot->years_practised !== null)<small>{{ (float) $talent->pivot->years_practised }} years practised</small>@endif
                    </div>
                    <form method="POST" action="{{ route('talent.profile.remove', ['talent', $talent]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $talent->display_name }}"><i class="bi bi-trash"></i></button></form>
                </div>
                @if($talent->pivot->achievements)<p>{{ $talent->pivot->achievements }}</p>@endif
                @if($talent->pivot->evidence_url)<a class="small" href="{{ $talent->pivot->evidence_url }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> View evidence</a>@endif
            </article>
        @empty
            <p class="text-muted mb-0">No talents added yet.</p>
        @endforelse
    </section>
</div>
