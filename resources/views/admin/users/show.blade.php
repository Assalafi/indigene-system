@extends('layouts.app')

@section('title', $user->full_name)
@section('page-title', $user->full_name)
@section('page-subtitle', $user->email.' &middot; '.ucfirst($user->status))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Users</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $user->full_name }}</span></li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-assignments">Assignments</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-security">Security events</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-authored">Authored actions</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-assignments">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>LGA</th><th>Role</th><th>Type</th><th>Starts</th><th>Ends</th><th>Status</th><th></th></tr></thead>
                                    <tbody>
                                        @forelse ($user->assignments as $assignment)
                                            <tr>
                                                <td>{{ $assignment->lga?->name ?? '—' }}</td>
                                                <td>{{ $assignment->role?->name ?? '—' }}</td>
                                                <td>{{ ucfirst($assignment->assignment_type) }}</td>
                                                <td>{{ optional($assignment->starts_at)->format('d/m/Y') ?? '—' }}</td>
                                                <td>{{ optional($assignment->ends_at)->format('d/m/Y') ?? 'Open' }}</td>
                                                <td>@include('partials.status-badge', ['status' => $assignment->status === 'active' ? 'approved' : 'revoked'])</td>
                                                <td>
                                                    @if ($assignment->status === 'active')
                                                        <form method="POST" action="{{ route('admin.users.assignments.end', $assignment) }}" class="d-flex gap-2"
                                                              data-confirm="End this assignment? The user loses scope immediately.">
                                                            @csrf
                                                            <input class="form-control form-control-sm" name="end_reason" type="text" placeholder="Reason (required)" maxlength="1000" required>
                                                            <button class="btn btn-sm btn-outline-danger flex-shrink-0" type="submit">End</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center text-secondary py-3">No assignments.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-security">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead><tr><th>Event</th><th>Success</th><th>Time</th></tr></thead>
                                    <tbody>
                                        @forelse ($loginEvents as $event)
                                            <tr>
                                                <td>{{ $event->event_type }}</td>
                                                <td>@include('partials.status-badge', ['status' => $event->success ? 'approved' : 'rejected'])</td>
                                                <td>{{ $event->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-secondary py-3">No login events.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-authored">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead><tr><th>Action</th><th>Object</th><th>Risk</th><th>Time</th></tr></thead>
                                    <tbody>
                                        @forelse ($authored as $log)
                                            <tr>
                                                <td>{{ $log->action }}</td>
                                                <td>{{ $log->auditable_type }} <code class="small">{{ substr($log->auditable_id ?? '', 0, 8) }}&hellip;</code></td>
                                                <td><span class="risk-chip {{ $log->risk_level === 'high' ? 'high' : ($log->risk_level === 'medium' ? 'medium' : 'low') }}">{{ $log->risk_level }}</span></td>
                                                <td>{{ $log->occurred_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-secondary py-3">No actions yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Account status</h5>
                    <form method="POST" action="{{ route('admin.users.status', $user) }}"
                          data-confirm="Change this account's status? The change is recorded in the audit log.">
                        @csrf
                        <select class="form-select mb-2" name="action" required>
                            <option value="">Select action&hellip;</option>
                            <option value="suspend">Suspend</option>
                            <option value="reactivate">Reactivate</option>
                            <option value="lock">Lock</option>
                            <option value="unlock">Unlock</option>
                        </select>
                        <input class="form-control form-control-sm mb-2" name="reason" type="text" placeholder="Reason (required for suspend/lock)" maxlength="2000">
                        <button class="btn btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Apply</button>
                    </form>
                    @error('action')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Role</h5>
                    <form method="POST" action="{{ route('admin.users.role', $user) }}"
                          data-confirm="Change this user's role? Session privileges are re-evaluated immediately.">
                        @csrf
                        <select class="form-select mb-2" name="role" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected($user->roles->contains('name', $role->name))>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Update role</button>
                    </form>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Activation &amp; password</h5>
                    @if ($user->status === 'invited')
                        <form method="POST" action="{{ route('admin.users.resend-activation', $user) }}" class="mb-3">
                            @csrf
                            <button class="btn btn-outline-secondary w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-mail-send-line me-1"></i> Regenerate activation link
                            </button>
                        </form>
                    @else
                        <p class="small text-secondary mb-3">This account is already active.</p>
                    @endif
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                          data-confirm="Reset this user's password? They will need the new password to sign in, and their existing sessions will be revoked.">
                        @csrf
                        <input class="form-control form-control-sm mb-2" name="new_password" type="password"
                               placeholder="New password (min 10 characters)" autocomplete="new-password" minlength="10" required>
                        <input class="form-control form-control-sm mb-2" name="new_password_confirmation" type="password"
                               placeholder="Confirm new password" autocomplete="new-password" minlength="10" required>
                        <button class="btn btn-outline-secondary w-100 rounded-3 fw-semibold" type="submit">
                            <i class="ri-key-2-line me-1"></i> Reset password
                        </button>
                    </form>
                    <p class="small text-secondary mt-2 mb-0">
                        The reset is recorded in the audit log and revokes all existing sessions for this user.
                    </p>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">New LGA assignment</h5>
                    <form method="POST" action="{{ route('admin.users.assignments.store', $user) }}">
                        @csrf
                        <select class="form-select form-select-sm mb-2" data-state-cascade data-lga-target="#assign_lga_id"
                                data-lga-url="{{ route('api.geography.lgas-by-state') }}">
                            <option value="">Select state&hellip;</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm mb-2" id="assign_lga_id" name="lga_id" required>
                            <option value="">Select state first, then LGA</option>
                        </select>
                        <select class="form-select form-select-sm mb-2" name="role_id" required>
                            <option value="">Select role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm mb-2" name="assignment_type" required>
                            <option value="primary">Primary</option>
                            <option value="acting">Acting</option>
                            <option value="temporary">Temporary</option>
                        </select>
                        <input class="form-control form-control-sm mb-2" name="appointment_title" type="text" placeholder="Appointment title" maxlength="120">
                        <input class="form-control form-control-sm mb-2" name="starts_at" type="date" title="Starts">
                        <input class="form-control form-control-sm mb-2" name="ends_at" type="date" title="Ends">
                        <button class="btn btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Create assignment</button>
                    </form>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Effective permissions</h5>
                    <div class="d-flex flex-wrap gap-1">
                        @forelse ($permissions as $permission)
                            <span class="badge bg-light text-secondary border">{{ $permission }}</span>
                        @empty
                            <span class="small text-secondary">No permissions.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
