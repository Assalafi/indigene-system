@extends('layouts.app')

@section('title', 'New Geography Import')
@section('page-title', 'New Geography Import')
@section('page-subtitle', 'Stage 1 of 3: upload. A dry run validates every row before any master row changes.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('geography.imports.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Imports</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">New import</span></li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    @include('partials.flash-messages')

                    <form method="POST" action="{{ route('geography.imports.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="dataset_type" class="form-label">Dataset type <span class="required-indicator">Required</span></label>
                            <select class="form-select" id="dataset_type" name="dataset_type" required>
                                <option value="states">States - columns: code, name, capital, type</option>
                                <option value="lgas">LGAs - columns: state_code, code, name, headquarters</option>
                                <option value="wards">Wards - columns: lga_code, code, name</option>
                                <option value="units">Units - columns: lga_code, ward_code, code, name, category</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="source_name" class="form-label">Source name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="source_name" name="source_name" type="text" maxlength="255"
                                       placeholder="e.g. INEC Registration Areas 2023" required>
                            </div>
                            <div class="col-md-3">
                                <label for="source_reference" class="form-label">Source reference</label>
                                <input class="form-control" id="source_reference" name="source_reference" type="text" maxlength="255">
                            </div>
                            <div class="col-md-3">
                                <label for="dataset_version" class="form-label">Dataset version</label>
                                <input class="form-control" id="dataset_version" name="dataset_version" type="text" maxlength="50">
                            </div>
                            <div class="col-12">
                                <label for="file" class="form-label">CSV file <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="file" name="file" type="file" accept=".csv,.txt" required>
                                <div class="form-text">First row must be a header row. Max 20 MB.</div>
                            </div>
                        </div>

                        <div class="alert alert-light border d-flex align-items-start gap-2 mt-4">
                            <span class="material-symbols-outlined">rule</span>
                            <div class="small">
                                <strong>Import rules:</strong> dry run before any change &middot; orphan records and duplicate
                                codes within a parent are rejected &middot; names are normalised for matching while official
                                spelling is preserved &middot; changed or removed official records are end-dated or merged,
                                never deleted &middot; locally curated village/community units are never silently overwritten.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('geography.imports.index') }}" class="btn btn-outline-secondary rounded-3 fw-semibold">Cancel</a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                <i class="ri-upload-cloud-2-line me-1"></i> Upload and validate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
