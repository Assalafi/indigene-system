@extends('layouts.app')

@section('title', 'Step 6: Guardian and Next of Kin')
@section('page-title', 'New Application - Step 6 of 8')
@section('page-subtitle', 'Guardian (where required) and next of kin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">escalator_warning</span>
                        Guardian and next of kin
                    </h5>

                    @php
                        $guardian = $profile->relations()->where('relation_type', 'guardian')->first();
                        $nok = $profile->relations()->where('relation_type', 'next_of_kin')->first();
                        $isMinor = $profile->isMinor();
                    @endphp

                    <div class="alert alert-info d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined">info</span>
                        <div>
                            @if ($isMinor)
                                <strong>A guardian is mandatory:</strong> the applicant's date of birth makes them a minor.
                                Guardian details are required below.
                            @else
                                <strong>Guardian</strong> is required only for minors or legally dependent applicants.
                                <strong>Next of kin</strong> is required for every applicant.
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 6]) }}">
                        @csrf
                        <h6 class="fw-semibold mb-3">Guardian {{ $isMinor ? '(required)' : '(optional)' }}</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label for="guardian_name" class="form-label">Full name {!! $isMinor ? '<span class="required-indicator">Required</span>' : '' !!}</label>
                                <input class="form-control" id="guardian_name" name="guardian_name" type="text" maxlength="180"
                                       value="{{ old('guardian_name', $guardian?->full_name) }}" @required($isMinor)>
                            </div>
                            <div class="col-md-3">
                                <label for="guardian_relationship" class="form-label">Relationship {!! $isMinor ? '<span class="required-indicator">Required</span>' : '' !!}</label>
                                <input class="form-control" id="guardian_relationship" name="guardian_relationship" type="text" maxlength="80"
                                       value="{{ old('guardian_relationship', $guardian?->relationship) }}" @required($isMinor)>
                            </div>
                            <div class="col-md-4">
                                <label for="guardian_phone" class="form-label">Phone {!! $isMinor ? '<span class="required-indicator">Required</span>' : '' !!}</label>
                                <input class="form-control phone-input" id="guardian_phone" name="guardian_phone" type="text" maxlength="20"
                                       value="{{ old('guardian_phone', $guardian?->phone) }}" @required($isMinor)>
                            </div>
                            <div class="col-12">
                                <label for="guardian_address" class="form-label">Address <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="guardian_address" name="guardian_address" type="text" maxlength="1000"
                                       value="{{ old('guardian_address', $guardian?->address) }}">
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="guardian_is_nok" name="guardian_is_nok" value="1">
                            <label class="form-check-label small" for="guardian_is_nok">
                                The guardian is the same person as the next of kin.
                            </label>
                        </div>

                        <h6 class="fw-semibold mb-3">Next of kin <span class="required-indicator">(required for every applicant)</span></h6>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="nok_name" class="form-label">Full name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="nok_name" name="nok_name" type="text" maxlength="180"
                                       value="{{ old('nok_name', $nok?->full_name) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="nok_relationship" class="form-label">Relationship <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="nok_relationship" name="nok_relationship" type="text" maxlength="80"
                                       value="{{ old('nok_relationship', $nok?->relationship) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nok_phone" class="form-label">Phone <span class="required-indicator">Required</span></label>
                                <input class="form-control phone-input" id="nok_phone" name="nok_phone" type="text" maxlength="20"
                                       value="{{ old('nok_phone', $nok?->phone) }}" required>
                            </div>
                            <div class="col-12">
                                <label for="nok_address" class="form-label">Address <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="nok_address" name="nok_address" type="text" maxlength="1000"
                                       value="{{ old('nok_address', $nok?->address) }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 5]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to documents <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
