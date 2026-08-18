@extends('layouts.app')

@section('title', 'Choose LGA')
@section('page-title', 'New Application - Choose LGA')
@section('page-subtitle', 'As a System Admin you may onboard applicants in any active LGA. Choose the issuing LGA to continue.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('applications.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Applications</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Choose LGA</span></li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('applications.create') }}">
                        <div class="mb-3">
                            <label for="state_id" class="form-label fw-semibold">State / FCT <span class="required-indicator">Required</span></label>
                            <select class="form-select" id="state_id" name="state_id"
                                    data-state-cascade data-lga-target="#lga_id"
                                    data-lga-url="{{ route('api.geography.lgas-by-state') }}" required autofocus>
                                <option value="">Select state&hellip;</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="lga_id" class="form-label fw-semibold">Issuing LGA <span class="required-indicator">Required</span></label>
                            <select class="form-select form-select-lg" id="lga_id" name="lga_id" required>
                                <option value="">Select state first, then LGA</option>
                            </select>
                            <div class="form-text">
                                State, LGA and geography options for this application are locked to the chosen LGA.
                            </div>
                        </div>
                        <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold mt-3" type="submit">
                            <i class="ri-arrow-right-line me-1"></i> Continue
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
