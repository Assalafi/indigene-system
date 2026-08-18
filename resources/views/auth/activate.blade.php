@extends('layouts.auth')

@section('title', 'Activate Account')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Activate your staff account</h2>
        <p class="text-secondary mb-0">
            Welcome, {{ $user->full_name }}. Create your password to activate your account.
        </p>
        <p class="text-secondary small mb-0">
            Role: {{ $user->primaryRoleName() }}@if ($user->activeLga()) &middot; LGA: {{ $user->activeLga()->name }}@endif
        </p>
    </div>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('activation.store', ['token' => $token]) }}">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">New password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="password" name="password" type="password"
                   autocomplete="new-password" required>
            <div class="form-text">Minimum 10 characters. Prefer a long passphrase.</div>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="password_confirmation" name="password_confirmation"
                   type="password" autocomplete="new-password" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone number <span class="text-secondary">(optional)</span></label>
            <input class="form-control form-control-lg phone-input" id="phone" name="phone" type="text"
                   placeholder="+2348012345678">
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" value="1" required>
            <label class="form-check-label small" for="accept_terms">
                I acknowledge the acceptable-use policy: I will only access records within my assigned
                role and LGA, and I understand every action is recorded in the audit trail.
                <span class="required-indicator">Required</span>
            </label>
        </div>
        <button class="btn btn-primary-div text-white fw-semibold w-100 py-2 fs-16" type="submit">
            <i class="ri-check-double-line me-1"></i> Activate account
        </button>
    </form>
@endsection
