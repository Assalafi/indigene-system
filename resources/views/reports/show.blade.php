@extends('layouts.app')

@section('title', $name)
@section('page-title', $name)
@section('page-subtitle')
    Report {{ $code }} &middot; person-level data is masked where exported.
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Reports</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $name }}</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.show', ['code' => $code]) }}" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input class="form-control" type="date" name="from" value="{{ request('from') }}" title="From">
                </div>
                <div class="col-md-3">
                    <input class="form-control" type="date" name="to" value="{{ request('to') }}" title="To">
                </div>
                <div class="col-md-4">
                    <input class="form-control" type="text" name="q" placeholder="Search&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i> Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                @php
                                    $values = match ($code) {
                                        'registrations' => [$row->registry_number, optional($row->currentProfile)->displayName(), optional($row->originLga)->name, str_replace('_', ' ', $row->lifecycle_status), optional($row->created_at)->format('d/m/Y')],
                                        'approved_indigenes' => [$row->registry_number, optional($row->currentProfile)->displayName(), optional($row->currentProfile)->sex ?? '—', optional($row->originLga)->name, optional($row->approved_at)->format('d/m/Y')],
                                        'turnaround' => [$row->application_number, $row->indigene->fullName(), $row->lga->name, optional($row->submitted_at)->format('d/m/Y'), optional($row->decided_at)->format('d/m/Y'), str_replace('_', ' ', $row->status->value)],
                                        'decisions' => [$row->application->application_number, $row->application->indigene->fullName(), optional($row->reviewer)->full_name ?? '—', str_replace('_', ' ', $row->outcome), optional($row->reviewed_at)->format('d/m/Y')],
                                        'rejection_reasons' => [$row->application_number, $row->indigene->fullName(), $row->lga->name, str_replace('_', ' ', $row->decision_reason_code ?? '—'), str_replace('_', ' ', $row->status->value), optional($row->decided_at)->format('d/m/Y')],
                                        'certificates' => [$row->certificate_number ?? 'Eligible', $row->indigene->fullName(), $row->lga->name, $row->status->label(), optional($row->issued_at)->format('d/m/Y'), $row->total_prints_cached],
                                        'prints' => [$row->certificate->certificate_number, $row->certificate->indigene->fullName(), $row->copyLabel(), str_replace('_', ' ', $row->reason_code ?? '—'), optional($row->requester)->full_name ?? '—', $row->created_at->format('d/m/Y H:i')],
                                        'duplicates' => [$row->application->application_number, $row->matchTypeLabel(), $row->score ?? '—', str_replace('_', ' ', $row->status), $row->created_at->format('d/m/Y')],
                                        'geography_completeness' => [$row->name, $row->state->name, $row->wards_count, $row->units_count, $row->districts_count],
                                        'staff_activity' => [$row->full_name, $row->email, optional($row->roles->first())->name ?? '—', $row->status, optional($row->last_login_at)->format('d/m/Y H:i') ?? 'Never'],
                                        'privacy_access' => [optional($row->actor)->full_name ?? '—', $row->subject_type, $row->data_category, $row->action, \Illuminate\Support\Str::limit($row->purpose, 60), $row->created_at->format('d/m/Y H:i')],
                                        default => [],
                                    };
                                @endphp
                                @foreach ($values as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columns) }}">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">monitoring</span>
                                    <p class="mb-1 fw-semibold">No rows for these filters</p>
                                    <p class="small mb-0">Adjust the date range or clear the search.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('partials.pagination')
        </div>
    </div>

    @can('report.export')
        <div class="card bg-white border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Export this report</h5>
                <form method="POST" action="{{ route('exports.create') }}"
                      data-confirm="Request this export? The request, actor, filters and row count are logged. The download expires automatically.">
                    @csrf
                    <input type="hidden" name="report_code" value="{{ $code }}">
                    <input type="hidden" name="from" value="{{ request('from') }}">
                    <input type="hidden" name="to" value="{{ request('to') }}">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="format" class="form-label small">Format <span class="required-indicator">Required</span></label>
                            <select class="form-select form-select-sm" id="format" name="format" required>
                                <option value="csv">CSV (spreadsheet)</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="purpose" class="form-label small">Purpose <span class="required-indicator">Required</span></label>
                            <input class="form-control form-control-sm" id="purpose" name="purpose" type="text" maxlength="2000" required
                                   placeholder="e.g. Monthly stakeholder reporting">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary-div text-white w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-download-cloud-2-line me-1"></i> Request export
                            </button>
                        </div>
                    </div>
                    <p class="small text-secondary mt-2 mb-0">
                        Exports are masked by default (no full NIN, no kin/guardian/address/document data unless
                        separately approved), use private expiring downloads, and neutralise spreadsheet formulas.
                    </p>
                </form>
            </div>
        </div>
    @endcan
@endsection
