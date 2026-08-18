@extends('layouts.app')

@section('title', 'Geography Imports')
@section('page-title', 'Geography Imports')
@section('page-subtitle', 'Versioned CSV imports with validation, dry run and publish. Referenced records are retired or merged, never deleted.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Imports</span></li>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('geography.imports.create') }}" class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold">
            <i class="ri-upload-cloud-2-line me-1"></i> New import
        </a>
    </div>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Dataset</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Rows</th>
                            <th>Inserted</th>
                            <th>Errors</th>
                            <th>Imported by</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td class="fw-semibold">{{ $batch->source_name }}</td>
                                <td>{{ strtoupper($batch->dataset_type) }}</td>
                                <td>{{ $batch->dataset_version ?? '—' }}</td>
                                <td>@include('partials.status-badge', ['status' => $batch->status === 'published' ? 'approved' : ($batch->status === 'failed' ? 'rejected' : 'submitted')])</td>
                                <td>{{ number_format($batch->row_count) }}</td>
                                <td>{{ number_format($batch->inserted_count) }}</td>
                                <td><span class="{{ $batch->error_count > 0 ? 'text-danger fw-bold' : 'text-secondary' }}">{{ number_format($batch->error_count) }}</span></td>
                                <td>{{ optional($batch->importer)->full_name }}</td>
                                <td>{{ $batch->created_at->format('d/m/Y') }}</td>
                                <td><a href="{{ route('geography.imports.show', $batch) }}" class="btn btn-sm btn-outline-secondary">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="10">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">upload_file</span>
                                    <p class="mb-1 fw-semibold">No imports yet</p>
                                    <p class="small mb-0">Upload a CSV to validate and publish geography data.</p>
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
