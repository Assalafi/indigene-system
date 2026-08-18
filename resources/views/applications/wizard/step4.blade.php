@extends('layouts.app')

@section('title', 'Step 4: Contact and Residence')
@section('page-title', 'New Application - Step 4 of 8')
@section('page-subtitle', 'Contact and residence &middot; <span id="autosave-indicator" class="text-success">Draft autosaves after a short pause</span>')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">contact_phone</span>
                        Contact and residence
                    </h5>
                    <p class="text-secondary small">
                        Residence details support contact and reporting. They do not affect origin
                        scope or the certificate issuing authority.
                    </p>

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 4]) }}" data-autosave>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="required-indicator">Required</span></label>
                                <input class="form-control phone-input" id="phone" name="phone" type="text" maxlength="20"
                                       placeholder="+2348012345678" value="{{ old('phone', $profile->phone) }}" required>
                                @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="email" name="email" type="email" maxlength="190"
                                       value="{{ old('email', $profile->email) }}">
                            </div>
                            <div class="col-12">
                                <label for="residential_address" class="form-label">Residential address <span class="required-indicator">Required</span></label>
                                <textarea class="form-control" id="residential_address" name="residential_address" rows="3"
                                          maxlength="1000" required>{{ old('residential_address', $profile->residential_address) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="residence_town" class="form-label">Town <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="residence_town" name="residence_town" type="text" maxlength="150"
                                       value="{{ old('residence_town', $profile->residence_town) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="residence_state_id" class="form-label">Residence state <span class="text-secondary">(optional)</span></label>
                                <select class="form-select" id="residence_state_id" name="residence_state_id">
                                    <option value="">—</option>
                                    @foreach ($residenceStates as $resState)
                                        <option value="{{ $resState->id }}" @selected($profile->residence_state_id === $resState->id)>{{ $resState->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="residence_lga_id" class="form-label">Residence LGA <span class="text-secondary">(optional)</span></label>
                                <select class="form-select" id="residence_lga_id" name="residence_lga_id">
                                    <option value="">—</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 3]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to family <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
