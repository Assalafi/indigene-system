@extends('layouts.app')

@section('title', 'Global Search')
@section('page-title', 'Global Search')
@section('page-subtitle', 'Search registry number, certificate number, name, masked NIN suffix, phone or email within your scope.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Search</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('indigenes.search') }}" class="row g-2 mb-4">
                <div class="col-md-8">
                    <input class="form-control form-control-lg" type="text" name="q"
                           placeholder="e.g. REG-2026-000001, DAM-2026-000001, a surname, 1234 (NIN suffix), +2348012345678"
                           value="{{ request('q') }}" required autofocus>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary-div text-white w-100 py-2 rounded-3 fw-semibold" type="submit">
                        <i class="ri-search-line me-1"></i> Search
                    </button>
                </div>
            </form>

            @if (request()->filled('q'))
                <p class="small text-secondary mb-3">{{ $results->count() }} record(s) found (max 50 shown).</p>

                @forelse ($results as $record)
                    @php $profile = $record->currentProfile; $cert = $record->certificates->first(); @endphp
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div>
                            <div class="fw-semibold">
                                <a href="{{ route('indigenes.show', $record) }}">{{ $profile?->displayName() ?? '—' }}</a>
                            </div>
                            <div class="small text-secondary">
                                {{ $record->registry_number }} &middot; NIN {{ $record->maskedNin() }}
                                &middot; {{ $record->originLga->name }} LGA
                                @if ($profile?->phone) &middot; {{ $profile->phone }} @endif
                            </div>
                            @if ($cert?->certificate_number)
                                <div class="small">
                                    <a href="{{ route('certificates.show', $cert) }}">{{ $cert->certificate_number }}</a>
                                    @include('partials.status-badge', ['status' => $cert->status->value])
                                </div>
                            @endif
                        </div>
                        <div>
                            @include('partials.status-badge', ['status' => $record->lifecycle_status])
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <span class="material-symbols-outlined">search_off</span>
                        <p class="mb-1 fw-semibold">No matches</p>
                        <p class="small mb-0">Try a registry or certificate number, a name, or a NIN suffix.</p>
                    </div>
                @endforelse
            @endif
        </div>
    </div>
@endsection
