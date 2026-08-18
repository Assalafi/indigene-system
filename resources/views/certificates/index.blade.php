@extends('layouts.app')

@section('title', 'Certificates')
@section('page-title', 'Certificates')
@section('page-subtitle', 'Issued, active, suspended, superseded and revoked certificates within your scope.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Certificates</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('certificates.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input class="form-control" type="text" name="q" placeholder="Certificate number, holder name, registry&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach (\App\Enums\CertificateStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input class="form-control" type="date" name="from" value="{{ request('from') }}" title="Issued from">
                </div>
                <div class="col-md-2">
                    <input class="form-control" type="date" name="to" value="{{ request('to') }}" title="Issued to">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Certificate number</th>
                            <th>Holder</th>
                            <th>LGA</th>
                            <th>Issue date</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Prints</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($certificates as $cert)
                            <tr>
                                <td><a href="{{ route('certificates.show', $cert) }}" class="fw-semibold">{{ $cert->certificate_number ?? 'Eligible' }}</a></td>
                                <td>{{ $cert->indigene->fullName() }}</td>
                                <td>{{ $cert->lga->name }}</td>
                                <td>{{ optional($cert->issued_at)->format('d/m/Y') ?? '—' }}</td>
                                <td>v{{ $cert->currentVersion?->version_no ?? 0 }}</td>
                                <td>@include('partials.status-badge', ['status' => $cert->status->value])</td>
                                <td><span class="fw-semibold">{{ $cert->total_prints_cached }}</span></td>
                                <td><a href="{{ route('certificates.show', $cert) }}" class="btn btn-sm btn-outline-secondary">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">verified</span>
                                    <p class="mb-1 fw-semibold">No certificates found</p>
                                    <p class="small mb-0">Certificates become eligible when an application is approved.</p>
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
