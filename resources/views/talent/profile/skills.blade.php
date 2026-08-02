<div class="profile-record-grid">
    <section class="dashboard-card">
        <div class="card-heading">
            <div><h2>Candidate skills</h2><p>Add skill-specific experience and proficiency details.</p></div>
        </div>

        @forelse($profile->skills as $item)
            @php
                $group = $skillGroups->firstWhere('id', $item->pivot->skill_group_id);
                $proficiency = $proficiencyLevels->firstWhere('id', $item->pivot->proficiency_level_id);
            @endphp
            <div class="profile-record align-items-start">
                <div>
                    <strong>{{ $item->display_name }} @if($item->pivot->is_primary)<span class="badge text-bg-primary ms-1">Primary</span>@endif</strong>
                    <span class="text-muted small">
                        {{ $group?->display_name ?? 'Ungrouped' }}
                        @if($proficiency) · {{ $proficiency->display_name }} @endif
                        @if($item->pivot->years_experience !== null) · {{ (float) $item->pivot->years_experience }} years @endif
                    </span>
                    @if($item->pivot->remarks)<small class="d-block mt-1">{{ $item->pivot->remarks }}</small>@endif
                </div>
                <form method="POST" action="{{ route('talent.profile.remove', ['skill', $item]) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $item->display_name }}"><i class="bi bi-x"></i></button>
                </form>
            </div>
        @empty
            <p class="text-muted">No skills added yet.</p>
        @endforelse

        <form method="POST" action="{{ route('talent.profile.skill') }}" class="mt-4">
            @csrf
            <div class="profile-form-grid">
                @include('talent.profile.master-select', ['name' => 'skill_group_id', 'label' => 'Skill group', 'items' => $skillGroups, 'value' => null])
                @include('talent.profile.master-select', ['name' => 'skill_id', 'label' => 'Skill', 'items' => $skills, 'value' => null])
                @include('talent.profile.master-select', ['name' => 'proficiency_level_id', 'label' => 'Proficiency level', 'items' => $proficiencyLevels, 'value' => null])
                <div><label class="form-label" for="years_experience">Years of experience</label><input class="form-control" id="years_experience" type="number" step=".5" min="0" max="70" name="years_experience" value="{{ old('years_experience') }}"></div>
                <div class="profile-span"><label class="form-label" for="remarks">Special remarks</label><textarea class="form-control" id="remarks" name="remarks" rows="3" maxlength="2000" placeholder="Projects, certifications, specialist knowledge, or other relevant details">{{ old('remarks') }}</textarea></div>
                <div class="profile-span">@include('talent.profile.switch', ['name' => 'is_primary', 'label' => 'Mark as a primary skill', 'checked' => false])</div>
            </div>
            <button class="btn btn-portal mt-3"><i class="bi bi-plus-lg"></i> Add or update skill</button>
        </form>
    </section>

    <section class="dashboard-card">
        <h2>Languages</h2>
        @foreach($profile->languages as $item)
            <div class="profile-record"><strong>{{ $item->display_name }}</strong><form method="POST" action="{{ route('talent.profile.remove', ['language', $item]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $item->display_name }}"><i class="bi bi-x"></i></button></form></div>
        @endforeach
        <form method="POST" action="{{ route('talent.profile.language') }}" class="mt-4">
            @csrf
            @include('talent.profile.master-select', ['name' => 'language_id', 'label' => 'Language', 'items' => $languages, 'value' => null])
            <div class="mt-3">@include('talent.profile.master-select', ['name' => 'proficiency_level_id', 'label' => 'Proficiency', 'items' => $proficiencyLevels, 'value' => null])</div>
            @include('talent.profile.switch', ['name' => 'is_native', 'label' => 'Native language', 'checked' => false])
            <button class="btn btn-portal mt-3">Add language</button>
        </form>
    </section>
</div>
