@props(['countries', 'states'])

<datalist id="country-options">
    @foreach($countries as $country)
        <option value="{{ $country->display_name }}"></option>
    @endforeach
</datalist>
<datalist id="state-options">
    @foreach($states as $state)
        <option value="{{ $state->display_name }}">{{ $state->country->display_name }}</option>
    @endforeach
</datalist>
