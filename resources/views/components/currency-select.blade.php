@props(['name' => 'currency', 'selected' => \App\Support\Currency::DEFAULT, 'required' => true])

<select {{ $attributes->class(['form-select'])->merge(['name' => $name]) }} @required($required)>
    @foreach(\App\Support\Currency::CODES as $currency)
        <option value="{{ $currency }}" @selected(old($name, $selected) === $currency)>{{ $currency }}</option>
    @endforeach
</select>
