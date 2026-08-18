@extends('layouts.app')

@section('title', 'Step 5: Family and Indigene Basis')
@section('page-title', 'New Application - Step 5 of 8')
@section('page-subtitle', 'Family details and the basis of the indigene claim &middot; <span id="autosave-indicator" class="text-success">Draft autosaves after a short pause</span>')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">family_restroom</span>
                        Family and indigene basis
                    </h5>

                    @php
                        $father = $profile->relations()->where('relation_type', 'father')->first();
                        $mother = $profile->relations()->where('relation_type', 'mother')->first();
                    @endphp

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 5]) }}" data-autosave>
                        @csrf
                        <h6 class="fw-semibold mb-3">Father</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label for="father_name" class="form-label">Full name</label>
                                <input class="form-control" id="father_name" name="father_name" type="text" maxlength="180"
                                       value="{{ old('father_name', $father?->full_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="father_origin_lga" class="form-label">Origin LGA</label>
                                <input class="form-control" id="father_origin_lga" name="father_origin_lga" type="text" maxlength="150"
                                       value="{{ old('father_origin_lga', $father?->address) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="father_phone" class="form-label">Phone</label>
                                <input class="form-control phone-input" id="father_phone" name="father_phone" type="text" maxlength="20"
                                       value="{{ old('father_phone', $father?->phone) }}">
                            </div>
                        </div>

                        <h6 class="fw-semibold mb-3">Mother</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label for="mother_name" class="form-label">Full name</label>
                                <input class="form-control" id="mother_name" name="mother_name" type="text" maxlength="180"
                                       value="{{ old('mother_name', $mother?->full_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="mother_origin_lga" class="form-label">Origin LGA</label>
                                <input class="form-control" id="mother_origin_lga" name="mother_origin_lga" type="text" maxlength="150"
                                       value="{{ old('mother_origin_lga', $mother?->address) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="mother_phone" class="form-label">Phone</label>
                                <input class="form-control phone-input" id="mother_phone" name="mother_phone" type="text" maxlength="20"
                                       value="{{ old('mother_phone', $mother?->phone) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="indigene_basis" class="form-label">
                                Evidence / basis of indigene claim <span class="required-indicator">Required</span>
                            </label>
                            <textarea class="form-control" id="indigene_basis" name="indigene_basis" rows="4" maxlength="2000" required
                                      placeholder="e.g. Both parents are indigenes of this LGA; father's family compound is in the stated village.">{{ old('indigene_basis', $profile->indigene_basis) }}</textarea>
                            @error('indigene_basis')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 4]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to guardian <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
