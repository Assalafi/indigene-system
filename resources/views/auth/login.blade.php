@extends('layouts.auth')

@section('title', 'Staff Login')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Staff sign in</h2>
        <p class="text-secondary mb-0">Access is restricted to authorised government and Haigha staff.</p>
    </div>

    @if ($errors->has('email'))
        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
            <span class="material-symbols-outlined">error</span>
            <div>{{ $errors->first('email') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email or staff identifier <span class="required-indicator">Required</span></label>
            <input class="form-control form-control-lg" id="email" name="email" type="text"
                   value="{{ old('email') }}" autocomplete="username" required autofocus>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password <span class="required-indicator">Required</span></label>
                <a class="small" href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <input class="form-control form-control-lg mt-2" id="password" name="password" type="password"
                   autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary-div text-white fw-semibold w-100 py-2 fs-16" type="submit">
            <i class="ri-login-box-line me-1"></i> Sign in
        </button>
    </form>

    <p class="small text-secondary mt-4">
        Attempts are rate limited and recorded. By signing in you accept the acceptable-use policy.
    </p>
@endsection
