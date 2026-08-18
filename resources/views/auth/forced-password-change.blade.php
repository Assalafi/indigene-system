@extends('layouts.auth')

@section('title', 'Change Password')

@section('content')
    <div class="text-center mb-4">
        <div class="nimcs-brand-mark mx-auto mb-3" style="width:64px;height:64px;border-radius:18px;display:flex;align-items:center;justify-content:center;color:#fff;background:linear-gradient(135deg,#087A4B,#0B1F3A);">
            <span class="material-symbols-outlined" style="font-size:30px;">password</span>
        </div>
        <h2 class="fw-bold mb-1">Update your password</h2>
        <p class="text-secondary mb-0">A new password is required before you can continue.</p>
    </div>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('password.change.store') }}">
        @csrf
        <div class="mb-3">
            <label for="current_password" class="form-label">Current password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="current_password" name="current_password"
                   type="password" autocomplete="current-password" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="password" name="password" type="password"
                   autocomplete="new-password" required>
            <div class="form-text">Minimum 10 characters. Prefer a long passphrase.</div>
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm new password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="password_confirmation" name="password_confirmation"
                   type="password" autocomplete="new-password" required>
        </div>
        <button class="btn btn-primary-div text-white fw-semibold w-100 py-2 fs-16" type="submit">
            <i class="ri-check-double-line me-1"></i> Update password
        </button>
    </form>
@endsection
