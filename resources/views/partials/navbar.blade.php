<div class="nimcs-service-bar" aria-label="Official service notice">
    <div class="container">
        <span class="d-flex align-items-center gap-2">
            <span class="material-symbols-outlined fs-18">shield_moon</span>
            Official indigene registration and certificate verification
        </span>
        <span class="d-flex align-items-center gap-3">
            <span class="provider-credit">Technology service by Haigha Tech</span>
            <span class="bar-divider" aria-hidden="true"></span>
            <a href="{{ route('accessibility') }}" class="service-bar-link d-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-18">accessibility_new</span>
                Accessibility
            </a>
            <a href="{{ route('support') }}" class="service-bar-link d-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-18">support_agent</span>
                Support
            </a>
        </span>
    </div>
</div>

<nav class="navbar navbar-expand-lg nimcs-navbar fixed-top" id="navbar" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand me-lg-4" href="{{ route('home') }}" aria-label="{{ $brandShortName ?? 'NIMCS' }} home">
            @if ($brandLogoUrl ?? null)
                <span class="d-flex align-items-center gap-2">
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandShortName ?? 'NIMCS' }} logo" class="nimcs-logo-img nimcs-navbar-logo">
                    <span class="brand-lockup d-flex flex-column lh-sm">
                        <span class="brand-name">Indigene Certification</span>
                        <span class="brand-sub">Nigerian Indigene Management &amp; Certification System</span>
                    </span>
                </span>
            @else
            <span class="nimcs-brand-mark" aria-hidden="true">
                <span class="material-symbols-outlined">verified_user</span>
            </span>
            <span class="brand-lockup d-flex flex-column lh-sm">
                <span class="brand-name">Indigene Certification</span>
                <span class="brand-sub">Nigerian Indigene Management &amp; Certification System</span>
            </span>
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#how-it-works">How it works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#verify-certificate">Verify</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#trust">Trust &amp; Privacy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#faqs">FAQ&rsquo;s</a>
                </li>
            </ul>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="{{ route('certificates.verify.form') }}" class="btn btn-nimcs-outline py-2 px-4 fw-semibold fs-15 rounded-pill">
                    <i class="ri-qr-scan-line fs-18 me-1"></i>
                    Verify Certificate
                </a>
                <a href="{{ route('login') }}" class="btn btn-nimcs-solid py-2 px-4 fw-semibold fs-15 rounded-pill text-white">
                    <i class="ri-login-box-line fs-18 me-1"></i>
                    Staff Login
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
    /* ---------- Service bar ---------- */
    .nimcs-service-bar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1031;
        min-height: 40px;
        color: #dbe8e2;
        background: #0B1F3A;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        font-size: .82rem;
    }
    .nimcs-service-bar .container {
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .nimcs-service-bar .provider-credit { color: #9fb3c4; font-weight: 500; }
    .nimcs-service-bar .bar-divider { width: 1px; height: 14px; background: rgba(255, 255, 255, .22); }
    .nimcs-service-bar .service-bar-link {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
    }
    .nimcs-service-bar .service-bar-link:hover { color: #ffd98a; }

    /* ---------- Navbar ---------- */
    .nimcs-navbar {
        top: 40px;
        padding: 12px 0;
        background: rgba(255, 255, 255, .98);
        border-bottom: 1px solid #E7EEEA;
        box-shadow: 0 4px 20px rgba(11, 31, 58, .04);
    }
    .nimcs-navbar.sticky { top: 40px !important; box-shadow: 0 14px 34px rgba(11, 31, 58, .09); }

    /* Brand */
    .nimcs-navbar .nimcs-brand-mark {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(140deg, #0B1F3A 0%, #0E2C52 55%, #087A4B 130%);
        box-shadow: 0 10px 22px rgba(11, 31, 58, .22);
    }
    .nimcs-navbar .nimcs-brand-mark::after {
        content: "";
        position: absolute;
        top: 4px;
        right: 4px;
        width: 9px;
        height: 9px;
        border-radius: 3px;
        background: #C7922B;
    }
    .nimcs-navbar .nimcs-brand-mark .material-symbols-outlined { font-size: 24px; }
    .nimcs-navbar .brand-name {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: .01em;
        color: #0B1F3A;
        line-height: 1.2;
    }
    .nimcs-navbar .brand-sub {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: .02em;
        color: #66746E;
        line-height: 1.3;
    }

    /* Nav links */
    .nimcs-navbar .navbar-nav .nav-link {
        font-size: .94rem;
        font-weight: 600;
        color: #33413B;
        padding: .55rem .9rem;
        margin-inline: .1rem;
        border-radius: 10px;
        transition: color .15s ease, background-color .15s ease;
    }
    .nimcs-navbar .navbar-nav .nav-link:hover { color: #055E3A; background: #F0FAF5; }
    .nimcs-navbar .navbar-nav .nav-link.active { color: #055E3A; background: #E3F4EC; font-weight: 700; }

    /* Buttons */
    .nimcs-navbar .btn-nimcs-outline {
        color: #055E3A;
        border: 1.5px solid #BFE3D2;
        background: #fff;
        white-space: nowrap;
        transition: all .15s ease;
    }
    .nimcs-navbar .btn-nimcs-outline:hover { color: #055E3A; background: #F0FAF5; border-color: #8FCEB0; }
    .nimcs-navbar .btn-nimcs-solid {
        background: linear-gradient(135deg, #087A4B, #055E3A);
        border: none;
        box-shadow: 0 10px 20px rgba(8, 122, 75, .28);
        white-space: nowrap;
        transition: all .15s ease;
    }
    .nimcs-navbar .btn-nimcs-solid:hover { background: linear-gradient(135deg, #055E3A, #04462B); box-shadow: 0 12px 24px rgba(8, 122, 75, .34); }

    /* Mobile */
    @media (max-width: 620px) {
        .nimcs-service-bar { display: none; }
        .nimcs-navbar, .nimcs-navbar.sticky { top: 0 !important; }
        .nimcs-navbar .brand-sub { display: none; }
        .nimcs-navbar .navbar-collapse { margin-top: .5rem; }
    }
</style>
