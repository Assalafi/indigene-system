@extends('layouts.app')

@section('title', 'National Dashboard')
@section('page-title', 'National Dashboard')
@section('page-subtitle', 'Platform-wide operations across all states and LGAs.')

@section('content')
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Total registered indigenes', 'value' => number_format($stats['total_indigenes']), 'icon' => 'groups', 'class' => 'bg-brand'],
                ['label' => 'Pending approvals', 'value' => number_format($stats['pending_approvals']), 'icon' => 'hourglass_top', 'class' => 'bg-warning'],
                ['label' => 'Approved this month', 'value' => number_format($stats['approved_this_month']), 'icon' => 'task_alt', 'class' => 'bg-info'],
                ['label' => 'Certificates issued this month', 'value' => number_format($stats['certificates_this_month']), 'icon' => 'verified', 'class' => 'bg-success'],
                ['label' => 'Print occurrences this month', 'value' => number_format($stats['prints_this_month']), 'icon' => 'print', 'class' => 'bg-secondary'],
                ['label' => 'Active LGAs / users', 'value' => number_format($stats['active_lgas']).' / '.number_format($stats['active_users']), 'icon' => 'map', 'class' => 'bg-brand-navy'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="col-xxl-4 col-md-6">
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
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-semibold">Application trend (14 days)</h5>
                        <a href="{{ route('applications.index') }}" class="small">View applications</a>
                    </div>
                    <div id="trend-chart" style="min-height: 260px;"></div>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-semibold">Applications waiting longest</h5>
                        <a href="{{ route('approvals.queue') }}" class="small">Open approval queue</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Application</th>
                                    <th>Applicant</th>
                                    <th>LGA</th>
                                    <th>Submitted</th>
                                    <th>Waiting</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($waitingLongest as $app)
                                    <tr>
                                        <td><a href="{{ route('applications.show', $app) }}" class="fw-semibold">{{ $app->application_number }}</a></td>
                                        <td>{{ $app->indigene->fullName() }}</td>
                                        <td>{{ $app->lga->name }}</td>
                                        <td>{{ optional($app->submitted_at)->format('d/m/Y H:i') }}</td>
                                        <td><span class="status-badge status-warning">{{ $app->queueAgeInDays() }} days</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary py-4">No pending applications.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Registrations by state</h5>
                    <ul class="list-unstyled mb-0">
                        @foreach ($byState as $row)
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>{{ $row->name }}</span>
                                <span class="fw-bold">{{ number_format($row->total) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">System health</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center py-2">
                            <span>Open duplicate flags</span>
                            <a href="{{ route('duplicates.index') }}" class="badge bg-danger text-decoration-none">{{ $openFlags }}</a>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2">
                            <span>Open fraud reports</span>
                            <a href="{{ route('admin.fraud-reports.index') }}" class="badge bg-warning text-dark text-decoration-none">{{ $openFraud }}</a>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2">
                            <span>Failed jobs</span>
                            <span class="badge {{ $failedJobs > 0 ? 'bg-danger' : 'bg-success' }}">{{ $failedJobs }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Recent privileged activity</h5>
                    <ul class="activity-timeline list-unstyled mb-0">
                        @forelse ($recentActivity as $log)
                            <li class="tl-item">
                                <div class="fw-semibold small">{{ str_replace(['application.', 'certificate.'], ['Application ', 'Certificate '], $log->action) }}</div>
                                <div class="small text-secondary">{{ optional($log->actor)->full_name }} &middot; {{ $log->occurred_at->diffForHumans() }}</div>
                            </li>
                        @empty
                            <li class="text-secondary">No recent privileged activity.</li>
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
            chart: { type: 'area', height: 260, fontFamily: 'Inter, Segoe UI, Roboto, sans-serif', toolbar: { show: false } },
            series: [{ name: 'Applications', data: @json($trend->pluck('total')->toArray()) }],
            xaxis: { categories: @json($trend->pluck('day')->toArray()), labels: { style: { fontSize: '11px' } } },
            colors: ['#087A4B'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: .02 } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#eef2f1' }
        };
        var chart = new ApexCharts(document.querySelector('#trend-chart'), options);
        chart.render();
    });
</script>
@endpush

