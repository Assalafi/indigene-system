@extends('layouts.app')

@section('title', 'Duplicate Review')
@section('page-title', 'Duplicate Review')
@section('page-subtitle', 'Possible matches shown with minimal comparison fields only. Fuzzy matching never makes the final indigene decision.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Duplicate Review</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('duplicates.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All states</option>
                        @foreach (['open', 'same_person', 'false_positive', 'escalated'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white" type="submit"><i class="ri-filter-line"></i> Filter</button>
                </div>
            </form>

            @forelse ($flags as $flag)
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="risk-chip {{ $flag->score >= 80 ? 'high' : ($flag->score >= 30 ? 'medium' : 'low') }}">
                                    {{ $flag->matchTypeLabel() }} @if ($flag->score) &middot; {{ $flag->score }}% @endif
                                </span>
                                <span class="status-badge status-{{ $flag->status === 'open' ? 'pending_chairman' : ($flag->status === 'false_positive' ? 'approved' : 'draft') }}">
                                    {{ str_replace('_', ' ', $flag->status) }}
                                </span>
                            </div>
                            <div class="small text-secondary">
                                Application <a href="{{ route('applications.show', $flag->application) }}" class="fw-semibold">{{ $flag->application->application_number }}</a>
                                &middot; {{ $flag->application->indigene->fullName() }}
                                &middot; NIN {{ $flag->application->indigene->maskedNin() }}
                                &middot; {{ $flag->application->lga->name }} LGA
                            </div>
                            @if ($flag->candidate)
                                <div class="small text-secondary mt-1">
                                    Candidate: <a href="{{ route('indigenes.show', $flag->candidate) }}" class="fw-semibold">{{ $flag->candidate->fullName() }}</a>
                                    ({{ $flag->candidate->registry_number }})
                                    @if ($flag->candidate->currentProfile)
                                        &middot; DOB {{ $flag->candidate->currentProfile->date_of_birth?->format('d/m/Y') }}
                                        &middot; {{ $flag->candidate->currentProfile->originLga?->name }} LGA
                                    @endif
                                </div>
                            @endif
                            <div class="small mt-1">{{ data_get($flag->evidence, 'message') }}</div>
                        </div>
                    </div>

                    @if ($flag->status === 'open')
                        <form method="POST" action="{{ route('duplicates.resolve', $flag) }}" class="row g-2 mt-3"
                              data-confirm="Confirm this duplicate resolution. It is recorded in the audit log.">
                            @csrf
                            <div class="col-md-4">
                                <select class="form-select" name="resolution" required>
                                    <option value="">Resolution&hellip;</option>
                                    <option value="same_person">Same person (link)</option>
                                    <option value="false_positive">False positive (dismiss)</option>
                                    <option value="escalate">Escalate</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input class="form-control" name="review_reason" type="text" placeholder="Reason (required)" maxlength="2000" required>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Resolve</button>
                            </div>
                        </form>
                    @elseif ($flag->review_reason)
                        <div class="small text-secondary mt-2">
                            Reviewed by {{ optional($flag->reviewer)->full_name }}: {{ $flag->review_reason }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <span class="material-symbols-outlined">content_copy</span>
                    <p class="mb-1 fw-semibold">No duplicate flags</p>
                    <p class="small mb-0">Potential duplicates appear here when detected at submission.</p>
                </div>
            @endforelse

            @include('partials.pagination')
        </div>
    </div>
@endsection
