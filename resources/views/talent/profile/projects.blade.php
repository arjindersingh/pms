<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>PORTFOLIO</span><h2>Add project</h2><p>Document your work, contribution, collaborators, and supporting evidence.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.project') }}" enctype="multipart/form-data" x-data="{ members: [{}] }">
            @csrf
            <div class="profile-form-grid">
                <div><label class="form-label" for="project_title">Project title</label><input class="form-control" id="project_title" name="title" value="{{ old('title') }}" required maxlength="200"></div>
                @include('talent.profile.master-select', ['name' => 'project_type_id', 'label' => 'Project type', 'items' => $projectTypes, 'value' => null])
                <div><label class="form-label" for="candidate_role">Candidate role</label><input class="form-control" id="candidate_role" name="candidate_role" value="{{ old('candidate_role') }}" maxlength="150"></div>
                <div><label class="form-label" for="organization_client">Organization / Client</label><input class="form-control" id="organization_client" name="organization_client" value="{{ old('organization_client') }}" maxlength="200"></div>
                <div><label class="form-label" for="team_size">Team size</label><input class="form-control" id="team_size" type="number" min="1" max="10000" name="team_size" value="{{ old('team_size') }}"></div>
                <div><label class="form-label" for="project_skills">Project skills</label><select class="form-select" id="project_skills" name="skills[]" multiple size="6">@foreach($skills as $skill)<option value="{{ $skill->id }}" @selected(in_array($skill->id, old('skills', [])))>{{ $skill->display_name }}</option>@endforeach</select><small class="form-text">Select all skills used in this project.</small></div>
                <div><label class="form-label" for="project_started_on">Start date</label><input class="form-control" id="project_started_on" type="date" name="started_on" value="{{ old('started_on') }}"></div>
                <div><label class="form-label" for="project_ended_on">End date</label><input class="form-control" id="project_ended_on" type="date" name="ended_on" value="{{ old('ended_on') }}"></div>
                <div class="profile-span profile-switches">
                    @include('talent.profile.switch', ['name' => 'currently_active', 'label' => 'Currently active', 'checked' => false])
                    @include('talent.profile.switch', ['name' => 'is_featured', 'label' => 'Feature this project', 'checked' => false])
                </div>
                @foreach([['description','Description'],['objectives','Objectives'],['candidate_contribution','Candidate contribution'],['outcome','Outcome']] as [$field,$label])
                    <div class="profile-span"><label class="form-label" for="project_{{ $field }}">{{ $label }}</label><textarea class="form-control" id="project_{{ $field }}" name="{{ $field }}" rows="3" maxlength="5000">{{ old($field) }}</textarea></div>
                @endforeach
                @foreach([['project_url','Project URL'],['repository_url','Repository URL'],['demo_url','Demo URL']] as [$field,$label])
                    <div><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control" id="{{ $field }}" type="url" name="{{ $field }}" value="{{ old($field) }}" placeholder="https://"></div>
                @endforeach
                <div><label class="form-label" for="project_screenshots">Screenshots</label><input class="form-control" id="project_screenshots" type="file" name="screenshots[]" accept="image/jpeg,image/png,image/webp" multiple><small class="form-text">Up to 10 images, 5 MB each.</small></div>
                <div><label class="form-label" for="project_documents">Supporting documents</label><input class="form-control" id="project_documents" type="file" name="supporting_documents[]" multiple><small class="form-text">Up to 10 documents, 10 MB each.</small></div>
            </div>

            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-between"><div><h3 class="h6 mb-1">Project team members</h3><small class="text-muted">Add collaborators connected with this project.</small></div><button class="btn btn-sm btn-outline-primary" type="button" @click="members.push({})"><i class="bi bi-person-plus"></i> Add member</button></div>
                <template x-for="(member, index) in members" :key="index">
                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-3"><label class="form-label">Name</label><input class="form-control" :name="`team_members[${index}][name]`" maxlength="150"></div>
                        <div class="col-md-3"><label class="form-label">Role</label><input class="form-control" :name="`team_members[${index}][role]`" maxlength="150"></div>
                        <div class="col-md-3"><label class="form-label">Organization</label><input class="form-control" :name="`team_members[${index}][organization]`" maxlength="200"></div>
                        <div class="col-md-2"><label class="form-label">Profile URL</label><input class="form-control" type="url" :name="`team_members[${index}][profile_url]`"></div>
                        <div class="col-md-1"><button class="btn btn-outline-danger w-100" type="button" @click="members.splice(index, 1)" :disabled="members.length === 1" aria-label="Remove team member"><i class="bi bi-x"></i></button></div>
                    </div>
                </template>
            </div>
            <button class="btn btn-portal mt-4" type="submit"><i class="bi bi-plus-lg"></i> Add project</button>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>PROJECT HISTORY</span><h2>Added projects</h2></div><b>{{ $records->count() }}</b></div>
        @forelse($records as $project)
            <article class="education-record">
                <div class="education-record-head">
                    <div>
                        <strong>{{ $project->title }} @if($project->is_featured)<span class="badge text-bg-warning ms-1">Featured</span>@endif</strong>
                        <span>{{ $project->type?->display_name ?? 'Unspecified type' }} @if($project->candidate_role) · {{ $project->candidate_role }} @endif</span>
                        <small>{{ $project->organization_client ?: 'Independent project' }} · {{ $project->started_on?->format('M Y') ?? 'Date not set' }} – {{ $project->currently_active ? 'Present' : ($project->ended_on?->format('M Y') ?? 'Not set') }}</small>
                    </div>
                    <form method="POST" action="{{ route('talent.profile.remove', ['project', $project]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </div>
                @if($project->description)<p>{{ $project->description }}</p>@endif
                @if($project->skills->isNotEmpty())<div class="subject-chips">@foreach($project->skills as $skill)<span class="subject-chip">{{ $skill->display_name }}</span>@endforeach</div>@endif
                @if($project->teamMembers->isNotEmpty())<div class="mt-3"><strong class="small">Team</strong><div class="d-flex flex-wrap gap-2 mt-1">@foreach($project->teamMembers as $member)<span class="badge text-bg-light border">{{ $member->name }}{{ $member->role ? ' · '.$member->role : '' }}</span>@endforeach</div></div>@endif
                <div class="d-flex flex-wrap gap-3 mt-3 small">
                    @foreach([[$project->project_url,'Project'],[$project->repository_url,'Repository'],[$project->demo_url,'Demo']] as [$url,$label])@if($url)<a href="{{ $url }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> {{ $label }}</a>@endif @endforeach
                    @foreach($project->screenshots ?? [] as $path)<a href="{{ Storage::url($path) }}" target="_blank"><i class="bi bi-image"></i> Screenshot</a>@endforeach
                    @foreach($project->supporting_documents ?? [] as $path)<a href="{{ Storage::url($path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ basename($path) }}</a>@endforeach
                </div>
            </article>
        @empty
            <p class="text-muted mb-0">No projects added yet.</p>
        @endforelse
    </section>
</div>
