<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $administratorPortal ? 'Administrator access' : 'Sign in' }} · {{ config('app.name', 'Placement Portal') }}</title>
    @livewireStyles
    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-orb auth-orb-one"></div><div class="auth-orb auth-orb-two"></div>
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5 position-relative">
        <div class="auth-card row g-0 overflow-hidden">
            <div class="col-lg-5 auth-card-aside d-none d-lg-flex flex-column justify-content-between p-5">
                <a class="brand text-white text-decoration-none" href="{{ route('home') }}">@if($companyProfile->logoUrl(true))<img class="landing-company-logo" src="{{ $companyProfile->logoUrl(true) }}" alt="{{ $companyProfile->display_name }}">@else<span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>@endif<span>{{ $companyProfile->display_name }}</span></a>
                <div>
                    <span class="eyebrow text-white-50">{{ $administratorPortal ? 'SECURE OPERATIONS' : 'YOUR NEXT MOVE' }}</span>
                    <h2 class="display-6 fw-bold mt-3">{{ $administratorPortal ? 'Manage placement success with confidence.' : 'Opportunity starts with one sign in.' }}</h2>
                </div>
                <div class="small text-white-50"><i class="bi bi-shield-check me-2"></i>Secure, role-aware access</div>
            </div>
            <div class="col-lg-7 bg-white p-4 p-md-5">
                <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-body mb-5" href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Back to home</a>
                <div class="mb-4">
                    <span class="auth-icon"><i class="bi {{ $administratorPortal ? 'bi-shield-lock' : 'bi-person-check' }}"></i></span>
                    <h1 class="h2 fw-bold mt-3 mb-2">{{ $administratorPortal ? 'Administrator access' : 'Welcome back' }}</h1>
                    <p class="text-secondary">{{ $administratorPortal ? 'Use your authorized administrator credentials.' : 'Sign in as a recruiter or talent.' }}</p>
                </div>
                <form method="POST" action="{{ $administratorPortal ? route('administrator.login.store') : route('login.store') }}">
                    @csrf
                    <div class="form-floating mb-3">
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                        <label for="email">Email address</label>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating mb-3" x-data="{ visible: false }">
                        <input class="form-control pe-5" id="password" name="password" :type="visible ? 'text' : 'password'" placeholder="Password" required>
                        <label for="password">Password</label>
                        <button class="password-toggle" type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'"><i class="bi" :class="visible ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                    </div>
                    <div class="form-check mb-4"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">Keep me signed in</label></div>
                    <button class="btn btn-brand btn-lg w-100" type="submit">Sign in securely <i class="bi bi-arrow-right ms-2"></i></button>
                </form>
                @unless($administratorPortal)
                    <div class="text-center mt-4 text-secondary">New here? <a href="{{ route('register.talent') }}">Join as talent</a> or <a href="{{ route('register.recruiter') }}">a recruiter</a></div>
                @endunless
            </div>
        </div>
    </main>
    <x-system-compatibility />
</body>
</html>
