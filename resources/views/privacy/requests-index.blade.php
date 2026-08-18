@extends('layouts.app')

@section('title', 'Privacy Requests')
@section('page-title', 'Privacy Requests')
@section('page-subtitle', 'Data-subject request cases with identity verification, due dates and decisions.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Privacy Requests</span></li>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('privacy.requests.create') }}" class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold">
            <i class="ri-add-line me-1"></i> New privacy request
        </a>
    </div>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('privacy.requests.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach (['open', 'in_progress', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="type">
                        <option value="">All request types</option>
                        @foreach (['access', 'rectification', 'objection', 'restriction', 'portability', 'erasure'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
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
                        <tr><th>Reference</th><th>Indigene</th><th>Type</th><th>Verification</th><th>Status</th><th>Due</th><th>Assignee</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td><a href="{{ route('privacy.requests.show', $request) }}" class="fw-semibold">{{ $request->reference_number }}</a></td>
                                <td>{{ $request->indigene?->fullName() ?? '—' }}</td>
                                <td>{{ $request->requestTypeLabel() }}</td>
                                <td>@include('partials.status-badge', ['status' => match($request->verification_status) {
                                    'verified' => 'approved', 'failed' => 'rejected', default => 'pending_chairman',
                                }])</td>
                                <td>@include('partials.status-badge', ['status' => $request->status === 'completed' ? 'approved' : ($request->status === 'in_progress' ? 'submitted' : 'pending_chairman')])</td>
                                <td>{{ optional($request->due_at)->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ optional($request->assignee)->full_name ?? 'Unassigned' }}</td>
                                <td><a href="{{ route('privacy.requests.show', $request) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">privacy_tip</span>
                                    <p class="mb-1 fw-semibold">No privacy requests</p>
                                    <p class="small mb-0">Data-subject cases appear here.</p>
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
