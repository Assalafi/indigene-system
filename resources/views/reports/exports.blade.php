@extends('layouts.app')

@section('title', 'My Exports')
@section('page-title', 'My Exports')
@section('page-subtitle', 'Every export records its purpose and expires automatically.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Reports</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Exports</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Report</th><th>Format</th><th>Status</th><th>Rows</th><th>Expires</th><th>Created</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($exports as $export)
                            <tr>
                                <td class="fw-semibold">{{ $export->report_code }}</td>
                                <td>{{ strtoupper($export->format) }}</td>
                                <td>
                                    @include('partials.status-badge', ['status' => match($export->status) {
                                        'queued' => 'draft', 'completed' => 'approved', 'failed' => 'rejected', default => 'draft',
                                    }])
                                </td>
                                <td>{{ number_format($export->row_count) }}</td>
                                <td>{{ optional($export->expires_at)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ $export->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($export->status === 'completed' && $export->file)
                                        <a href="{{ route('exports.download', $export) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="ri-download-2-line me-1"></i> Download
                                        </a>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">download</span>
                                    <p class="mb-1 fw-semibold">No exports yet</p>
                                    <p class="small mb-0">Request an export from any report page.</p>
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
