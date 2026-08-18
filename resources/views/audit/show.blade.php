@extends('layouts.app')

@section('title', 'Audit Event')
@section('page-title', 'Audit Event')
@section('page-subtitle', $log->action.' &middot; '.$log->occurred_at->format('d/m/Y H:i'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('audit.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Audit Log</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $log->id }}</span></li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Details</h5>
                    <dl class="review-grid">
                        <div class="review-item"><dt>Actor</dt><dd>{{ optional($log->actor)->full_name ?? 'System' }} ({{ $log->actor_type }})</dd></div>
                        <div class="review-item"><dt>Actor role</dt><dd>{{ $log->actor_role ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Actor LGA</dt><dd>{{ optional($log->actorLga)->name ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Action</dt><dd>{{ $log->action }}</dd></div>
                        <div class="review-item"><dt>Object</dt><dd>{{ $log->auditable_type ?? '—' }} <code>{{ $log->auditable_id ?? '' }}</code></dd></div>
                        <div class="review-item"><dt>Route</dt><dd>{{ $log->route_name ?? '—' }} ({{ $log->http_method ?? '' }})</dd></div>
                        <div class="review-item"><dt>Result</dt><dd>{{ $log->result }}</dd></div>
                        <div class="review-item"><dt>Risk level</dt><dd>{{ $log->risk_level }}</dd></div>
                        <div class="review-item"><dt>IP hash</dt><dd><code class="small">{{ $log->ip_hash ? substr($log->ip_hash, 0, 16).'&hellip;' : '—' }}</code></dd></div>
                        <div class="review-item"><dt>Request ID</dt><dd><code class="small">{{ $log->request_id ?? '—' }}</code></dd></div>
                        <div class="review-item"><dt>Occurred at</dt><dd>{{ $log->occurred_at->format('d/m/Y H:i:s') }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Change payload</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="small fw-semibold text-secondary">Before</h6>
                            @if (!empty($log->before_values))
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($log->before_values as $key => $value)
                                        <li class="border-bottom py-1"><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="small text-secondary">No before snapshot.</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="small fw-semibold text-secondary">After</h6>
                            @if (!empty($log->after_values))
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($log->after_values as $key => $value)
                                        <li class="border-bottom py-1"><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="small text-secondary">No after snapshot.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
