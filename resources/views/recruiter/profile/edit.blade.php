@extends('layouts.recruiter')
@section('title', match($section) {'basic' => 'Basic Detail', 'contact' => 'Contact Detail', default => 'Organisations'})
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">RECRUITER PROFILE</span><h1>{{ match($section) {'basic' => 'Basic Detail', 'contact' => 'Contact Detail', default => 'Organisations'} }}</h1><p>Your Recruiter Profile is separate from the account profile available in the top bar.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the form.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@if($section === 'basic')
<form method="POST" action="{{ route('recruiter.profile.basic.update') }}" class="dashboard-card profile-form-card">@csrf @method('PUT')
    <div class="card-heading"><div><span>PROFESSIONAL IDENTITY</span><h2>Basic recruiter details</h2></div><span class="badge text-bg-light">Recruiter profile</span></div>
    <div class="profile-form-grid">
        <div><label class="form-label">Full name *</label><input class="form-control" name="full_name" required value="{{ old('full_name',$profile->full_name ?: auth()->user()->name) }}"><div class="form-text">This can differ from your account name.</div></div>
        <div><label class="form-label">Designation</label><input class="form-control" name="designation" value="{{ old('designation',$profile->designation) }}" placeholder="Placement Officer / HR Manager"></div>
        <div class="profile-span"><label class="form-label">Professional summary</label><textarea class="form-control" name="professional_summary" rows="4" placeholder="Your recruiting role, sectors and experience">{{ old('professional_summary',$profile->professional_summary) }}</textarea></div>
        <div class="profile-span"><label class="form-label">LinkedIn URL</label><input class="form-control" type="url" name="linkedin_url" value="{{ old('linkedin_url',$profile->linkedin_url) }}"></div>
    </div>
    <button class="btn btn-portal mt-3"><i class="bi bi-check2-circle"></i> Save basic detail</button>
</form>
@elseif($section === 'contact')
<form method="POST" action="{{ route('recruiter.profile.contact.update') }}" class="dashboard-card profile-form-card">@csrf @method('PUT')
    <div class="card-heading"><div><span>CONTACT DETAIL</span><h2>How candidates and organizations reach you</h2></div></div>
    <div class="profile-form-grid">
        <div><label class="form-label">Primary phone *</label><input class="form-control" name="phone" required value="{{ old('phone',$profile->phone) }}"></div>
        <div><label class="form-label">Alternate phone</label><input class="form-control" name="alternate_phone" value="{{ old('alternate_phone',$profile->alternate_phone) }}"></div>
        <div><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="{{ old('whatsapp',$profile->whatsapp) }}"></div>
        <div><label class="form-label">Work email</label><input class="form-control" type="email" name="work_email" value="{{ old('work_email',$profile->work_email) }}"><div class="form-text">Account email: {{ auth()->user()->email }}</div></div>
        <div><label class="form-label">Preferred contact</label><select class="form-select" name="preferred_contact_method">@foreach(['email'=>'Email','phone'=>'Phone','whatsapp'=>'WhatsApp'] as $value=>$label)<option value="{{ $value }}" @selected(old('preferred_contact_method',$profile->preferred_contact_method ?: 'email')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div></div>
        <div><label class="form-label">Address line 1</label><input class="form-control" name="address_line_1" value="{{ old('address_line_1',$profile->address_line_1) }}"></div>
        <div><label class="form-label">Address line 2</label><input class="form-control" name="address_line_2" value="{{ old('address_line_2',$profile->address_line_2) }}"></div>
        @foreach(['city'=>'City','state'=>'State / Province','postal_code'=>'Postal code','country'=>'Country *'] as $field=>$label)<div><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $field }}" @required($field==='country') value="{{ old($field,$profile->{$field} ?: ($field==='country'?'India':'')) }}"></div>@endforeach
    </div>
    <button class="btn btn-portal mt-3"><i class="bi bi-check2-circle"></i> Save contact detail</button>
</form>
@else
<section class="dashboard-card">
    <div class="card-heading"><div><span>MULTIPLE ORGANISATIONS</span><h2>{{ $profile->organizations->count() }} {{ Str::plural('organisation',$profile->organizations->count()) }}</h2></div><button class="btn btn-portal" type="button" data-bs-toggle="collapse" data-bs-target="#organization-form"><i class="bi bi-plus-lg"></i> Add organisation</button></div>
    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="organization-form">@include('recruiter.profile.organization-form', ['organization' => null])</div>
    <div class="row g-3">
    @forelse($profile->organizations as $organization)
        <div class="col-12"><article class="border rounded-3 p-3">
            <div class="d-flex flex-wrap justify-content-between gap-3"><div><div class="d-flex gap-2 align-items-center"><h3 class="h5 mb-0">{{ $organization->name }}</h3>@if($organization->is_primary)<span class="badge text-bg-primary">Primary</span>@endif @unless($organization->is_active)<span class="badge text-bg-secondary">Inactive</span>@endunless</div><div class="text-secondary mt-1">{{ $organization->type_label }} · {{ $organization->city }}, {{ $organization->state }}</div><small>Contact: {{ $organization->placement_contact_name }} · {{ $organization->placement_email }}</small>@if($organization->hoi_name)<small class="d-block">HOI: {{ $organization->hoi_name }}{{ $organization->hoi_designation ? ' · '.$organization->hoi_designation : '' }}</small>@endif</div><div class="d-flex gap-2"><button class="btn btn-sm btn-portal-soft" type="button" data-bs-toggle="collapse" data-bs-target="#organization-{{ $organization->id }}">Edit</button><form method="POST" action="{{ route('recruiter.organizations.destroy',$organization) }}" onsubmit="return confirm('Remove this organisation?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form></div></div>
            <div class="collapse mt-3" id="organization-{{ $organization->id }}">@include('recruiter.profile.organization-form', ['organization' => $organization])</div>
        </article></div>
    @empty
        <div class="account-empty"><i class="bi bi-buildings"></i><strong>No organisations added</strong><span>Add each organisation you recruit for, including its basic, contact and HOI details.</span></div>
    @endforelse
    </div>
</section>
@endif
@endsection
