@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-subtitle', 'Append-only audit events. No edit or delete actions exist in the application.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Audit Log</span></li>
@endsection

@section('content')
    @php
        $auditActions = \App\Models\AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
    @endphp

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('audit.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input class="form-control" type="text" name="q" placeholder="Search action or object&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="action">
                        <option value="">All actions</option>
                        @foreach ($auditActions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="risk">
                        <option value="">All risk levels</option>
                        @foreach (['low', 'medium', 'high'] as $risk)
                            <option value="{{ $risk }}" @selected(request('risk') === $risk)>{{ ucfirst($risk) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Time</th><th>Actor</th><th>Action</th><th>Object</th><th>LGA</th><th>Risk</th><th>Result</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->occurred_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ optional($log->actor)->full_name ?? 'System' }}</span>
                                    <div class="small text-secondary">{{ $log->actor_role ?? '' }}</div>
                                </td>
                                <td>{{ $log->action }}</td>
                                <td class="small">
                                    {{ $log->auditable_type ?? '—' }}
                                    @if ($log->auditable_id)
                                        <code>{{ substr($log->auditable_id, 0, 8) }}&hellip;</code>
                                    @endif
                                </td>
                                <td>{{ optional($log->actorLga)->name ?? '—' }}</td>
                                <td><span class="risk-chip {{ $log->risk_level === 'high' ? 'high' : ($log->risk_level === 'medium' ? 'medium' : 'low') }}">{{ $log->risk_level }}</span></td>
                                <td>@include('partials.status-badge', ['status' => $log->result === 'success' ? 'approved' : 'rejected'])</td>
                                <td><a href="{{ route('audit.show', $log) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">shield_person</span>
                                    <p class="mb-1 fw-semibold">No audit events</p>
                                    <p class="small mb-0">Events appear as staff use the system.</p>
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
