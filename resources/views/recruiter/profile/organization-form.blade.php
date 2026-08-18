@php($editing = (bool) $organization)
<form method="POST" action="{{ $editing ? route('recruiter.organizations.update',$organization) : route('recruiter.organizations.store') }}" class="border rounded-3 p-3 bg-light-subtle">@csrf @if($editing) @method('PUT') @endif
    <div class="profile-form-grid">
        <div><label class="form-label">Organization name *</label><input class="form-control" name="name" required value="{{ $editing ? $organization->name : old('name') }}"></div>
        <div><label class="form-label">Category *</label><select class="form-select" name="organization_type" required><option value="">Select category</option>@foreach($organizationTypes as $category)<option value="{{ $category->code }}" @selected(($editing?$organization->organization_type:old('organization_type'))===$category->code)>{{ $category->display_name }}</option>@endforeach</select></div>
        <div><label class="form-label">Other category</label><input class="form-control" name="other_type" value="{{ $editing ? $organization->other_type : old('other_type') }}"></div>
        <div><label class="form-label">Legal name</label><input class="form-control" name="legal_name" value="{{ $editing ? $organization->legal_name : old('legal_name') }}"></div>
        <div><label class="form-label">Registration / affiliation no.</label><input class="form-control" name="registration_number" value="{{ $editing ? $organization->registration_number : old('registration_number') }}"></div>
        <div><label class="form-label">Website</label><input class="form-control" type="url" name="website" value="{{ $editing ? $organization->website : old('website') }}"></div>
        <div><label class="form-label">Industry / education board / speciality</label><input class="form-control" name="industry" value="{{ $editing ? $organization->industry : old('industry') }}"></div>
        <div><label class="form-label">Organization size</label><select class="form-select" name="organization_size"><option value="">Select</option>@foreach(['1-10','11-50','51-200','201-500','501-1000','1000+'] as $size)<option @selected(($editing?$organization->organization_size:old('organization_size'))===$size)>{{ $size }}</option>@endforeach</select></div>
        <div class="profile-span"><label class="form-label">About the organization</label><textarea class="form-control" name="description" rows="2">{{ $editing ? $organization->description : old('description') }}</textarea></div>
        <div class="profile-span"><h3 class="h6 mt-2 mb-0">Head of Institution (HOI)</h3></div>
        <div><label class="form-label">HOI name</label><input class="form-control" name="hoi_name" value="{{ $editing ? $organization->hoi_name : old('hoi_name') }}"></div>
        <div><label class="form-label">HOI designation</label><input class="form-control" name="hoi_designation" value="{{ $editing ? $organization->hoi_designation : old('hoi_designation') }}" placeholder="Principal / Director / CEO"></div>
        <div><label class="form-label">HOI email</label><input class="form-control" type="email" name="hoi_email" value="{{ $editing ? $organization->hoi_email : old('hoi_email') }}"></div>
        <div><label class="form-label">HOI phone</label><input class="form-control" name="hoi_phone" value="{{ $editing ? $organization->hoi_phone : old('hoi_phone') }}"></div>
        <div class="profile-span"><h3 class="h6 mt-2 mb-0">Contact Detail</h3></div>
        <div><label class="form-label">Placement contact name *</label><input class="form-control" name="placement_contact_name" required value="{{ $editing ? $organization->placement_contact_name : old('placement_contact_name') }}"></div>
        <div><label class="form-label">Contact designation</label><input class="form-control" name="placement_contact_designation" value="{{ $editing ? $organization->placement_contact_designation : old('placement_contact_designation') }}"></div>
        <div><label class="form-label">Placement email *</label><input class="form-control" type="email" name="placement_email" required value="{{ $editing ? $organization->placement_email : old('placement_email') }}"></div>
        <div><label class="form-label">Placement phone *</label><input class="form-control" name="placement_phone" required value="{{ $editing ? $organization->placement_phone : old('placement_phone') }}"></div>
        <div><label class="form-label">Alternate phone</label><input class="form-control" name="alternate_phone" value="{{ $editing ? $organization->alternate_phone : old('alternate_phone') }}"></div>
        <div><label class="form-label">Address line 1 *</label><input class="form-control" name="address_line_1" required value="{{ $editing ? $organization->address_line_1 : old('address_line_1') }}"></div>
        <div><label class="form-label">Address line 2</label><input class="form-control" name="address_line_2" value="{{ $editing ? $organization->address_line_2 : old('address_line_2') }}"></div>
        @foreach(['city'=>'City *','state'=>'State / Province *','postal_code'=>'Postal code *','country'=>'Country *'] as $field=>$label)<div><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $field }}" required value="{{ $editing ? $organization->{$field} : old($field,$field==='country'?'India':'') }}"></div>@endforeach
        <div class="form-check form-switch"><input type="hidden" name="is_primary" value="0"><input class="form-check-input" type="checkbox" name="is_primary" value="1" @checked($editing?$organization->is_primary:old('is_primary'))><label class="form-check-label">Primary organization</label></div>
        <div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($editing?$organization->is_active:old('is_active',true))><label class="form-check-label">Currently recruiting</label></div>
    </div>
    <button class="btn btn-portal mt-3">{{ $editing ? 'Update organization' : 'Add organization' }}</button>
</form>
