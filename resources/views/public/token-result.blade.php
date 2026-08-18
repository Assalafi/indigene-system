@extends('layouts.public')

@section('title', 'Certificate Verification')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 760px;">
            @if ($result)
                @php
                    $tone = strtolower($result['status']) === 'valid' ? 'valid' : (in_array(strtolower($result['status']), ['suspended', 'superseded']) ? 'warning' : 'invalid');
                @endphp
                <div class="verify-result-banner {{ $tone }}">
                    <span class="material-symbols-outlined">{{ $tone === 'valid' ? 'verified' : ($tone === 'warning' ? 'warning' : 'gpp_maybe') }}</span>
                    <h2 class="fw-bold mt-2 mb-1">{{ $result['status'] }}</h2>
                    <p class="mb-0 opacity-75">Certificate {{ $result['certificate_number'] }}</p>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-body p-4">
                        <dl class="review-grid">
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
                            <a href="{{ route('certificates.verify.form') }}" class="btn btn-outline-secondary">
                                <i class="ri-search-line me-1"></i> Verify another certificate
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="verify-result-banner invalid">
                    <span class="material-symbols-outlined">gpp_maybe</span>
                    <h2 class="fw-bold mt-2 mb-1">INVALID</h2>
                    <p class="mb-0 opacity-75">This QR code does not resolve to a valid certificate.</p>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('fraud-reports.create') }}" class="btn btn-outline-danger">
                        <i class="ri-alarm-warning-line me-1"></i> Report suspected fraud
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection

