@extends('layouts.app')

@section('title', 'Step 2: Identity')
@section('page-title', 'New Application - Step 2 of 8')
@section('page-subtitle', 'Identity and biometric photograph &middot; <span id="autosave-indicator" class="text-success">Draft autosaves after a short pause</span>')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">badge</span>
                        Identity
                    </h5>

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 2]) }}"
                          enctype="multipart/form-data" data-autosave>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nin" class="form-label">National Identification Number (NIN) <span class="required-indicator">Required</span></label>
                                <input class="form-control nin-input" id="nin" name="nin" type="text" inputmode="numeric"
                                       maxlength="11" placeholder="11 digits" value="{{ old('nin') }}" required>
                                <div class="form-text">Stored encrypted; masked everywhere by default.</div>
                                @error('nin')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-secondary">(optional)</span></label>
                                <select class="form-select" id="title" name="title">
                                    <option value="">—</option>
                                    @foreach (['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Chief', 'Alhaji', 'Hajiya', 'Engr', 'Barr'] as $t)
                                        <option value="{{ $t }}" @selected($profile->title === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="surname" class="form-label">Surname <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="surname" name="surname" type="text" maxlength="100"
                                       value="{{ old('surname', $profile->surname) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="first_name" name="first_name" type="text" maxlength="100"
                                       value="{{ old('first_name', $profile->first_name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle name <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="middle_name" name="middle_name" type="text" maxlength="100"
                                       value="{{ old('middle_name', $profile->middle_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label">Date of birth <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="date_of_birth" name="date_of_birth" type="date"
                                       max="{{ now()->toDateString() }}"
                                       value="{{ old('date_of_birth', $profile->date_of_birth?->toDateString()) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sex" class="form-label">Sex <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="sex" name="sex" required>
                                    <option value="">Select</option>
                                    <option value="male" @selected(old('sex', $profile->sex) === 'male')>Male</option>
                                    <option value="female" @selected(old('sex', $profile->sex) === 'female')>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="marital_status" class="form-label">Marital status <span class="text-secondary">(optional)</span></label>
                                <select class="form-select" id="marital_status" name="marital_status">
                                    <option value="">—</option>
                                    @foreach (['Single', 'Married', 'Divorced', 'Separated', 'Widowed'] as $ms)
                                        <option value="{{ $ms }}" @selected($profile->marital_status === $ms)>{{ $ms }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nationality" class="form-label">Nationality</label>
                                <input class="form-control" id="nationality" name="nationality" type="text" maxlength="80"
                                       value="{{ old('nationality', $profile->nationality ?? 'Nigerian') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="photo" class="form-label">Applicant photograph <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required>
                                <div class="form-text">JPEG, PNG or WebP, max 5 MB. Face must be clearly visible.</div>
                                @error('photo')<span class="text-danger small">{{ $message }}</span>@enderror
                                @if ($profile->photoFile)
                                    <div class="mt-2 small text-success">
                                        <i class="ri-checkbox-circle-line"></i> Current photo uploaded
                                        ({{ $profile->photoFile->original_name }}).
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 1]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to place of origin <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
