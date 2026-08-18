<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Sign in') | {{ $brandShortName }}</title>
        @include('partials.styles')
    </head>
    <body class="auth-wrap">
        <div class="auth-wrap row g-0">
            <div class="col-lg-6 d-none d-lg-flex auth-brand-panel align-items-center justify-content-center p-5">
                <div class="auth-brand-inner text-center" style="max-width: 460px;">
                    <div class="nimcs-brand-mark mx-auto mb-4" style="width:72px;height:72px;border-radius:20px;display:flex;align-items:center;justify-content:center;color:#fff;background:linear-gradient(135deg,#087A4B,#0B1F3A);box-shadow:0 10px 30px rgba(0,0,0,.25);">
                        <span class="material-symbols-outlined" style="font-size:36px;">verified_user</span>
                    </div>
                    <h1 class="fs-2 fw-bold mb-3">Nigerian Indigene Management &amp; Certification System</h1>
                    <p class="fs-16 opacity-75 mb-4">
                        The authoritative workflow for indigene registration, LGA approval,
                        verifiable certificates and a permanent audit trail.
                    </p>
                    <ul class="list-unstyled text-start d-inline-block fs-14 opacity-75">
                        <li class="mb-2"><span class="material-symbols-outlined align-middle me-2">verified</span>LGA-controlled approval workflow</li>
                        <li class="mb-2"><span class="material-symbols-outlined align-middle me-2">qr_code_scanner</span>QR-based public certificate verification</li>
                        <li class="mb-2"><span class="material-symbols-outlined align-middle me-2">lock</span>Privacy-protected records and audit trail</li>
                    </ul>
                    <p class="fs-12 opacity-50 mt-4 mb-0">Powered and managed by Haigha Tech</p>
                </div>
            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5">
                <div class="w-100" style="max-width: 480px;">
                    @include('partials.flash-messages')
                    @yield('content')
                </div>
            </div>
        </div>

        @include('partials.scripts')
    </body>
</html>
