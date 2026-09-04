@extends('layouts.app')

@section('title', 'Approval Queue')
@section('page-title', 'Approval Queue')
@section('page-subtitle', 'Applications awaiting your decision, ordered by submission age. Bulk approval is prohibited.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Approval Queue</span></li>
@endsection

@section('content')
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('approvals.queue', ['tab' => 'all']) }}">All pending</a></li>
        @if (auth()->user()->isSystemAdmin())
            <li class="nav-item"><a class="nav-link {{ $tab === 'escalated' ? 'active' : '' }}" href="{{ route('approvals.queue', ['tab' => 'escalated']) }}">Escalated</a></li>
        @endif
        <li class="nav-item"><a class="nav-link {{ $tab === 'overdue' ? 'active' : '' }}" href="{{ route('approvals.queue', ['tab' => 'overdue']) }}">Overdue</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'flagged' ? 'active' : '' }}" href="{{ route('approvals.queue', ['tab' => 'flagged']) }}">Flagged duplicates</a></li>
    </ul>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Applicant</th>
                            <th>Ward / unit</th>
                            <th>Submitted by</th>
                            <th>Route</th>
                            <th>Waiting</th>
                            <th>Flags</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $app)
                            @php $profile = $app->indigene->currentProfile; $flags = $app->duplicateFlags()->where('status', 'open')->count(); @endphp
                            <tr>
                                <td><a href="{{ route('applications.show', $app) }}" class="fw-semibold">{{ $app->application_number }}</a></td>
                                <td>{{ $profile?->displayName() ?? $app->indigene->fullName() }}</td>
                                <td>{{ $profile?->ward?->name ?? '—' }} / {{ $profile?->unit?->name ?? '—' }}</td>
                                <td>
                                    {{ $app->creator->full_name }}
                                    <div class="small text-secondary">{{ $app->creator->primaryRoleName() }}</div>
                                </td>
                                <td><span class="small">{{ $app->approval_route === 'admin_only' ? 'Admin only' : 'Chairman or Admin' }}</span></td>
                                <td>
                                    @php $days = $app->queueAgeInDays(); $overdue = $app->due_at && $app->due_at->isPast(); @endphp
                                    @if ($days !== null)
                                        <span class="status-badge {{ $overdue ? 'status-rejected' : 'status-pending_chairman' }}">{{ $days }} days</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($flags > 0)
                                        <a href="{{ route('applications.show', $app) }}" class="risk-chip high text-decoration-none">
                                            <span class="material-symbols-outlined">content_copy</span> {{ $flags }}
                                        </a>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-primary-div text-white rounded-3">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">task_alt</span>
                                    <p class="mb-1 fw-semibold">Queue is clear</p>
                                    <p class="small mb-0">No applications are awaiting your decision.</p>
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
