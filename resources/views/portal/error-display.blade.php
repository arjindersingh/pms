@php
    $font = match($settings->font_family) { 'serif' => 'Georgia, serif', 'mono' => 'ui-monospace, monospace', 'rounded' => '"Trebuchet MS", sans-serif', default => 'inherit' };
    $allMessages = collect($errors->all())->when(session()->has('error'), fn ($items) => $items->prepend(session('error')))->filter()->unique()->values();
    $messages = $settings->group_messages ? $allMessages : $allMessages->take(1);
@endphp
<div class="error-display error-display--{{ $settings->placement }} error-density--{{ $settings->density }} error-motion--{{ $settings->motion }}"
     style="--error-font:{{ $font }};--error-size:{{ $settings->font_size }}px;--error-text:{{ $settings->text_color }};--error-bg:{{ $settings->background_color }};--error-accent:{{ $settings->accent_color }}"
     role="alert" aria-live="assertive" x-data="{ visible: true }" x-show="visible" x-transition
     @if($settings->auto_dismiss_seconds > 0) x-init="setTimeout(() => visible = false, {{ $settings->auto_dismiss_seconds * 1000 }})" @endif>
    <div class="error-display__panel">
        @if($settings->show_icon)<span class="error-display__icon"><i class="bi bi-exclamation-triangle-fill"></i></span>@endif
        <div class="error-display__content"><strong>We couldn’t complete that action</strong>
            <ul>@foreach($messages as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
        @if($settings->allow_dismiss)<button type="button" @click="visible = false" aria-label="Dismiss errors"><i class="bi bi-x-lg"></i></button>@endif
    </div>
</div>
