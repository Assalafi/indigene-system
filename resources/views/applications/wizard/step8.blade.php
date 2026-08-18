@extends('layouts.app')

@section('title', 'Step 8: Review and Declaration')
@section('page-title', 'New Application - Step 8 of 8')
@section('page-subtitle', 'Review all data, then submit for approval')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <h5 class="form-section-title mb-0" style="border:0;padding:0;">
                            <span class="material-symbols-outlined">rate_review</span>
                            Review
                        </h5>
                        <div class="small text-secondary">
                            Registry: <strong>{{ $indigene->registry_number }}</strong> &middot;
                            Application: <strong>{{ $application->application_number }}</strong>
                        </div>
                    </div>

                    <h6 class="fw-semibold text-brand-navy mb-3">Identity</h6>
                    <dl class="review-grid mb-4">
                        <div class="review-item"><dt>NIN</dt><dd class="nin-mask">{{ $indigene->maskedNin() }}</dd></div>
                        <div class="review-item"><dt>Full name</dt><dd>{{ $profile->displayName() ?: '—' }}</dd></div>
                        <div class="review-item"><dt>Date of birth</dt><dd>{{ $profile->date_of_birth?->format('d/m/Y') ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Sex</dt><dd>{{ ucfirst($profile->sex ?: '—') }}</dd></div>
                        <div class="review-item"><dt>Marital status</dt><dd>{{ $profile->marital_status ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Nationality</dt><dd>{{ $profile->nationality ?? 'Nigerian' }}</dd></div>
                    </dl>

                    <h6 class="fw-semibold text-brand-navy mb-3">Place of origin</h6>
                    <dl class="review-grid mb-4">
                        <div class="review-item"><dt>State / LGA</dt><dd>{{ $state->name }} / {{ $lga->name }}</dd></div>
                        <div class="review-item"><dt>District</dt><dd>{{ $profile->district?->name ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Ward</dt><dd>{{ $profile->ward?->name ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Village / community unit</dt><dd>{{ $profile->unit?->name ?? '—' }}</dd></div>
                    </dl>

                    <h6 class="fw-semibold text-brand-navy mb-3">Contact and residence</h6>
                    <dl class="review-grid mb-4">
                        <div class="review-item"><dt>Phone</dt><dd>{{ $profile->phone ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Email</dt><dd>{{ $profile->email ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Address</dt><dd>{{ $profile->residential_address ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Town</dt><dd>{{ $profile->residence_town ?? '—' }}</dd></div>
                    </dl>

                    <h6 class="fw-semibold text-brand-navy mb-3">Family, guardian and next of kin</h6>
                    <dl class="review-grid mb-4">
                        @foreach ($profile->relations as $relation)
                            <div class="review-item">
                                <dt>{{ $relation->relationTypeLabel() }}</dt>
                                <dd>
                                    {{ $relation->full_name }}
                                    @if ($relation->relationship) &middot; {{ $relation->relationship }} @endif
                                    @if ($relation->phone) &middot; {{ $relation->phone }} @endif
                                </dd>
                            </div>
                        @endforeach
                        @if ($profile->relations->isEmpty())
                            <div class="review-item"><dt>Relations</dt><dd>None recorded</dd></div>
                        @endif
                        <div class="review-item"><dt>Indigene basis</dt><dd class="text-truncate-2">{{ $profile->indigene_basis ?? '—' }}</dd></div>
                    </dl>

                    <h6 class="fw-semibold text-brand-navy mb-3">Supporting documents ({{ $application->documents->count() }})</h6>
                    <ul class="list-unstyled mb-4">
                        @forelse ($application->documents as $doc)
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <i class="ri-file-line me-1"></i>
                                    <span class="fw-semibold">{{ $doc->documentTypeLabel() }}</span>
                                    <span class="small text-secondary ms-2">{{ $doc->fileAsset->original_name }}</span>
                                </div>
                                <span class="status-badge status-submitted"><span class="material-symbols-outlined">shield_check</span>{{ $doc->fileAsset->status === 'quarantined' ? 'Quarantined' : 'Stored privately' }}</span>
                            </li>
                        @empty
                            <li class="text-danger small">No documents uploaded. Go back to step 7.</li>
                        @endforelse
                    </ul>

                    <div class="alert alert-warning d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined">warning</span>
                        <div>
                            <strong>Before submission:</strong> confirm the applicant has reviewed every field above.
                            Submission routes the application to
                            <strong>{{ $application->routeTarget() }}</strong>
                            and blocks further edits to this version.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 8]) }}">
                        @csrf
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="declaration" name="declaration" value="1" required>
                            <label class="form-check-label" for="declaration">
                                I confirm the applicant (or their authorised representative) has reviewed this
                                information, that it is accurate to the best of my knowledge, and that the notice
                                and privacy acknowledgements were captured. I understand this submission is recorded
                                in the audit trail. <span class="required-indicator">Required</span>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 7]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back to documents
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                <i class="ri-send-plane-line me-1"></i> Submit for approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
