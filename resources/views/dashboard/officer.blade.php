@extends('layouts.app')

@section('title', 'Officer Dashboard')
@section('page-title', 'Indigene Officer Dashboard')
@section('page-subtitle', 'Assigned LGA: '.$lga->name.', '.$lga->state->name.' State')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('applications.create') }}" class="btn btn-primary-div text-white py-2 px-4 rounded-3 fw-semibold">
            <i class="ri-user-add-line me-1"></i> Register new indigene
        </a>
        <a href="{{ route('applications.index', ['tab' => 'corrections']) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
            <i class="ri-edit-note-line me-1"></i> Corrections requested
        </a>
    </div>

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Submitted / pending', 'value' => number_format($stats['submitted_pending']), 'icon' => 'hourglass_top', 'class' => 'bg-warning', 'href' => route('applications.index', ['tab' => 'all'])],
                ['label' => 'Correction required', 'value' => number_format($stats['correction_required']), 'icon' => 'edit_note', 'class' => 'bg-brand', 'href' => route('applications.index', ['tab' => 'corrections'])],
                ['label' => 'Approved & ready to print', 'value' => number_format($stats['approved_ready_to_print']), 'icon' => 'task_alt', 'class' => 'bg-success', 'href' => route('certificates.index')],
                ['label' => 'Registered in '.$lga->name, 'value' => number_format($stats['registered_total']), 'icon' => 'groups', 'class' => 'bg-brand-navy', 'href' => route('indigenes.index')],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="col-xl-4 col-md-6">
                <a href="{{ $card['href'] }}" class="text-decoration-none">
                    <div class="stat-card h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon {{ $card['class'] }}">
                                <span class="material-symbols-outlined" style="color:#fff;">{{ $card['icon'] }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="stat-value">{{ $card['value'] }}</div>
                                <div class="stat-label">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-semibold">Recent applications</h5>
                        <a href="{{ route('applications.index') }}" class="small">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Application</th>
                                    <th>Applicant</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recent as $app)
                                    <tr>
                                        <td><a href="{{ route('applications.show', $app) }}" class="fw-semibold">{{ $app->application_number }}</a></td>
                                        <td>{{ $app->indigene->fullName() }}</td>
                                        <td>{{ ucfirst($app->application_type) }}</td>
                                        <td>@include('partials.status-badge', ['status' => $app->status->value])</td>
                                        <td>{{ $app->updated_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary py-4">
                                        No applications yet. <a href="{{ route('applications.create') }}">Register your first indigene</a>.
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">LGA service level</h5>
                    <div class="text-center py-3">
                        <div class="stat-value">{{ $avgApprovalDays ?? '—' }}</div>
                        <div class="stat-label">Average approval time (days)</div>
                    </div>
                    <p class="small text-secondary mb-0">
                        Approvals are performed by the Chairman or a System Admin. Your submitted
                        applications are routed automatically; Chairman-created applications route
                        to the System Admin.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

