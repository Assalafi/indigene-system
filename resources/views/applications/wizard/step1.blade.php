@extends('layouts.app')

@section('title', 'Step 1: Notice and Authority')
@section('page-title', 'New Application - Step 1 of 8')
@section('page-subtitle', 'Application '.$application->application_number.' &middot; Draft')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">privacy_tip</span>
                        Notice and authority
                    </h5>
                    <p class="text-secondary">
                        Before collecting any data, the applicant (or their authorised representative)
                        must understand why the information is collected and how it is protected.
                    </p>

                    <div class="alert alert-info d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined">info</span>
                        <div>
                            <strong>Purpose of collection.</strong>
                            Data is collected solely to operate the official indigene register for
                            {{ $lga->name }} Local Government Area, {{ $state->name }} State, to route this
                            application through LGA approval, and to issue a verifiable indigene certificate.
                            The lawful basis is the performance of an official public-interest task.
                        </div>
                    </div>

                    <div class="alert alert-light border d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined">lock</span>
                        <div>
                            <strong>Privacy protections.</strong>
                            The NIN is encrypted and masked by default. It never appears in URLs, QR codes,
                            exports, notifications or logs. Access to records is restricted to your LGA and
                            every access is recorded.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 1]) }}">
                        @csrf
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="acknowledge_notice" name="acknowledge_notice" value="1"
                                   @if ($application->declaration_version) checked @endif>
                            <label class="form-check-label" for="acknowledge_notice">
                                The applicant acknowledges the purpose of collection and the authority under
                                which it is made. <span class="required-indicator">Required</span>
                            </label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="acknowledge_privacy" name="acknowledge_privacy" value="1"
                                   @if ($application->declaration_version) checked @endif>
                            <label class="form-check-label" for="acknowledge_privacy">
                                The privacy notice (version 1.0) has been explained and accepted.
                                <span class="required-indicator">Required</span>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span></span>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to identity <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
