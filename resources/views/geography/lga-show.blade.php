@extends('layouts.app')

@section('title', $lga->name.' LGA')
@section('page-title', $lga->name.' Local Government Area')
@section('page-subtitle', $lga->state->name.' State &middot; Code '.$lga->code)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('geography.states') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Geography</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $lga->name }}</span></li>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Wards', 'value' => number_format($lga->wards_count), 'icon' => 'domain', 'class' => 'bg-brand'],
                ['label' => 'Units', 'value' => number_format($lga->units_count), 'icon' => 'location_city', 'class' => 'bg-brand-navy'],
                ['label' => 'Districts', 'value' => number_format($lga->districts_count), 'icon' => 'lan', 'class' => 'bg-secondary'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon {{ $card['class'] }}">
                            <span class="material-symbols-outlined" style="color:#fff;">{{ $card['icon'] }}</span>
                        </div>
                        <div>
                            <div class="stat-value">{{ $card['value'] }}</div>
                            <div class="stat-label">{{ $card['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Wards</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead><tr><th>Code</th><th>Name</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($lga->wards as $ward)
                                    <tr>
                                        <td><code>{{ $ward->code }}</code></td>
                                        <td>{{ $ward->name }}</td>
                                        <td>@include('partials.status-badge', ['status' => $ward->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-3">No wards signed off yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Districts</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead><tr><th>Code</th><th>Name</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($lga->districts as $district)
                                    <tr>
                                        <td><code>{{ $district->code }}</code></td>
                                        <td>{{ $district->name }}</td>
                                        <td>@include('partials.status-badge', ['status' => $district->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-3">No districts configured.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
