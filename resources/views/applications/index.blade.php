@extends('layouts.app')

@section('title', 'Applications')
@section('page-title', 'Applications')
@section('page-subtitle', 'Versioned requests for onboarding, correction, replacement or status action.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Applications</span></li>
@endsection

@section('content')
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('applications.index', ['tab' => 'all']) }}">All</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'awaiting-review' ? 'active' : '' }}" href="{{ route('applications.index', ['tab' => 'awaiting-review']) }}">Awaiting review</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'corrections' ? 'active' : '' }}" href="{{ route('applications.index', ['tab' => 'corrections']) }}">Corrections</a></li>
    </ul>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('applications.index') }}" class="row g-2 mb-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-4">
                    <input class="form-control" type="text" name="q" placeholder="Search number, name, NIN suffix&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">All types</option>
                        @foreach (['onboarding', 'amendment', 'replacement', 'reinstatement'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input class="form-control" type="date" name="from" value="{{ request('from') }}" title="From date">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <input class="form-control" type="date" name="to" value="{{ request('to') }}" title="To date">
                    <button class="btn btn-primary-div text-white" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Applicant</th>
                            <th>LGA</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Queue age</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $app)
                            <tr>
                                <td><a href="{{ route('applications.show', $app) }}" class="fw-semibold">{{ $app->application_number }}</a></td>
                                <td>
                                    {{ $app->profile?->displayName() ?: 'Unnamed applicant' }}
                                    <div class="small text-secondary">NIN {{ $app->indigene->maskedNin() }}</div>
                                </td>
                                <td>{{ $app->lga->name }}</td>
                                <td>{{ ucfirst($app->application_type) }}</td>
                                <td>@include('partials.status-badge', ['status' => $app->status->value])</td>
                                <td>{{ optional($app->submitted_at)->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @if ($app->queueAgeInDays() !== null && $app->status->isPendingDecision())
                                        <span class="status-badge {{ $app->queueAgeInDays() > 7 ? 'status-rejected' : 'status-pending_chairman' }}">{{ $app->queueAgeInDays() }} days</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                        @can('delete', $app)
                                            <form method="POST" action="{{ route('applications.delete', $app) }}"
                                                  data-confirm="Delete application {{ $app->application_number }} and its applicant record? This cannot be undone.">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">description</span>
                                    <p class="mb-1 fw-semibold">No applications found</p>
                                    <p class="small mb-3">Adjust the filters, or register a new indigene.</p>
                                    <a href="{{ route('applications.create') }}" class="btn btn-primary-div text-white rounded-3 fw-semibold">New application</a>
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
