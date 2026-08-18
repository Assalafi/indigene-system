@extends('layouts.public')

@section('title', 'Verify a Certificate')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 860px;">
            <div class="section-heading">
                <p class="eyebrow" style="display:inline-flex;align-items:center;gap:.5rem;color:#055e3a;font-weight:800;letter-spacing:.11em;text-transform:uppercase;">Public verification</p>
                <h2 style="color:#0b1f3a;margin:.5rem 0 .7rem;">Check a certificate status</h2>
                <p style="color:#66746e;margin:0;">Verification is free, does not require login, and never shows private data.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    @include('partials.flash-messages')

                    <form method="POST" action="{{ route('certificates.verify') }}">
                        @csrf
                        <label for="certificate_number" class="fw-semibold mb-2">Certificate number</label>
                        <input class="form-control form-control-lg cert-number-input" id="certificate_number"
                               name="certificate_number" type="text" inputmode="text" autocomplete="off"
                               maxlength="80" placeholder="e.g. DAM-2026-000001"
                               value="{{ old('certificate_number') }}" required>
                        <div class="form-text">Enter the number exactly as printed at the top-right of the certificate.</div>
                        @error('certificate_number')
                            <span role="alert" class="text-danger small">{{ $message }}</span>
                        @enderror
                        <button class="btn btn-primary-div text-white fw-semibold px-4 py-2 mt-3" type="submit">
                            <i class="ri-qr-scan-line me-1"></i> Check certificate status
                        </button>
                    </form>

                    @if (isset($result) && $result)
                        <div class="mt-4 verify-result-banner {{ strtolower($result['status']) === 'valid' ? 'valid' : 'warning' }}">
                            <span class="material-symbols-outlined">{{ strtolower($result['status']) === 'valid' ? 'verified' : 'warning' }}</span>
                            <h2 class="fw-bold mt-2 mb-1">{{ $result['status'] }}</h2>
                            <p class="mb-0 opacity-75">Certificate {{ $result['certificate_number'] }}</p>
                        </div>

                        <dl class="review-grid mt-4">
                            <div class="review-item"><dt>Holder</dt><dd>{{ $result['holder_name'] }}</dd></div>
                            <div class="review-item"><dt>Issuing authority</dt><dd>{{ $result['issuing_lga'] }} LGA, {{ $result['issuing_state'] }} State</dd></div>
                            <div class="review-item"><dt>Ward</dt><dd>{{ $result['ward'] ?? '—' }}</dd></div>
                            <div class="review-item"><dt>Village / community unit</dt><dd>{{ $result['unit'] ?? '—' }}</dd></div>
                            <div class="review-item"><dt>Original issue date</dt><dd>{{ $result['issue_date'] ?? '—' }}</dd></div>
                            <div class="review-item"><dt>Last status update</dt><dd>{{ $result['last_status_update'] ?? '—' }}</dd></div>
                        </dl>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <a href="{{ route('fraud-reports.create') }}" class="btn btn-outline-danger">
                                <i class="ri-alarm-warning-line me-1"></i> Report suspected fraud
                            </a>
                            <button class="btn btn-outline-secondary no-print" onclick="window.print()">
                                <i class="ri-printer-line me-1"></i> Print verification receipt
                            </button>
                        </div>
                    @elseif (isset($result) && ! $result)
                        <div class="mt-4 verify-result-banner invalid">
                            <span class="material-symbols-outlined">gpp_maybe</span>
                            <h2 class="fw-bold mt-2 mb-1">INVALID</h2>
                            <p class="mb-0 opacity-75">No certificate matches this number. Check the number and try again.</p>
                        </div>
                        <p class="small text-secondary mt-3">
                            Suspect a problem?
                            <a href="{{ route('fraud-reports.create') }}" class="fw-semibold">Report suspected fraud</a>.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

