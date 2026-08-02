@php
    $platformIcons = ['LINKEDIN' => 'bi-linkedin', 'GITHUB' => 'bi-github', 'GITLAB' => 'bi-git', 'YOUTUBE' => 'bi-youtube', 'PERSONAL_WEBSITE' => 'bi-globe', 'PORTFOLIO_WEBSITE' => 'bi-window', 'OTHER' => 'bi-link-45deg'];
@endphp
<div class="education-stack">
    <section class="dashboard-card education-form-card">
        <div class="card-heading"><div><span>ONLINE PRESENCE</span><h2>Add profile</h2><p>Connect your professional, creative, research, and social profiles.</p></div></div>
        <form method="POST" action="{{ route('talent.profile.social') }}">
            @csrf
            <div class="profile-form-grid">
                @include('talent.profile.master-select', ['name' => 'social_platform_id', 'label' => 'Platform', 'items' => $socialPlatforms, 'value' => null])
                <div><label class="form-label" for="custom_platform_name">Custom platform name</label><input class="form-control" id="custom_platform_name" name="custom_platform_name" value="{{ old('custom_platform_name') }}" maxlength="100" placeholder="Required when platform is Other"></div>
                <div><label class="form-label" for="social_username">Username / Handle</label><input class="form-control" id="social_username" name="username" value="{{ old('username') }}" maxlength="150" placeholder="e.g. @username"></div>
                <div><label class="form-label" for="social_profile_url">Profile URL</label><input class="form-control" id="social_profile_url" type="url" name="profile_url" value="{{ old('profile_url') }}" required maxlength="500" placeholder="https://"></div>
                <div class="profile-span">@include('talent.profile.switch', ['name' => 'is_primary', 'label' => 'Make this my primary online profile', 'checked' => false])</div>
            </div>
            <button class="btn btn-portal mt-4"><i class="bi bi-plus-lg"></i> Add profile</button>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="card-heading"><div><span>PROFILE LINKS</span><h2>Social & professional profiles</h2></div><b>{{ $records->count() }}</b></div>
        @forelse($records as $socialProfile)
            @php
                $platformName = $socialProfile->platform?->code === 'OTHER' ? $socialProfile->custom_platform_name : $socialProfile->platform?->display_name;
                $icon = $platformIcons[$socialProfile->platform?->code] ?? 'bi-person-badge';
            @endphp
            <article class="education-record">
                <div class="education-record-head">
                    <div>
                        <strong><i class="bi {{ $icon }} me-1"></i>{{ $platformName ?: 'Online profile' }} @if($socialProfile->is_primary)<span class="badge text-bg-primary ms-1">Primary</span>@endif</strong>
                        @if($socialProfile->username)<span>{{ $socialProfile->username }}</span>@endif
                        <small class="text-break">{{ $socialProfile->profile_url }}</small>
                    </div>
                    <form method="POST" action="{{ route('talent.profile.remove', ['social-profile', $socialProfile]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $platformName }} profile"><i class="bi bi-trash"></i></button></form>
                </div>
                <a class="small" href="{{ $socialProfile->profile_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> Open profile</a>
            </article>
        @empty
            <p class="text-muted mb-0">No social or professional profiles added yet.</p>
        @endforelse
    </section>
</div>
