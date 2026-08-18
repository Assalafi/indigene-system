@extends('layouts.app')

@section('title', 'Sensitive Data Access')
@section('page-title', 'Sensitive Data Access')
@section('page-subtitle', 'Every reveal, download and export of restricted data is recorded with its purpose.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Sensitive Access</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('audit.sensitive-access') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select class="form-select" name="action">
                        <option value="">All actions</option>
                        <option value="reveal" @selected(request('action') === 'reveal')>Reveal</option>
                        <option value="download" @selected(request('action') === 'download')>Download</option>
                        <option value="export" @selected(request('action') === 'export')>Export</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Time</th><th>Actor</th><th>Subject</th><th>Data category</th><th>Action</th><th>Purpose</th><th>Result</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="fw-semibold">{{ optional($log->actor)->full_name ?? '—' }}</td>
                                <td class="small">{{ $log->subject_type }} <code>{{ substr($log->subject_id, 0, 8) }}&hellip;</code></td>
                                <td><span class="badge bg-light text-secondary border">{{ $log->data_category }}</span></td>
                                <td>{{ ucfirst($log->action) }}</td>
                                <td class="text-truncate-2" style="max-width:260px;">{{ $log->purpose }}</td>
                                <td>@include('partials.status-badge', ['status' => $log->result === 'success' ? 'approved' : 'rejected'])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">privacy_tip</span>
                                    <p class="mb-1 fw-semibold">No sensitive access recorded</p>
                                    <p class="small mb-0">Reveals, downloads and exports appear here.</p>
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
