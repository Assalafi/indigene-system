@extends('layouts.public')

@section('title', 'Home')

@section('meta-description', 'Register and verify approved Nigerian indigene certificates securely.')

@section('content')
<div class="nimcs-landing">
    <!-- Start Banner Area -->
    <div class="banner-area position-relative z-1" id="home">
        <div class="container position-relative z-1">
            <div class="banner-content text-center">
                <span class="top-title">
                    <span>Official National Service</span>
                </span>
                <h1 class="fs-60 mb-3 pb-md-3">A trusted digital register for every approved indigene.</h1>
                <p class="fs-18 m-auto mb-3 pb-md-3 mw-740">
                    Register indigene records through the authorised Local Government workflow and verify
                    issued certificates instantly using a certificate number or secure QR code.
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="#verify-certificate" class="btn btn-primary-div py-2 px-4 fs-16 fw-medium rounded-3 text-white">
                        <i class="ri-qr-scan-line fs-18 position-relative top-2"></i>
                        <span class="ms-1">Verify a certificate</span>
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary-div py-2 px-4 fs-16 fw-medium rounded-3">
                        <i class="ri-login-box-line fs-18 position-relative top-2"></i>
                        <span class="ms-1">Access staff portal</span>
                    </a>
                </div>
                <ul class="ps-0 mb-0 list-unstyled d-flex flex-wrap justify-content-center gap-4 mt-4 trust-line">
                    <li>LGA-controlled approval</li>
                    <li>Trackable print copies</li>
                    <li>Privacy-protected records</li>
                </ul>
            </div>

            <div class="banner-cert-wrap text-center">
                <div class="cert-mock">
                    <div class="cert-mock-inner">
                        <div class="cert-mock-top">
                            <span class="cert-mock-coat">
                                <span class="material-symbols-outlined">account_balance</span>
                            </span>
                            <span class="cert-mock-number">DAM-2026-000001<br>
                                <small>ORIGINAL - COPY 01</small>
                            </span>
                        </div>
                        <div class="cert-mock-heading">DAMBOA LOCAL GOVERNMENT<br>BORNO STATE</div>
                        <div class="cert-mock-title">INDIGENE CERTIFICATE</div>
                        <div class="cert-mock-body">
                            <div class="cert-mock-statement">
                                This is to certify that<br>
                                <strong>ALH. MUSTAPHA ABDUL</strong><br>
                                is a bona fide indigene of Damboa Local Government
                                Area, from Ajigin, Ajigin Ward.
                            </div>
                            <div class="cert-mock-photo">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                        </div>
                        <div class="cert-mock-bottom">
                            <div class="cert-mock-qr">
                                <span class="material-symbols-outlined">qr_code_2</span>
                                <small>Scan to verify</small>
                            </div>
                            <div class="cert-mock-sign">
                                <div class="sign-line"></div>
                                <small>Executive Chairman</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="float-chip chip-verified">
                    <span class="material-symbols-outlined">verified</span>
                    <span><strong>VALID</strong><small>Verified at the source</small></span>
                </div>
                <div class="float-chip chip-print">
                    <span class="material-symbols-outlined">print</span>
                    <span><strong>Print tracked</strong><small>COPY 01 of 01</small></span>
                </div>
            </div>
        </div>

        <div class="landing-blob blob-1"></div>
        <div class="landing-blob blob-2"></div>
    </div>
    <!-- End Banner Area -->

    <!-- Start Key Features Area -->
    <div class="key-features-area pb-125 position-relative z-2" id="features">
        <div class="container">
            <div class="section-title">
                <span class="top-title">
                    <span>Key Features</span>
                </span>
                <h2>Official records with privacy and accountability designed in</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="key-features-single-item">
                        <i class="material-symbols-outlined nimcs-feature-icon d-inline-flex align-items-center justify-content-center">how_to_vote</i>
                        <h3>LGA-Controlled Approval</h3>
                        <p>Every record passes through the assigned LGA Chairman or System Admin. Separation of duties blocks self-approval.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="key-features-single-item">
                        <i class="material-symbols-outlined nimcs-feature-icon d-inline-flex align-items-center justify-content-center">qr_code_scanner</i>
                        <h3>QR Verification</h3>
                        <p>Each certificate carries a secure QR code and unique number verified publicly in seconds, free of charge.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="key-features-single-item">
                        <i class="material-symbols-outlined nimcs-feature-icon d-inline-flex align-items-center justify-content-center">lock_person</i>
                        <h3>Privacy-Protected</h3>
                        <p>NIN is encrypted and masked by default. It never appears in URLs, QR codes, exports, notifications or logs.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="key-features-single-item mb-25 mb-0">
                        <i class="material-symbols-outlined nimcs-feature-icon d-inline-flex align-items-center justify-content-center">receipt_long</i>
                        <h3>Trackable Print Copies</h3>
                        <p>Every printable copy is numbered and recorded: COPY 01 original, COPY 02+ reprints with a reason.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="key-features-single-item mb-25 mb-0">
                        <i class="material-symbols-outlined nimcs-feature-icon d-inline-flex align-items-center justify-content-center">map</i>
                        <h3>Nationwide Geography</h3>
                        <p>All 36 states, the FCT and 774 LGAs seeded, with wards and village units signed off per LGA.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="key-features-single-item mb-25 mb-0">
                        <i class="material-symbols-outlined nimcs-feature-icon d-inline-flex align-items-center justify-content-center">shield_person</i>
                        <h3>Immutable Audit Trail</h3>
                        <p>Approvals, certificate versions, revocations and prints are recorded permanently and can never be edited away.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Key Features Area -->

    <!-- Start How It Works Area -->
    <div class="how-works-area position-relative z-1 pb-125" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <span class="top-title">
                    <span>How it works</span>
                </span>
                <h2>A clear, accountable path to certification</h2>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="hw-card">
                        <div class="hw-step">
                            <span class="hw-num">1</span>
                            <span class="hw-icon material-symbols-outlined">person_add</span>
                        </div>
                        <h3>Register</h3>
                        <p>An authorised Officer captures bio data, place of origin, family details and required evidence in a guided eight-step wizard.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="hw-card">
                        <div class="hw-step">
                            <span class="hw-num">2</span>
                            <span class="hw-icon material-symbols-outlined">how_to_vote</span>
                        </div>
                        <h3>Review and approve</h3>
                        <p>The LGA Chairman or authorised System Admin checks the evidence, resolves any duplicate flags and records the decision.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="hw-card">
                        <div class="hw-step">
                            <span class="hw-num">3</span>
                            <span class="hw-icon material-symbols-outlined">verified</span>
                        </div>
                        <h3>Issue and verify</h3>
                        <p>The approved record receives a numbered certificate with a QR code anyone can check on the public verification page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End How It Works Area -->

    <!-- Start Verify + Trust Area -->
    <div class="verify-trust-area pb-125 position-relative z-2" id="trust">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="verify-panel" id="verify-certificate">
                        <span class="top-title">
                            <span>Public Verification</span>
                        </span>
                        <h2>Check a certificate in seconds</h2>
                        <p>Enter the certificate number exactly as printed on the document. Verification is free and does not require login.</p>

                        <form class="verify-form" method="POST" action="{{ route('certificates.verify') }}">
                            @csrf
                            <label for="certificate_number">Certificate number</label>
                            <input class="form-control h-55" id="certificate_number" name="certificate_number"
                                   type="text" inputmode="text" autocomplete="off" maxlength="80"
                                   placeholder="e.g. DAM-2026-000001" value="{{ old('certificate_number') }}" required>
                            @error('certificate_number')
                                <span role="alert" style="color:#b42318">{{ $message }}</span>
                            @enderror
                            <button class="btn btn-primary-div py-2 px-4 fs-16 fw-medium rounded-3 text-white" type="submit">
                                Check certificate status
                            </button>
                            <span class="verify-help">Do not enter a NIN. Results show only the minimum public information.</span>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="tailor-content">
                        <span class="top-title">
                            <span>Built for public trust</span>
                        </span>
                        <h2>Verified at the source, protected end to end</h2>
                        <ul class="ps-0 mb-0 list-unstyled">
                            <li>
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="material-symbols-outlined fs-20 text-primary">done_outline</i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3>Role-based access</h3>
                                        <p>Officers, Chairmen and System Administrators each see only their authorised scope.</p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="material-symbols-outlined fs-20 text-primary">done_outline</i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3>Unique numbers, one source of truth</h3>
                                        <p>Certificates are numbered sequentially per LGA and can never be reused or duplicated.</p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="material-symbols-outlined fs-20 text-primary">done_outline</i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3>Immutable history</h3>
                                        <p>Every approval, version, reprint and revocation stays permanently on record.</p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="material-symbols-outlined fs-20 text-primary">done_outline</i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3>Masked NIN, logged access</h3>
                                        <p>Sensitive data is hidden by default and every privileged reveal is recorded.</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Verify + Trust Area -->

    <!-- Start FAQ Area -->
    <div class="faq-arae position-relative z-1 pb-125" id="faqs">
        <div class="container">
            <div class="section-title mw-630">
                <span class="top-title">
                    <span>FAQ&rsquo;s</span>
                </span>
                <h2>Frequently asked questions</h2>
            </div>

            <div class="accordion faq-wrapper mw-740 m-auto" id="faqAccordion">
                <div class="accordion-item mb-3 border-0 bg-white">
                    <h2 class="accordion-header">
                        <button class="accordion-button text-secondary bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="true" aria-controls="faqOne">
                            How do I verify a certificate?
                        </button>
                    </h2>
                    <div id="faqOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Enter the certificate number printed at the top-right of the document on the Verify page, or scan the QR code. The result shows VALID, SUSPENDED, SUPERSEDED or REVOKED together with the holder's name and issuing LGA.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item mb-3 border-0 bg-white">
                    <h2 class="accordion-header">
                        <button class="accordion-button text-secondary bg-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                            Will my NIN be shown publicly?
                        </button>
                    </h2>
                    <div id="faqTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>No. Your NIN is encrypted, masked by default and never appears in URLs, QR codes, exports, notifications or logs. Public verification shows only the minimum information needed to validate a certificate.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item mb-3 border-0 bg-white">
                    <h2 class="accordion-header">
                        <button class="accordion-button text-secondary bg-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                            How do I get an indigene certificate?
                        </button>
                    </h2>
                    <div id="faqThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Visit the indigene desk of your Local Government Area. An authorised Officer registers your details, and the LGA Chairman or a System Admin approves the record before a certificate can be issued.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item mb-3 border-0 bg-white">
                    <h2 class="accordion-header">
                        <button class="accordion-button text-secondary bg-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
                            What if my details change?
                        </button>
                    </h2>
                    <div id="faqFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Material changes to name, date of birth, NIN, place of origin or photograph require a formal amendment application at your LGA, followed by reapproval. Your previous record and certificates stay on file.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item mb-3 border-0 bg-white">
                    <h2 class="accordion-header">
                        <button class="accordion-button text-secondary bg-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFive" aria-expanded="false" aria-controls="faqFive">
                            I suspect a fraudulent certificate. What should I do?
                        </button>
                    </h2>
                    <div id="faqFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Use the "Report suspected fraud" link on any verification result, or the fraud report page. Your report is reviewed by the issuing authority and resolved on record.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End FAQ Area -->

    <!-- Start Unlock Area -->
    <div class="unlock-area ptb-150 position-relative z-1">
        <div class="container">
            <div class="border-bottom pb-150">
                <div class="row">
                    <div class="unlock-content">
                        <span class="top-title">
                            <span>Authorised staff</span>
                        </span>
                        <h2>Register, review, approve and print within your assigned LGA.</h2>
                        <p>
                            Staff access is granted by the System Administrator. Every action is scoped to your
                            LGA and recorded in the audit trail.
                        </p>
                        <a href="{{ route('login') }}" class="btn btn-primary-div py-2 px-4 fs-16 fw-medium rounded-3 text-white">
                            <i class="ri-login-box-line fs-18"></i>
                            <span class="ms-1">Open staff portal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="landing-blob blob-3"></div>
        <div class="landing-blob blob-4"></div>
    </div>
    <!-- End Unlock Area -->
