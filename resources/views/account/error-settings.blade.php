@extends('layouts.'.auth()->user()->userType->category->value)

@section('title', 'Error settings')
@section('content')
<div class="dashboard-heading mb-4"><div><span class="dashboard-eyebrow">ACCOUNT</span><h1>Error display settings</h1><p>Choose how errors look, move, and appear across your dashboard.</p></div></div>
<form method="POST" action="{{ route('account.error-settings.update') }}" class="error-settings-grid">@csrf @method('PUT')
    <section class="portal-card account-panel p-4">
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        <h2 class="h5 fw-bold mb-3">Placement and behaviour</h2>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="placement">Display location</label><select class="form-select" id="placement" name="placement">@foreach(config('error-display.placements') as $value => $label)<option value="{{ $value }}" @selected(old('placement', $settings->placement) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="density">Spacing</label><select class="form-select" id="density" name="density">@foreach(config('error-display.densities') as $value => $label)<option value="{{ $value }}" @selected(old('density', $settings->density) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="motion">Entrance motion</label><select class="form-select" id="motion" name="motion">@foreach(config('error-display.motions') as $value => $label)<option value="{{ $value }}" @selected(old('motion', $settings->motion) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="auto_dismiss_seconds">Auto-dismiss (0–30 seconds)</label><input class="form-control" id="auto_dismiss_seconds" type="number" min="0" max="30" name="auto_dismiss_seconds" value="{{ old('auto_dismiss_seconds', $settings->auto_dismiss_seconds) }}"></div>
        </div>
        <h2 class="h5 fw-bold mt-4 mb-3">Typography and colour</h2>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="font_family">Font</label><select class="form-select" id="font_family" name="font_family">@foreach(config('error-display.fonts') as $value => $label)<option value="{{ $value }}" @selected(old('font_family', $settings->font_family) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="font_size">Font size (12–20 px)</label><input class="form-control" id="font_size" type="number" min="12" max="20" name="font_size" value="{{ old('font_size', $settings->font_size) }}"></div>
            @foreach(['text_color' => 'Text colour', 'background_color' => 'Background', 'accent_color' => 'Accent'] as $name => $label)<div class="col-sm-4"><label class="form-label" for="{{ $name }}">{{ $label }}</label><input class="form-control form-control-color w-100" id="{{ $name }}" type="color" name="{{ $name }}" value="{{ old($name, $settings->{$name}) }}"></div>@endforeach
        </div>
        <div class="error-toggles mt-4">
            @foreach(['show_icon' => 'Show warning icon', 'allow_dismiss' => 'Allow manual dismissal', 'group_messages' => 'Show all messages together'] as $name => $label)<label class="form-check form-switch"><input type="hidden" name="{{ $name }}" value="0"><input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $settings->{$name}))><span class="form-check-label">{{ $label }}</span></label>@endforeach
        </div>
        @if($errors->any())<div class="small text-danger mt-3">Please correct the settings highlighted by the error display.</div>@endif
        <button class="btn btn-primary mt-4" type="submit">Save error settings</button>
    </section>
    <aside class="portal-card error-preview-card p-4"><span class="dashboard-eyebrow">PREVIEW</span><h2 class="h5 fw-bold mt-2">Designed around you</h2><p class="text-secondary small">Your saved choices apply to validation and application errors in this dashboard on every device and future session.</p><div class="error-preview"><i class="bi bi-exclamation-triangle-fill"></i><div><strong>Example error</strong><small>This is how an important message can call for your attention.</small></div></div></aside>
</form>
@endsection
