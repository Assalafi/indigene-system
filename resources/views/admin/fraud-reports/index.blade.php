@extends('layouts.app')

@section('title', 'Fraud Reports')
@section('page-title', 'Fraud Reports')
@section('page-subtitle', 'Public reports of suspected fraudulent certificates.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Fraud Reports</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.fraud-reports.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        <option value="open" @selected(request('status') === 'open')>Open</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Reference</th><th>Certificate</th><th>Holder</th><th>Report</th><th>Status</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td><a href="{{ route('admin.fraud-reports.show', $report) }}" class="fw-semibold">{{ $report->reference_number }}</a></td>
                                <td>{{ $report->certificate?->certificate_number ?? '—' }}</td>
                                <td>{{ $report->certificate?->indigene?->fullName() ?? '—' }}</td>
                                <td class="text-truncate-2" style="max-width:280px;">{{ $report->report_text }}</td>
                                <td>@include('partials.status-badge', ['status' => $report->status === 'open' ? 'pending_chairman' : 'approved'])</td>
                                <td>{{ $report->created_at->format('d/m/Y') }}</td>
                                <td><a href="{{ route('admin.fraud-reports.show', $report) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">alarm</span>
                                    <p class="mb-1 fw-semibold">No fraud reports</p>
                                    <p class="small mb-0">Public reports appear here for review.</p>
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
