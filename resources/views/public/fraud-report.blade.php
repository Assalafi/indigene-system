@extends('layouts.public')

@section('title', 'Report Suspected Fraud')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 720px;">
            <div class="section-heading">
                <h2 style="color:#0b1f3a;">Report suspected certificate fraud</h2>
                <p style="color:#66746e;">Your report is confidential and reviewed by the issuing authority.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    @include('partials.flash-messages')

                    <form method="POST" action="{{ route('fraud-reports.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="certificate_number" class="form-label">Certificate number <span class="text-secondary">(optional)</span></label>
                            <input class="form-control cert-number-input" id="certificate_number" name="certificate_number"
                                   type="text" maxlength="80" placeholder="e.g. DAM-2026-000001"
                                   value="{{ old('certificate_number') }}">
                        </div>
                        <div class="mb-3">
                            <label for="reporter_name" class="form-label">Your name <span class="text-secondary">(optional)</span></label>
                            <input class="form-control" id="reporter_name" name="reporter_name" type="text" maxlength="180" value="{{ old('reporter_name') }}">
                        </div>
                        <div class="mb-3">
                            <label for="reporter_contact" class="form-label">Contact <span class="text-secondary">(optional)</span></label>
                            <input class="form-control" id="reporter_contact" name="reporter_contact" type="text" maxlength="180" value="{{ old('reporter_contact') }}">
                            <div class="form-text">Phone or email, used only to follow up on this report.</div>
                        </div>
                        <div class="mb-4">
                            <label for="report_text" class="form-label">What happened? <span class="required-indicator">Required</span></label>
                            <textarea class="form-control" id="report_text" name="report_text" rows="5" maxlength="4000" required>{{ old('report_text') }}</textarea>
                            @error('report_text')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <button class="btn btn-primary-div text-white fw-semibold px-4 py-2" type="submit">
                            <i class="ri-alarm-warning-line me-1"></i> Submit report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

