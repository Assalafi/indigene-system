@extends('layouts.app')

@section('title', 'Create Staff User')
@section('page-title', 'Create Staff User')
@section('page-subtitle', 'Accounts are created by System Admin; a one-time activation link is issued. Passwords are never emailed.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Users</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Create</span></li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    @include('partials.flash-messages')

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="full_name" name="full_name" type="text" maxlength="180" value="{{ old('full_name') }}" required>
                                @error('full_name')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                            {{ $role->name }}@if ($role->description) - {{ $role->description }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="email" name="email" type="email" maxlength="190" value="{{ old('email') }}" required>
                                @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                                <input class="form-control phone-input" id="phone" name="phone" type="text" maxlength="20" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="state_id" class="form-label">State / FCT</label>
                                <select class="form-select" id="state_id"
                                        data-state-cascade data-lga-target="#lga_id"
                                        data-lga-url="{{ route('api.geography.lgas-by-state') }}">
                                    <option value="">Select state&hellip;</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="lga_id" class="form-label">LGA assignment</label>
                                <select class="form-select" id="lga_id" name="lga_id">
                                    <option value="">Select state first</option>
                                </select>
                                @error('lga_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_title" class="form-label">Appointment title</label>
                                <input class="form-control" id="appointment_title" name="appointment_title" type="text" maxlength="120" value="{{ old('appointment_title') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_reference" class="form-label">Appointment reference</label>
                                <input class="form-control" id="appointment_reference" name="appointment_reference" type="text" maxlength="100" value="{{ old('appointment_reference') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="starts_at" class="form-label">Starts</label>
                                <input class="form-control" id="starts_at" name="starts_at" type="date" value="{{ old('starts_at') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="ends_at" class="form-label">Ends</label>
                                <input class="form-control" id="ends_at" name="ends_at" type="date" value="{{ old('ends_at') }}">
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-start gap-2 mt-4">
                            <span class="material-symbols-outlined">mail_lock</span>
                            <div class="small">
                                A one-time activation link will be generated. The link is shown in the
                                application log in local development and delivered by the configured mailer
                                in production. Only one active primary Chairman per LGA is permitted.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-3 fw-semibold">Cancel</a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                <i class="ri-user-add-line me-1"></i> Create user
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
