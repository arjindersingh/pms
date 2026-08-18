@extends('layouts.recruiter')
@section('title', $jobPosting->exists ? 'Edit Job' : 'Create Job')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">TALENT ACQUISITION HUB</span><h1>{{ $jobPosting->exists ? 'Edit job posting' : 'Create a job' }}</h1><p>Choose a category-specific post or enter a custom title for complete flexibility.</p></div><a class="btn btn-portal-light" href="{{ route('recruiter.job-postings.index') }}">Back to jobs</a></div>
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the form.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if($organizations->isEmpty())<div class="alert alert-warning">Add an active organisation before creating a job. <a href="{{ route('recruiter.profile.organizations') }}">Manage organisations</a></div>@endif
<form method="POST" action="{{ $jobPosting->exists ? route('recruiter.job-postings.update', $jobPosting) : route('recruiter.job-postings.store') }}" class="dashboard-card profile-form-card" data-job-form>@csrf @if($jobPosting->exists) @method('PUT') @endif
    <div class="card-heading"><div><span>JOB DETAIL</span><h2>Opportunity information</h2></div></div>
    <div class="profile-form-grid">
        <div><label class="form-label">Organisation *</label><select class="form-select" name="recruiter_organization_id" required data-organization><option value="">Select organisation</option>@foreach($organizations as $organization)<option value="{{ $organization->id }}" data-category="{{ $organization->organization_type }}" @selected(old('recruiter_organization_id',$jobPosting->recruiter_organization_id)==$organization->id)>{{ $organization->name }} · {{ $organization->type_label }}</option>@endforeach</select></div>
        <div><label class="form-label">Predefined post</label><select class="form-select" name="organization_post_id" data-post><option value="">Custom / other post</option>@foreach($categories as $category)@foreach($category->posts as $post)<option value="{{ $post->id }}" data-category="{{ $category->code }}" @selected(old('organization_post_id',$jobPosting->organization_post_id)==$post->id)>{{ $post->display_name }}</option>@endforeach @endforeach</select><div class="form-text">Options adapt to the selected organisation category.</div></div>
        <div class="profile-span"><label class="form-label">Custom post title</label><input class="form-control" name="custom_title" value="{{ old('custom_title', $jobPosting->organization_post_id ? '' : $jobPosting->title) }}" placeholder="Use when no predefined post fits"></div>
        <div><label class="form-label">Employment type</label><select class="form-select" name="employment_type">@foreach([''=>'Select','full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','temporary'=>'Temporary','internship'=>'Internship','consultancy'=>'Consultancy'] as $value=>$label)<option value="{{ $value }}" @selected(old('employment_type',$jobPosting->employment_type)===$value)>{{ $label }}</option>@endforeach</select></div>
        <div><label class="form-label">Work mode</label><select class="form-select" name="work_mode">@foreach([''=>'Select','onsite'=>'On-site','remote'=>'Remote','hybrid'=>'Hybrid','field'=>'Field-based'] as $value=>$label)<option value="{{ $value }}" @selected(old('work_mode',$jobPosting->work_mode)===$value)>{{ $label }}</option>@endforeach</select></div>
        <div><label class="form-label">Location</label><input class="form-control" name="location" value="{{ old('location',$jobPosting->location) }}"></div>
        <div><label class="form-label">Vacancies *</label><input class="form-control" type="number" min="1" name="vacancies" value="{{ old('vacancies',$jobPosting->vacancies ?: 1) }}" required></div>
        <div><label class="form-label">Minimum salary</label><input class="form-control" type="number" min="0" step="0.01" name="salary_min" value="{{ old('salary_min',$jobPosting->salary_min) }}"></div>
        <div><label class="form-label">Maximum salary</label><input class="form-control" type="number" min="0" step="0.01" name="salary_max" value="{{ old('salary_max',$jobPosting->salary_max) }}"></div>
        <div><label class="form-label">Currency</label><x-currency-select :selected="$jobPosting->currency ?: \App\Support\Currency::DEFAULT" /></div>
        <div><label class="form-label">Application deadline</label><input class="form-control" type="date" name="application_deadline" value="{{ old('application_deadline',$jobPosting->application_deadline?->format('Y-m-d')) }}"></div>
        <div class="profile-span"><label class="form-label">Description *</label><textarea class="form-control" name="description" rows="5" required>{{ old('description',$jobPosting->description) }}</textarea></div>
        <div class="profile-span"><label class="form-label">Requirements</label><textarea class="form-control" name="requirements" rows="4">{{ old('requirements',$jobPosting->requirements) }}</textarea></div>
        <div><label class="form-label">Status *</label><select class="form-select" name="status" required>@foreach(['draft'=>'Draft','published'=>'Published','closed'=>'Closed'] as $value=>$label)<option value="{{ $value }}" @selected(old('status',$jobPosting->status ?: 'draft')===$value)>{{ $label }}</option>@endforeach</select></div>
    </div>
    <button class="btn btn-portal mt-4" @disabled($organizations->isEmpty())><i class="bi bi-check2-circle"></i> {{ $jobPosting->exists ? 'Save changes' : 'Create job posting' }}</button>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const organisation = document.querySelector('[data-organization]');
    const post = document.querySelector('[data-post]');
    if (!organisation || !post) return;
    const filterPosts = () => {
        const category = organisation.selectedOptions[0]?.dataset.category;
        [...post.options].forEach((option, index) => option.hidden = index > 0 && option.dataset.category !== category);
        if (post.selectedOptions[0]?.hidden) post.value = '';
    };
    organisation.addEventListener('change', filterPosts);
    filterPosts();
});
</script>
@endsection
