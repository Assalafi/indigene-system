@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Reset your password</h2>
        <p class="text-secondary mb-0">
            Enter the email address and phone number on file for your staff account.
            If they match, you can set a new password immediately — no email link needed.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
            <span class="material-symbols-outlined">check_circle</span>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Staff email <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="email" name="email" type="email"
                   value="{{ old('email') }}" required autofocus>
            @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label for="phone" class="form-label">Phone number on file <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg phone-input" id="phone" name="phone" type="text"
                   placeholder="e.g. 08012345678 or +2348012345678" maxlength="20" value="{{ old('phone') }}" required>
            @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
        <button class="btn btn-primary-div text-white fw-semibold w-100 py-2 fs-16" type="submit">
            <i class="ri-arrow-right-line me-1"></i> Continue to new password
        </button>
    </form>

    <p class="text-center mt-4">
        <a href="{{ route('login') }}">Back to sign in</a>
    </p>
@endsection
