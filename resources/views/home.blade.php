<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A modern placement portal connecting ambitious talent with exceptional recruiters.">
    <title>PlaceFlow · Where talent meets opportunity</title>
    @livewireStyles
    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page">
    <nav class="navbar navbar-expand-lg landing-nav fixed-top" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 24" :class="scrolled && 'is-scrolled'">
        <div class="container">
            <a class="navbar-brand brand" href="#"><span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span><span>Place<span class="text-gradient">Flow</span></span></a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation"><i class="bi bi-list fs-2"></i></button>
            <div class="collapse navbar-collapse" id="landingNav">
                <ul class="navbar-nav mx-auto gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#possibilities">Possibilities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#community">Community</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2 pt-3 pt-lg-0">
                    @auth
                        <a class="btn btn-brand" href="{{ route(auth()->user()->dashboardRoute()) }}">My dashboard <i class="bi bi-arrow-right ms-1"></i></a>
                    @else
                        <a class="btn btn-link text-body text-decoration-none fw-semibold" href="{{ route('login') }}">Sign in</a>
                        <div class="dropdown">
                            <button class="btn btn-brand dropdown-toggle" data-bs-toggle="dropdown" type="button">Create account</button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2">
                                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('register.talent') }}"><i class="bi bi-briefcase text-primary me-2"></i>Talent</a></li>
                                <li><a class="dropdown-item rounded-3 py-2" href="{{ route('register.recruiter') }}"><i class="bi bi-building text-warning me-2"></i>Recruiter</a></li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-section overflow-hidden position-relative">
            <div class="hero-grid"></div><div class="hero-glow hero-glow-one"></div><div class="hero-glow hero-glow-two"></div>
            <div class="container position-relative">
                <div class="row align-items-center min-vh-100 py-5 g-5">
                    <div class="col-lg-6 pt-5">
                        <div class="hero-copy">
                            <span class="hero-pill"><span class="pulse-dot"></span> Opportunities are waiting for you</span>
                            <h1 class="hero-title mt-4">Where ambition finds its <span class="text-gradient">perfect place.</span></h1>
                            <p class="hero-lead mt-4">One vibrant community for exceptional talent and forward-thinking recruiters. Discover opportunities, build meaningful connections, and shape what comes next.</p>
                            <div class="d-flex flex-column flex-sm-row gap-3 mt-5">
                                <a class="btn btn-brand btn-lg hero-cta" href="{{ route('register.talent') }}"><i class="bi bi-rocket-takeoff me-2"></i>Find my opportunity</a>
                                <a class="btn btn-soft btn-lg hero-cta" href="{{ route('register.recruiter') }}"><i class="bi bi-people me-2"></i>Hire exceptional talent</a>
                            </div>
                            <div class="hero-proof d-flex align-items-center gap-3 mt-5">
                                <div class="avatar-stack"><span>AK</span><span>JM</span><span>SR</span><span><i class="bi bi-plus"></i></span></div>
                                <div><div class="text-warning small">★★★★★</div><small class="text-secondary">Trusted by a growing placement community</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-visual position-relative mx-auto">
                            <div class="dashboard-preview glass-card">
                                <div class="preview-top d-flex justify-content-between align-items-center"><div class="d-flex gap-2"><i></i><i></i><i></i></div><span class="small text-secondary">placeflow.app</span><span class="preview-avatar">JD</span></div>
                                <div class="preview-body">
                                    <div class="d-flex justify-content-between align-items-start mb-4"><div><small class="text-secondary">Good morning, Jordan</small><h3 class="h4 fw-bold mt-1">Your career is moving <span>forward.</span></h3></div><span class="notification"><i class="bi bi-bell"></i><b></b></span></div>
                                    <div class="row g-3">
                                        <div class="col-7"><div class="mini-panel h-100"><div class="d-flex justify-content-between"><span class="panel-label">Profile strength</span><strong class="text-primary">86%</strong></div><div class="progress mt-3" style="height: 7px"><div class="progress-bar gradient-bar" style="width:86%"></div></div><div class="profile-lines mt-4"><i></i><i></i><i></i></div></div></div>
                                        <div class="col-5"><div class="mini-panel stat-panel"><i class="bi bi-send-check"></i><strong>12</strong><small>Applications</small></div></div>
                                        <div class="col-12"><div class="mini-panel"><div class="panel-label mb-3">Recommended for you</div><div class="job-row"><span class="company-logo purple">N</span><div class="flex-grow-1"><strong>Product Designer</strong><small>Nova Labs · Remote</small></div><span class="match-badge">94% match</span></div><div class="job-row"><span class="company-logo orange">S</span><div class="flex-grow-1"><strong>Growth Associate</strong><small>Solaris · Hybrid</small></div><span class="match-badge">89% match</span></div></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="float-card float-card-top"><span class="float-icon success"><i class="bi bi-check-lg"></i></span><div><strong>Interview confirmed!</strong><small>Tomorrow · 10:30 AM</small></div></div>
                            <div class="float-card float-card-bottom"><div class="avatar-gradient">MW</div><div><strong>New profile match</strong><small>Senior developer · 96%</small></div></div>
                            <div class="orbit-dot orbit-one"></div><div class="orbit-dot orbit-two"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="logo-strip py-4"><div class="container"><div class="d-flex flex-wrap justify-content-center align-items-center gap-4 gap-lg-5 text-secondary"><span class="small text-uppercase fw-semibold tracking-wide">Built for every journey</span><span><i class="bi bi-buildings me-2"></i>Growing teams</span><span><i class="bi bi-mortarboard me-2"></i>New graduates</span><span><i class="bi bi-lightning-charge me-2"></i>Career movers</span><span><i class="bi bi-globe2 me-2"></i>Modern workplaces</span></div></div></section>

        <section class="section-space" id="how-it-works">
            <div class="container">
                <div class="section-heading text-center mx-auto"><span class="eyebrow text-primary">SIMPLE BY DESIGN</span><h2 class="display-5 fw-bold mt-3">From possibility to placement</h2><p class="lead text-secondary">Less friction, more momentum. Everything you need, right where you need it.</p></div>
                <div class="row g-4 mt-5">
                    @foreach([['01','Create your story','Build a profile that goes beyond a résumé and shows what makes you exceptional.','bi-stars','violet'],['02','Discover your match','Smart recommendations surface the roles and people aligned with your ambitions.','bi-compass','coral'],['03','Move forward','Apply, connect, interview, and track every step from one beautifully simple workspace.','bi-arrow-up-right-circle','cyan']] as [$number,$title,$copy,$icon,$color])
                        <div class="col-md-4"><article class="step-card h-100 {{ $color }}"><span class="step-number">{{ $number }}</span><span class="step-icon"><i class="bi {{ $icon }}"></i></span><h3 class="h4 fw-bold mt-4">{{ $title }}</h3><p class="text-secondary mb-0">{{ $copy }}</p></article></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-space possibilities-section" id="possibilities">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-6"><article class="audience-card talent-card h-100"><div class="audience-content"><span class="audience-tag"><i class="bi bi-briefcase"></i> FOR TALENT</span><h2 class="display-6 fw-bold mt-4">Your talent deserves to be <em>seen.</em></h2><p>Showcase your potential, discover roles that feel right, and stay in control of every application.</p><ul class="feature-list"><li><i class="bi bi-check2"></i>Personalized job discovery</li><li><i class="bi bi-check2"></i>One profile, effortless applications</li><li><i class="bi bi-check2"></i>Clear progress at every stage</li></ul><a class="btn btn-light btn-lg" href="{{ route('register.talent') }}">Start your journey <i class="bi bi-arrow-right ms-2"></i></a></div><div class="audience-art talent-art"><i class="bi bi-person-workspace"></i></div></article></div>
                    <div class="col-lg-6"><article class="audience-card recruiter-card h-100"><div class="audience-content"><span class="audience-tag"><i class="bi bi-building"></i> FOR RECRUITERS</span><h2 class="display-6 fw-bold mt-4">Meet people who move you <em>forward.</em></h2><p>Publish roles, discover standout candidates, and create a hiring experience people remember.</p><ul class="feature-list"><li><i class="bi bi-check2"></i>Beautiful, focused job postings</li><li><i class="bi bi-check2"></i>Organized candidate pipelines</li><li><i class="bi bi-check2"></i>Collaborative hiring workspace</li></ul><a class="btn btn-dark btn-lg" href="{{ route('register.recruiter') }}">Build your team <i class="bi bi-arrow-right ms-2"></i></a></div><div class="audience-art recruiter-art"><i class="bi bi-people-fill"></i></div></article></div>
                </div>
            </div>
        </section>

        <section class="section-space community-section" id="community">
            <div class="container text-center position-relative"><div class="community-orb"></div><span class="eyebrow text-warning">THE NEXT CHAPTER STARTS HERE</span><h2 class="display-4 fw-bold text-white mx-auto mt-3">Ready to find where you belong?</h2><p class="lead text-white-50 mx-auto mt-3">Join a placement community built around potential, progress, and possibility.</p><div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-5"><a class="btn btn-light btn-lg px-4" href="{{ route('register.talent') }}">I’m looking for work</a><a class="btn btn-outline-light btn-lg px-4" href="{{ route('register.recruiter') }}">I’m looking for talent</a></div></div>
        </section>
    </main>

    <footer class="landing-footer py-5"><div class="container"><div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3"><a class="brand text-decoration-none text-white" href="#"><span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span><span>PlaceFlow</span></a><p class="text-white-50 small mb-0">© {{ date('Y') }} PlaceFlow. Make your next move matter.</p><a class="text-white-50 text-decoration-none small" href="{{ route('login') }}">Member sign in <i class="bi bi-arrow-right ms-1"></i></a></div></div></footer>
</body>
</html>
