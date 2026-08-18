@extends('layouts.app')

@section('title', 'Import Review')
@section('page-title', 'Import Review: '.$batch->source_name)
@section('page-subtitle', 'Dataset '.strtoupper($batch->dataset_type).' @if ($batch->dataset_version) v{{ $batch->dataset_version }} @endif &middot; SHA-256 '.substr($batch->checksum_sha256 ?? '—', 0, 16).'&hellip;')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('geography.imports.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Imports</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $batch->source_name }}</span></li>
@endsection

@section('content')
    @if ($batch->status === 'published')
        <div class="alert alert-success d-flex align-items-start gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <div>
                <strong>Batch published.</strong>
                Published by {{ optional($batch->publisher)->full_name }}
                at {{ optional($batch->published_at)->format('d/m/Y H:i') }}.
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Data rows', 'value' => number_format($batch->row_count), 'icon' => 'table_rows', 'class' => 'bg-brand'],
                ['label' => 'Validation errors', 'value' => number_format($batch->error_count), 'icon' => 'error', 'class' => $batch->error_count > 0 ? 'bg-danger' : 'bg-success'],
                ['label' => 'Inserted', 'value' => number_format($batch->inserted_count), 'icon' => 'add_circle', 'class' => 'bg-info'],
                ['label' => 'Skipped', 'value' => number_format($batch->skipped_count), 'icon' => 'remove_circle', 'class' => 'bg-secondary'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="col-md-3">
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

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Validation report</h5>

            @php $report = $batch->validation_report ?? []; @endphp

            @if (!empty($report['errors']))
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($report['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    <div>No validation errors.</div>
                </div>
            @endif

            @if (!empty($report['rows']))
                <h6 class="fw-semibold mb-2">Validated rows (preview, first 20)</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                @foreach (array_keys($report['rows'][0]) as $header)
                                    <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($report['rows'], 0, 20) as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($batch->status === 'validated' && $batch->error_count === 0)
        <div class="card bg-white border-0 rounded-3 mb-4">
            <div class="card-body p-4 text-center">
                <form method="POST" action="{{ route('geography.imports.publish', $batch) }}"
                      data-confirm="Publish this batch? Rows are applied to the master data in one transaction.">
                    @csrf
                    <button class="btn btn-brand-green px-5 py-2 rounded-3 fw-semibold" type="submit">
                        <i class="ri-rocket-line me-1"></i> Publish batch
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection
