@extends('layouts.app')

@section('title', 'LGA Profiles & Signatories')
@section('page-title', 'LGA Profiles & Signatories')
@section('page-subtitle', 'Branding, certificate wording and signatory authority per LGA. Publishing a new version never alters historical certificate versions.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">LGA Profiles</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.lga-profiles.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input class="form-control" type="text" name="q" placeholder="Search LGA name&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>LGA</th><th>State</th><th>Profile version</th><th>Signatory</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($lgas as $lgaRow)
                            <tr>
                                <td class="fw-semibold">{{ $lgaRow->name }}</td>
                                <td>{{ $lgaRow->state->name }}</td>
                                <td>
                                    @if ($lgaRow->profile)
                                        <span class="status-badge status-approved"><span class="material-symbols-outlined">verified</span>v{{ $lgaRow->profile->version_no }}</span>
                                    @else
                                        <span class="status-badge status-rejected"><span class="material-symbols-outlined">pending</span>None</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($lgaRow->activeSignatory)
                                        {{ $lgaRow->activeSignatory->full_name }}
                                    @else
                                        <span class="text-danger small fw-semibold">No active signatory</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.lga-profiles.show', $lgaRow) }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">No LGAs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('partials.pagination')
        </div>
    </div>
@endsection
