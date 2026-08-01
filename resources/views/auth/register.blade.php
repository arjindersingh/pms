<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join as {{ $category->label() }} · {{ config('app.name', 'Placement Portal') }}</title>
    @livewireStyles @livewireScriptConfig @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-orb auth-orb-one"></div><div class="auth-orb auth-orb-two"></div>
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5 position-relative">
        <div class="auth-card bg-white p-4 p-md-5" style="max-width: 620px">
            <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-body mb-4" href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Back to home</a>
            <div class="d-flex align-items-start gap-3 mb-4">
                <span class="auth-icon flex-shrink-0"><i class="bi {{ $category === \App\Enums\UserCategory::Recruiter ? 'bi-building' : 'bi-briefcase' }}"></i></span>
                <div><span class="eyebrow text-primary">CREATE YOUR ACCOUNT</span><h1 class="h2 fw-bold mb-1">Join as {{ $category->label() }}</h1><p class="text-secondary mb-0">{{ $category === \App\Enums\UserCategory::Recruiter ? 'Find the talent that moves your organization forward.' : 'Build your profile and find work worth doing.' }}</p></div>
            </div>
            <form method="POST" action="{{ $category === \App\Enums\UserCategory::Recruiter ? route('register.recruiter.store') : route('register.talent.store') }}">
                @csrf
                <div class="form-floating mb-3"><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Full name" required><label for="name">{{ $category === \App\Enums\UserCategory::Recruiter ? 'Contact name' : 'Full name' }}</label>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-floating mb-3"><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" required><label for="email">Email address</label>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><div class="form-floating"><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="Password" required><label for="password">Password</label>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                    <div class="col-md-6"><div class="form-floating"><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm password" required><label for="password_confirmation">Confirm password</label></div></div>
                </div>
                <div class="form-check mb-4"><input class="form-check-input @error('terms') is-invalid @enderror" id="terms" name="terms" type="checkbox" value="1" required><label class="form-check-label" for="terms">I agree to the terms and privacy policy.</label>@error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <button class="btn btn-brand btn-lg w-100" type="submit">Create my account <i class="bi bi-arrow-right ms-2"></i></button>
            </form>
            <p class="text-center text-secondary mt-4 mb-0">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </main>
</body>
</html>
