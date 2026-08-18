@extends('layouts.app')

@section('title', $report->reference_number)
@section('page-title', $report->reference_number)
@section('page-subtitle', 'Received '.$report->created_at->format('d/m/Y H:i'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.fraud-reports.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Fraud Reports</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $report->reference_number }}</span></li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Report details</h5>
                    <dl class="review-grid">
                        <div class="review-item"><dt>Reference</dt><dd>{{ $report->reference_number }}</dd></div>
                        <div class="review-item"><dt>Status</dt><dd>@include('partials.status-badge', ['status' => $report->status === 'open' ? 'pending_chairman' : 'approved'])</dd></div>
                        <div class="review-item"><dt>Certificate</dt>
                            <dd>
                                @if ($report->certificate)
                                    <a href="{{ route('certificates.show', $report->certificate) }}" class="fw-semibold">{{ $report->certificate->certificate_number }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="review-item"><dt>Reporter contact</dt>
                            <dd>
                                @php
                                    try { $contact = $report->reporter_contact_ciphertext ? decrypt($report->reporter_contact_ciphertext) : null; }
                                    catch (\Throwable) { $contact = null; }
                                @endphp
                                {{ $contact ?? 'Not provided' }}
                            </dd>
                        </div>
                    </dl>
                    <div class="border rounded-3 p-3 mt-3 bg-light">
                        <strong class="small text-secondary">Report text</strong>
                        <p class="mb-0 mt-1">{{ $report->report_text }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Resolution</h5>
                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.fraud-reports.resolve', $report) }}"
                              data-confirm="Resolve this fraud report? The resolution is recorded.">
                            @csrf
                            <textarea class="form-control mb-3" name="resolution" rows="5" maxlength="4000" required
                                      placeholder="Describe the investigation outcome and any action taken&hellip;"></textarea>
                            <button class="btn btn-primary-div text-white w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-check-double-line me-1"></i> Mark resolved
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success">
                            <div class="small text-secondary">Resolved {{ optional($report->resolved_at)->format('d/m/Y H:i') }}</div>
                            <p class="mb-0 mt-1">{{ $report->resolution }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
