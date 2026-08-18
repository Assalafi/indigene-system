@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Accurate, privacy-aware operational reports. Exports are role-safe, masked and logged.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Reports</span></li>
@endsection

@section('content')
    <div class="row g-3">
        @foreach ($catalogue as $item)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('reports.show', ['code' => $item['code']]) }}" class="text-decoration-none">
                    <div class="stat-card h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-brand-navy">
                                <span class="material-symbols-outlined" style="color:#fff;">{{ $item['icon'] }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-brand-navy">{{ $item['name'] }}</div>
                                <div class="stat-label">Open report <i class="ri-arrow-right-line"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
