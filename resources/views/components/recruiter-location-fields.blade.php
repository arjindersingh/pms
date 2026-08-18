@props(['countries', 'country' => 'India', 'state' => null, 'district' => null, 'required' => false])

<div class="profile-span">
    <div class="profile-form-grid" data-location-fields data-states-url="{{ route('locations.states', ['country' => '__COUNTRY__']) }}" data-districts-url="{{ route('locations.districts', ['state' => '__STATE__']) }}">
        <div><label class="form-label">Country{{ $required ? ' *' : '' }}</label><select class="form-select" name="country" data-location-country @required($required)><option value="">Select country</option>@foreach($countries as $option)<option value="{{ $option->display_name }}" data-code="{{ $option->code }}" @selected($country === $option->display_name)>{{ $option->display_name }}</option>@endforeach</select></div>
        <div><label class="form-label">State / Province{{ $required ? ' *' : '' }}</label><select class="form-select" name="state" data-location-state data-selected="{{ $state }}" @required($required)><option value="">Select state / province</option>@if($state)<option value="{{ $state }}" selected>{{ $state }}</option>@endif</select></div>
        <div><label class="form-label">District</label><select class="form-select" name="district" data-location-district data-selected="{{ $district }}"><option value="">Select district</option>@if($district)<option value="{{ $district }}" selected>{{ $district }}</option>@endif</select></div>
    </div>
</div>