</div>
@endsection

@push('styles')
<style>
    /* ---------- NIMCS landing scope overrides ---------- */
    .nimcs-landing { overflow-x: clip; }

    .nimcs-landing .banner-area {
        padding-top: 215px;
        padding-bottom: 150px;
        background:
            radial-gradient(circle at 88% 12%, rgba(199, 146, 43, .16), transparent 30%),
            radial-gradient(circle at 8% 70%, rgba(8, 122, 75, .12), transparent 32%),
            linear-gradient(180deg, #fbfdfc 0%, #f3f8f5 100%);
    }

    .nimcs-landing .banner-content .fs-60 {
        max-width: 980px;
        margin-inline: auto;
        letter-spacing: -.04em;
        color: #0b1f3a;
    }
    .nimcs-landing .banner-content { margin-bottom: 75px; }
    .nimcs-landing .banner-content p.fs-18 { color: #4d5c55; }

    .nimcs-landing .top-title { color: #055e3a; border-color: #055e3a; }
    .nimcs-landing .top-title::before, .nimcs-landing .top-title::after { background-color: #055e3a; }
    .nimcs-landing .top-title span::before, .nimcs-landing .top-title span::after { background-color: #055e3a; }

    .nimcs-landing .btn-primary-div { background: #087a4b; border-color: #087a4b; }
    .nimcs-landing .btn-primary-div:hover { background: #055e3a; border-color: #055e3a; }
    .nimcs-landing .btn-outline-primary-div { color: #055e3a; border-color: #055e3a; }
    .nimcs-landing .btn-outline-primary-div:hover { color: #fff; background: #055e3a; }
    .nimcs-landing .text-primary { color: #087a4b !important; }

    /* ---------- Key feature icons (distinct tint vs glyph) ---------- */
    .nimcs-landing .nimcs-feature-icon {
        width: 86px;
        height: 86px;
        border-radius: 18px;
        font-size: 42px;
        color: #055E3A;
        background: #E3F4EC;
        border: 1px solid #C5E8D5;
        box-shadow: inset 0 0 0 4px #FFFFFF, 0 12px 26px rgba(8, 122, 75, .12);
        transition: all .25s ease;
    }
    .nimcs-landing .key-features-single-item:hover .nimcs-feature-icon {
        color: #fff;
        background: linear-gradient(135deg, #087A4B, #055E3A);
        border-color: #087A4B;
        box-shadow: 0 14px 30px rgba(8, 122, 75, .28);
        transform: translateY(-3px);
    }

    .nimcs-landing .trust-line li { color: #66746e; font-weight: 600; }
    .nimcs-landing .trust-line li::before { content: "\2713"; margin-right: .45rem; color: #087a4b; font-weight: 900; }

    /* ---------- Certificate mock ---------- */
    .banner-cert-wrap { position: relative; max-width: 780px; margin: 0 auto; }
    .cert-mock {
        display: inline-block;
        padding: 14px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .8);
        box-shadow: 0 30px 80px rgba(11, 31, 58, .18);
        transform: rotate(-1.5deg);
        transition: transform .3s ease;
    }
    .cert-mock:hover { transform: rotate(0deg); }
    .cert-mock-inner {
        width: 640px;
        max-width: 82vw;
        padding: 26px 30px 22px;
        border: 3px double #087a4b;
        border-radius: 10px;
        background:
            repeating-linear-gradient(45deg, rgba(8, 122, 75, .035) 0 .8px, transparent .8px 4px),
            #ffffff;
        text-align: left;
    }
    .cert-mock-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .cert-mock-coat { display: inline-flex; font-size: 34px; color: #0b1f3a; }
    .cert-mock-number { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-align: right; }
    .cert-mock-number small { color: #66746e; }
    .cert-mock-heading {
        text-align: center;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: .06em;
        color: #0b1f3a;
        margin-top: 6px;
    }
    .cert-mock-title {
        text-align: center;
        font-size: 21px;
        font-weight: 800;
        color: #b42318;
        letter-spacing: .05em;
        margin: 10px 0 14px;
    }
    .cert-mock-body { display: flex; gap: 18px; align-items: flex-start; }
    .cert-mock-statement { flex: 1; font-size: 11.5px; line-height: 1.65; color: #15231d; }
    .cert-mock-statement strong { font-size: 13px; }
    .cert-mock-photo {
        width: 74px;
        height: 88px;
        border: 2px solid #087a4b;
        border-radius: 6px;
        background: #f4f7f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9fb0a8;
        font-size: 34px;
        flex-shrink: 0;
    }
    .cert-mock-bottom { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 16px; }
    .cert-mock-qr {
        width: 62px;
        height: 62px;
        border: 1px solid #0b1f3a;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        color: #0b1f3a;
    }
    .cert-mock-qr small { font-size: 7px; font-weight: 700; letter-spacing: .04em; }
    .cert-mock-sign { width: 150px; text-align: center; }
    .cert-mock-sign .sign-line { border-bottom: 1px solid #33413b; height: 26px; }
    .cert-mock-sign small { font-size: 9px; color: #4d5c55; }

    .float-chip {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(11, 31, 58, .14);
        text-align: left;
        animation: floaty 5s ease-in-out infinite;
    }
    .float-chip .material-symbols-outlined { font-size: 26px; color: #087a4b; }
    .float-chip strong { display: block; font-size: 13px; color: #0b1f3a; line-height: 1.2; }
    .float-chip small { color: #66746e; font-size: 11px; }
    .chip-verified { top: 18%; left: -6%; }
    .chip-print { bottom: 14%; right: -7%; animation-delay: 2.5s; }
    @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    /* ---------- Blobs ---------- */
    .landing-blob { position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; z-index: -1; }
    .blob-1 { top: -120px; left: -140px; width: 380px; height: 380px; background: rgba(199, 146, 43, .18); }
    .blob-2 { top: 120px; right: -140px; width: 420px; height: 420px; background: rgba(8, 122, 75, .16); }
    .blob-3 { top: 10%; left: 10%; width: 320px; height: 320px; background: rgba(8, 122, 75, .12); }
    .blob-4 { bottom: 0; right: 5%; width: 300px; height: 300px; background: rgba(199, 146, 43, .12); }

    /* ---------- How it works cards ---------- */
    .hw-card {
        height: 100%;
        padding: 30px 26px;
        border: 1px solid #d9e2de;
        border-radius: 16px;
        background: #fff;
        transition: all .3s ease;
    }
    .hw-card:hover { box-shadow: 0 18px 44px rgba(11, 31, 58, .10); transform: translateY(-4px); }
    .hw-step { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .hw-num {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #087a4b;
        color: #fff;
        font-weight: 800;
        font-size: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .hw-icon { font-size: 30px; color: #c7922b; }
    .hw-card h3 { font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #0b1f3a; }
    .hw-card p { margin: 0; color: #66746e; }

    /* ---------- Verify panel ---------- */
    .verify-panel {
        padding: clamp(1.8rem, 4vw, 2.6rem);
        border: 1px solid rgba(217, 226, 222, .9);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(11, 31, 58, .10);
    }
    .verify-panel h2 { font-size: 28px; line-height: 1.35; letter-spacing: -.02em; margin: 14px 0 10px; color: #0b1f3a; }
    .verify-panel > p { color: #66746e; margin-bottom: 18px; }
    .verify-form { display: grid; gap: 12px; }
    .verify-form label { color: #15231d; font-weight: 700; font-size: .9rem; }
    .verify-form .form-control { text-transform: uppercase; }
    .verify-help { color: #66746e; font-size: .79rem; }

    /* ---------- FAQ tweaks ---------- */
    .nimcs-landing .faq-wrapper .accordion-button { font-weight: 600; }
    .nimcs-landing .faq-wrapper .accordion-button:not(.collapsed) { color: #055e3a; }
    .nimcs-landing .faq-wrapper .accordion-body p { margin: 0; }

    /* ---------- Unlock CTA ---------- */
    .nimcs-landing .unlock-content { text-align: center; }
    .nimcs-landing .unlock-content h2 { margin-bottom: 15px; }
    .nimcs-landing .unlock-content p { margin-bottom: 25px; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1200px) {
        .chip-verified { left: 0; }
        .chip-print { right: 0; }
    }
    @media (max-width: 860px) {
        .cert-mock-inner { width: 100%; max-width: 560px; }
        .float-chip { display: none; }
        .banner-area { padding-top: 190px; }
        .nimcs-landing .banner-content { margin-bottom: 45px; }
    }
    @media (max-width: 620px) {
        .nimcs-landing .banner-area { padding-top: 160px; padding-bottom: 90px; }
        .nimcs-landing .banner-content .fs-60 { font-size: 34px; }
        .cert-mock-body { flex-direction: column; }
        .cert-mock-photo { width: 100%; height: 120px; }
    }
</style>
@endpush
