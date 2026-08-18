@extends('layouts.app')

@section('title', 'Help Centre')
@section('page-title', 'Help Centre')
@section('page-subtitle', 'Role-specific short guides for daily work.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Help</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Getting started</h5>
            <div class="row g-3">
                @foreach ($guides as $guide)
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="stat-icon bg-brand-soft mb-3" style="width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 18px rgba(11,31,58,.10);">
                                <span class="material-symbols-outlined text-brand-green">{{ $guide['icon'] }}</span>
                            </div>
                            <h6 class="fw-semibold">{{ $guide['title'] }}</h6>
                            <p class="small text-secondary mb-0">{{ $guide['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Still stuck?</h5>
            <p class="text-secondary mb-0">
                Email <a href="mailto:support@haighatech.com">support@haighatech.com</a> or contact your System
                Administrator. <strong>Never share a full NIN in a support message</strong> - a registry or
                certificate number is enough to locate a record.
            </p>
        </div>
    </div>
@endsection
