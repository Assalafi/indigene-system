@extends('layouts.app')

@section('title', 'Chairman Dashboard')
@section('page-title', 'Chairman Dashboard')
@section('page-subtitle', $lga->name.' Local Government Area, '.$lga->state->name.' State')

@section('content')
    @if ($incompleteGeography)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <span class="material-symbols-outlined">warning</span>
            <div>
                <strong>Geography is incomplete for {{ $lga->name }}.</strong>
                Wards and village/community units must be signed off before onboarding can complete.
                <a href="{{ route('geography.wards') }}" class="alert-link">Review geography</a>.
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Awaiting your review', 'value' => number_format($stats['awaiting_review']), 'icon' => 'hourglass_top', 'class' => 'bg-warning', 'href' => route('approvals.queue')],
                ['label' => 'Oldest pending (days)', 'value' => $stats['oldest_pending_days'], 'icon' => 'schedule', 'class' => 'bg-brand', 'href' => route('approvals.queue')],
                ['label' => 'Approved this month', 'value' => number_format($stats['approved_this_month']), 'icon' => 'task_alt', 'class' => 'bg-info', 'href' => route('applications.index')],
                ['label' => 'Rejected this month', 'value' => number_format($stats['rejected_this_month']), 'icon' => 'cancel', 'class' => 'bg-danger', 'href' => route('applications.index')],
                ['label' => 'Certificates this month', 'value' => number_format($stats['certificates_this_month']), 'icon' => 'verified', 'class' => 'bg-success', 'href' => route('certificates.index')],
                ['label' => 'Reprints this month', 'value' => number_format($stats['reprints_this_month']), 'icon' => 'print', 'class' => 'bg-secondary', 'href' => route('certificates.print-history')],
                ['label' => 'Registered indigenes', 'value' => number_format($stats['indigenes_total']), 'icon' => 'groups', 'class' => 'bg-brand-navy', 'href' => route('indigenes.index')],
                ['label' => 'Wards / units', 'value' => number_format($stats['wards_total']).' / '.number_format($stats['units_total']), 'icon' => 'map', 'class' => 'bg-brand', 'href' => route('geography.wards')],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <a href="{{ $card['href'] }}" class="text-decoration-none">
                    <div class="stat-card h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon {{ $card['class'] }}">
                                <span class="material-symbols-outlined" style="color:#fff;">{{ $card['icon'] }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="stat-value">{{ $card['value'] }}</div>
                                <div class="stat-label">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="mb-0 fw-semibold">Applications awaiting your review</h5>
                <a href="{{ route('approvals.queue') }}" class="btn btn-primary-div text-white py-2 px-4 rounded-3 fw-semibold">
                    <i class="ri-how-to-vote-line me-1"></i> Review pending applications
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Applicant</th>
                            <th>Ward / unit</th>
                            <th>Submitted by</th>
                            <th>Waiting</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($awaitingReview->take(10) as $app)
                            @php $profile = $app->indigene->currentProfile; @endphp
                            <tr>
                                <td><a href="{{ route('applications.show', $app) }}" class="fw-semibold">{{ $app->application_number }}</a></td>
                                <td>{{ $profile?->displayName() ?? '—' }}</td>
                                <td>{{ $profile?->ward?->name }} / {{ $profile?->unit?->name }}</td>
                                <td>{{ $app->creator->full_name }}</td>
                                <td>
                                    @if ($app->queueAgeInDays() !== null)
                                        @php $days = $app->queueAgeInDays(); $overdue = $app->due_at && $app->due_at->isPast(); @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="status-badge {{ $overdue ? 'status-rejected' : 'status-pending_chairman' }}">{{ $days }} days</span>
                                            <div class="sla-meter flex-grow-1" style="min-width:60px;">
                                                <div class="sla-fill {{ $overdue ? 'overdue' : '' }}" style="width: {{ min(100, $days / 7 * 100) }}%;"></div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td><a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-outline-secondary">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">Nothing awaiting your review. Great work.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Registrations by ward</h5>
                    <div id="ward-chart" style="min-height: 240px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Recent actions in {{ $lga->name }}</h5>
                    <ul class="activity-timeline list-unstyled mb-0">
                        @forelse ($recentActions as $log)
                            <li class="tl-item">
                                <div class="fw-semibold small">{{ str_replace(['application.', 'certificate.', 'geography.'], ['', '', ''], $log->action) }}</div>
                                <div class="small text-secondary">{{ optional($log->actor)->full_name }} &middot; {{ $log->occurred_at->diffForHumans() }}</div>
                            </li>
                        @empty
                            <li class="text-secondary">No recent activity.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: { type: 'bar', height: 240, fontFamily: 'Inter, Segoe UI, Roboto, sans-serif', toolbar: { show: false } },
            series: [{ name: 'Indigenes', data: @json($byWard->pluck('total')->toArray()) }],
            xaxis: { categories: @json($byWard->pluck('name')->toArray()), labels: { style: { fontSize: '10px' } } },
            colors: ['#0B1F3A'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#eef2f1' }
        };
        var chart = new ApexCharts(document.querySelector('#ward-chart'), options);
        chart.render();
    });
</script>
@endpush
