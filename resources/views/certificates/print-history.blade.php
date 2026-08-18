@extends('layouts.app')

@section('title', 'Print History')
@section('page-title', 'Print History')
@section('page-subtitle', 'Server-authorised printable copy occurrences. Counts derive from immutable print events.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('certificates.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Certificates</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Print History</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('certificates.print-history') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="q" placeholder="Certificate number or holder name&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="copy_type">
                        <option value="">Original and reprints</option>
                        <option value="original" @selected(request('copy_type') === 'original')>Original only</option>
                        <option value="reprint" @selected(request('copy_type') === 'reprint')>Reprints only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i> Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Copy</th>
                            <th>Certificate</th>
                            <th>Holder</th>
                            <th>Reason</th>
                            <th>Requested by</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td><span class="status-badge {{ $event->copy_type === 'original' ? 'status-approved' : 'status-pending_chairman' }}">{{ $event->copyLabel() }}</span></td>
                                <td>
                                    <a href="{{ route('certificates.show', $event->certificate) }}" class="fw-semibold">{{ $event->certificate->certificate_number }}</a>
                                </td>
                                <td>{{ $event->certificate->indigene->fullName() }}</td>
                                <td>{{ str_replace('_', ' ', $event->reason_code ?? '—') }}</td>
                                <td>{{ optional($event->requester)->full_name }}</td>
                                <td>{{ $event->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('certificates.download', ['certificate' => $event->certificate, 'event' => $event]) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Download PDF copy">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">print</span>
                                    <p class="mb-1 fw-semibold">No print occurrences yet</p>
                                    <p class="small mb-0">Print copies are generated from the certificate page.</p>
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
