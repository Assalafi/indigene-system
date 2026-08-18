@extends('layouts.auth')

@section('title', 'New Password')

@section('content')
    <div class="text-center mb-4">
        <div class="nimcs-brand-mark mx-auto mb-3" style="width:64px;height:64px;border-radius:18px;display:flex;align-items:center;justify-content:center;color:#fff;background:linear-gradient(135deg,#087A4B,#0B1F3A);">
            <span class="material-symbols-outlined" style="font-size:30px;">key</span>
        </div>
        <h2 class="fw-bold mb-1">Choose a new password</h2>
        <p class="text-secondary mb-0">
            Your email and phone were verified. Set your new password to continue.
            All existing sessions will be revoked.
        </p>
    </div>

    <form method="POST" action="{{ route('password.new.store') }}">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">New password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="password" name="password" type="password"
                   autocomplete="new-password" required>
            <div class="form-text">Minimum 10 characters. Prefer a long passphrase.</div>
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="password_confirmation" name="password_confirmation"
                   type="password" autocomplete="new-password" required>
        </div>
        <button class="btn btn-primary-div text-white fw-semibold w-100 py-2 fs-16" type="submit">
            <i class="ri-key-2-line me-1"></i> Set new password
        </button>
    </form>

    <p class="text-center mt-4">
        <a href="{{ route('login') }}">Back to sign in</a>
    </p>
@endsection
