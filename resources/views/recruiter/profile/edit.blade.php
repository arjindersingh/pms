@extends('layouts.recruiter')
@section('title', 'Recruiter Profile')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">RECRUITER IDENTITY</span><h1>Your profile and organizations</h1><p>Maintain one primary contact profile and separate placement details for every organization you recruit for.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the form.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('recruiter.profile.update') }}" class="dashboard-card mb-4">@csrf @method('PUT')
    <div class="card-heading"><div><span>COMMON CONTACT DETAILS</span><h2>Primary recruiter</h2></div><span class="badge text-bg-light">Account: {{ auth()->user()->email }}</span></div>
    <div class="profile-form-grid">
        <div><label class="form-label">Full name</label><input class="form-control" value="{{ auth()->user()->name }}" disabled><div class="form-text">Change this from Account Profile.</div></div>
        <div><label class="form-label">Designation</label><input class="form-control" name="designation" value="{{ old('designation',$profile->designation) }}" placeholder="Placement Officer / HR Manager"></div>
        <div><label class="form-label">Primary phone *</label><input class="form-control" name="phone" required value="{{ old('phone',$profile->phone) }}"></div>
        <div><label class="form-label">Alternate phone</label><input class="form-control" name="alternate_phone" value="{{ old('alternate_phone',$profile->alternate_phone) }}"></div>
        <div><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="{{ old('whatsapp',$profile->whatsapp) }}"></div>
        <div><label class="form-label">Work email</label><input class="form-control" type="email" name="work_email" value="{{ old('work_email',$profile->work_email) }}"></div>
        <div><label class="form-label">Preferred contact</label><select class="form-select" name="preferred_contact_method">@foreach(['email'=>'Email','phone'=>'Phone','whatsapp'=>'WhatsApp'] as $value=>$label)<option value="{{ $value }}" @selected(old('preferred_contact_method',$profile->preferred_contact_method)===$value)>{{ $label }}</option>@endforeach</select></div>
        <div><label class="form-label">LinkedIn URL</label><input class="form-control" type="url" name="linkedin_url" value="{{ old('linkedin_url',$profile->linkedin_url) }}"></div>
        <div class="profile-span"><label class="form-label">Professional summary</label><textarea class="form-control" name="professional_summary" rows="3" placeholder="Your recruiting role, sectors and experience">{{ old('professional_summary',$profile->professional_summary) }}</textarea></div>
        <div><label class="form-label">Address line 1</label><input class="form-control" name="address_line_1" value="{{ old('address_line_1',$profile->address_line_1) }}"></div>
        <div><label class="form-label">Address line 2</label><input class="form-control" name="address_line_2" value="{{ old('address_line_2',$profile->address_line_2) }}"></div>
        @foreach(['city'=>'City','state'=>'State / Province','postal_code'=>'Postal code','country'=>'Country *'] as $field=>$label)<div><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $field }}" @required($field==='country') value="{{ old($field,$profile->{$field} ?: ($field==='country'?'India':'')) }}"></div>@endforeach
    </div>
    <button class="btn btn-portal mt-3"><i class="bi bi-check2-circle"></i> Save common profile</button>
</form>

<section class="dashboard-card">
    <div class="card-heading"><div><span>ORGANIZATIONS</span><h2>{{ $profile->organizations->count() }} {{ Str::plural('organization',$profile->organizations->count()) }} under this recruiter</h2></div><button class="btn btn-portal" type="button" data-bs-toggle="collapse" data-bs-target="#organization-form"><i class="bi bi-plus-lg"></i> Add organization</button></div>
    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="organization-form">
        @include('recruiter.profile.organization-form', ['organization' => null])
    </div>
    <div class="row g-3">
    @forelse($profile->organizations as $organization)
        <div class="col-12"><article class="border rounded-3 p-3">
            <div class="d-flex flex-wrap justify-content-between gap-3"><div><div class="d-flex gap-2 align-items-center"><h3 class="h5 mb-0">{{ $organization->name }}</h3>@if($organization->is_primary)<span class="badge text-bg-primary">Primary</span>@endif @unless($organization->is_active)<span class="badge text-bg-secondary">Inactive</span>@endunless</div><div class="text-secondary mt-1">{{ $organization->type_label }} · {{ $organization->city }}, {{ $organization->state }}</div><small>{{ $organization->placement_contact_name }} · {{ $organization->placement_email }} · {{ $organization->placement_phone }}</small></div><div class="d-flex gap-2"><button class="btn btn-sm btn-portal-soft" type="button" data-bs-toggle="collapse" data-bs-target="#organization-{{ $organization->id }}">Edit</button><form method="POST" action="{{ route('recruiter.organizations.destroy',$organization) }}" onsubmit="return confirm('Remove this organization?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form></div></div>
            <div class="collapse mt-3" id="organization-{{ $organization->id }}">@include('recruiter.profile.organization-form', ['organization' => $organization])</div>
        </article></div>
    @empty
        <div class="account-empty"><i class="bi bi-buildings"></i><strong>No organizations added</strong><span>Add a school, college, hospital, company, agency, or another organization you recruit for.</span></div>
    @endforelse
    </div>
</section>
@endsection
