@extends('layouts.app')

@section('title', 'States & LGAs')
@section('page-title', 'States & LGAs')
@section('page-subtitle')
    National administrative geography master data.
    {{ number_format(\App\Models\State::count()) }} states/FCT and
    {{ number_format(\App\Models\Lga::count()) }} LGAs seeded.
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">States &amp; LGAs</span></li>
@endsection

@php
    $tab = $tab ?? 'states';
    $tabQuery = fn (string $t) => array_merge(request()->except(['tab', 'page', 'page_s', 'page_l']), ['tab' => $t]);
    $activeStates = \App\Models\State::where('status', 'active')->orderBy('name')->get();
@endphp

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0 fw-semibold">National geography</h5>
                <span class="small text-secondary">
                    {{ number_format(\App\Models\State::count()) }} states/FCT &middot;
                    {{ number_format(\App\Models\Lga::count()) }} LGAs
                </span>
            </div>

            <form method="GET" action="{{ route('geography.states') }}" class="row g-2 mb-3 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-4">
                    <input class="form-control" type="text" name="q" placeholder="Search name or code&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="state_id">
                        <option value="">All states (LGAs tab)</option>
                        @foreach ($activeStates as $stateOption)
                            <option value="{{ $stateOption->id }}" @selected(request('state_id') === $stateOption->id)>{{ $stateOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary-div text-white w-100" type="submit" title="Apply filters">
                        <i class="ri-filter-line"></i>
                    </button>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('geography.imports.create') }}" class="btn btn-outline-primary-div rounded-3 fw-semibold w-100">
                        <i class="ri-upload-cloud-2-line me-1"></i> Import data
                    </a>
                </div>
            </form>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'states' ? 'active' : '' }}" href="{{ route('geography.states', $tabQuery('states')) }}">
                        States / FCT <span class="badge bg-light text-secondary ms-1">{{ number_format($states->total()) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'lgas' ? 'active' : '' }}" href="{{ route('geography.states', $tabQuery('lgas')) }}">
                        LGAs <span class="badge bg-light text-secondary ms-1">{{ number_format($lgas->total()) }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- States --}}
                <div class="tab-pane fade {{ $tab === 'states' ? 'show active' : '' }}" id="tab-states">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr><th>Code</th><th>Name</th><th>Type</th><th>Capital</th><th>LGAs</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($states as $state)
                                    <tr>
                                        <td><code>{{ $state->code }}</code></td>
                                        <td class="fw-semibold">{{ $state->name }}</td>
                                        <td>{{ strtoupper($state->type) }}</td>
                                        <td>{{ $state->capital ?? '—' }}</td>
                                        <td>{{ number_format($state->lgas_count) }}</td>
                                        <td>@include('partials.status-badge', ['status' => $state->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-secondary py-4">No states found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $states])
                </div>

                {{-- LGAs --}}
                <div class="tab-pane fade {{ $tab === 'lgas' ? 'show active' : '' }}" id="tab-lgas">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr><th>Code</th><th>Name</th><th>State</th><th>Type</th><th>Headquarters</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($lgas as $lgaRow)
                                    <tr>
                                        <td><code>{{ $lgaRow->code }}</code></td>
                                        <td class="fw-semibold">{{ $lgaRow->name }}</td>
                                        <td>{{ $lgaRow->state->name }}</td>
                                        <td>{{ str_replace('_', ' ', ucfirst($lgaRow->type)) }}</td>
                                        <td>{{ $lgaRow->headquarters ?? '—' }}</td>
                                        <td>@include('partials.status-badge', ['status' => $lgaRow->status])</td>
                                        <td>
                                            <a href="{{ route('geography.lga-show', $lgaRow) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="ri-arrow-right-line"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-secondary py-4">No LGAs found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $lgas])
                </div>
            </div>
        </div>
    </div>
@endsection
