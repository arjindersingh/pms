<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>INTERESTS</span><h2>Add hobby</h2><p>Share the activities and interests that are part of your life outside work.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.hobby') }}">
            @csrf
            <div class="profile-form-grid">
                @include('talent.profile.master-select', ['name' => 'hobby_id', 'label' => 'Hobby', 'items' => $hobbies, 'value' => null])
                @include('talent.profile.master-select', ['name' => 'hobby_category_id', 'label' => 'Hobby category', 'items' => $hobbyCategories, 'value' => null])
                @include('talent.profile.master-select', ['name' => 'interest_level_id', 'label' => 'Interest level', 'items' => $interestLevels, 'value' => null])
                <div><label class="form-label" for="years_active">Years active</label><input class="form-control" id="years_active" type="number" min="0" max="100" step=".5" name="years_active" value="{{ old('years_active') }}"></div>
                <div class="profile-span"><label class="form-label" for="hobby_description">Description</label><textarea class="form-control" id="hobby_description" name="description" rows="4" maxlength="3000" placeholder="How you participate, what you enjoy, or notable experiences">{{ old('description') }}</textarea></div>
            </div>
            <button class="btn btn-portal mt-4"><i class="bi bi-plus-lg"></i> Add or update hobby</button>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>MY INTERESTS</span><h2>Hobbies & interests</h2></div><b>{{ $records->count() }}</b></div>
        @forelse($records as $hobby)
            @php
                $category = $hobbyCategories->firstWhere('id', $hobby->pivot->hobby_category_id);
                $interest = $interestLevels->firstWhere('id', $hobby->pivot->interest_level_id);
            @endphp
            <article class="education-record">
                <div class="education-record-head">
                    <div><strong>{{ $hobby->display_name }}</strong><span>{{ $category?->display_name ?? 'Uncategorised' }} @if($interest) · {{ $interest->display_name }} @endif</span>@if($hobby->pivot->years_active !== null)<small>{{ (float) $hobby->pivot->years_active }} years active</small>@endif</div>
                    <form method="POST" action="{{ route('talent.profile.remove', ['hobby', $hobby]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $hobby->display_name }}"><i class="bi bi-trash"></i></button></form>
                </div>
                @if($hobby->pivot->description)<p class="mb-0">{{ $hobby->pivot->description }}</p>@endif
            </article>
        @empty
            <p class="text-muted mb-0">No hobbies added yet.</p>
        @endforelse
    </section>
</div>
