@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Staff identity and account security. Roles and LGA assignments are managed by System Admin.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">My Profile</span></li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Contact details</h5>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="full_name" name="full_name" type="text" maxlength="180" value="{{ old('full_name', $user->full_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input class="form-control phone-input" id="phone" name="phone" type="text" maxlength="20" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input class="form-control" type="text" value="{{ $user->email }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input class="form-control" type="text" value="{{ $user->primaryRoleName() }}" disabled>
                            </div>
                        </div>
                        <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold mt-3" type="submit">
                            <i class="ri-save-line me-1"></i> Save details
                        </button>
                    </form>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Change password</h5>
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="current_password" class="form-label">Current password</label>
                                <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                            </div>
                            <div class="col-md-4">
                                <label for="password" class="form-label">New password</label>
                                <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>
                                <div class="form-text">Minimum 10 characters.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="password_confirmation" class="form-label">Confirm password</label>
                                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary-div rounded-3 fw-semibold mt-3" type="submit">
                            <i class="ri-key-2-line me-1"></i> Change password
                        </button>
                    </form>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-1">Account security</h5>
                    <p class="small text-secondary mb-0">
                        Password changes and privileged actions are recorded in the audit trail.
                        Sessions are revoked automatically on password reset or account suspension.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Role &amp; assignments</h5>
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        @foreach ($user->roles as $role)
                            <span class="status-badge status-submitted">{{ $role->name }}</span>
                        @endforeach
                    </div>
                    <ul class="list-unstyled mb-0">
                        @forelse ($user->assignments as $assignment)
                            <li class="border rounded-3 p-2 mb-2 small">
                                <strong>{{ $assignment->lga?->name ?? '—' }}</strong>
                                <div class="text-secondary">
                                    {{ ucfirst($assignment->assignment_type) }} &middot;
                                    {{ optional($assignment->starts_at)->format('d/m/Y') }} &rarr; {{ optional($assignment->ends_at)->format('d/m/Y') ?? 'open' }}
                                    &middot; {{ $assignment->status }}
                                </div>
                            </li>
                        @empty
                            <li class="text-secondary small">No LGA assignments.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Recent security events</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead><tr><th>Event</th><th>Result</th><th>Time</th></tr></thead>
                            <tbody>
                                @forelse ($recentSecurity as $event)
                                    <tr>
                                        <td>{{ $event->event_type }}</td>
                                        <td>@include('partials.status-badge', ['status' => $event->success ? 'approved' : 'rejected'])</td>
                                        <td>{{ $event->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-3">No events.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Sessions</h5>
                    <p class="small text-secondary mb-0">
                        Sessions are revoked automatically on password reset or account suspension.
                        System Administrators can expire your sessions if a compromise is suspected.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
