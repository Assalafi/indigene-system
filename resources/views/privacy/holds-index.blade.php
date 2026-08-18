@extends('layouts.app')

@section('title', 'Legal Holds')
@section('page-title', 'Legal Holds')
@section('page-subtitle', 'A hold overrides normal deletion and disposal jobs.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Legal Holds</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Apply a legal hold</h5>
            <form method="POST" action="{{ route('privacy.holds.store') }}" class="row g-3 mb-4"
                  data-confirm="Apply this legal hold? Disposal jobs for this subject are suspended.">
                @csrf
                <div class="col-md-4">
                    <input class="form-control" name="subject_type" type="text" placeholder="Subject type, e.g. App\Models\Indigene" maxlength="60" required>
                </div>
                <div class="col-md-4">
                    <input class="form-control" name="subject_id" type="text" placeholder="Subject UUID" maxlength="40" required>
                </div>
                <div class="col-md-4">
                    <input class="form-control" name="authority_reference" type="text" placeholder="Authority reference" maxlength="180">
                </div>
                <div class="col-md-9">
                    <textarea class="form-control" name="reason" rows="2" placeholder="Reason for the hold" maxlength="4000" required></textarea>
                </div>
                <div class="col-md-3">
                    <input class="form-control" name="ends_at" type="date" title="Hold ends">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                        <i class="ri-lock-line me-1"></i> Apply hold
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Active holds</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Subject</th><th>Reason</th><th>Authority</th><th>Starts</th><th>Ends</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($holds as $hold)
                            <tr>
                                <td class="small">{{ $hold->subject_type }} <code>{{ substr($hold->subject_id, 0, 8) }}&hellip;</code></td>
                                <td class="text-truncate-2" style="max-width:260px;">{{ $hold->reason }}</td>
                                <td>{{ $hold->authority_reference ?? '—' }}</td>
                                <td>{{ optional($hold->starts_at)->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ optional($hold->ends_at)->format('d/m/Y') ?? 'Open' }}</td>
                                <td>@include('partials.status-badge', ['status' => $hold->status === 'active' ? 'pending_chairman' : 'approved'])</td>
                                <td>
                                    @if ($hold->status === 'active')
                                        <form method="POST" action="{{ route('privacy.holds.release', $hold) }}"
                                              data-confirm="Release this legal hold? Normal disposal resumes.">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Release</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">lock</span>
                                    <p class="mb-1 fw-semibold">No legal holds</p>
                                    <p class="small mb-0">Holds are applied to subjects that must not be disposed.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('partials.pagination')
        </div>
    </div>
@endsection
