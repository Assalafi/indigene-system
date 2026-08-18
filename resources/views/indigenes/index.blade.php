@extends('layouts.app')

@section('title', 'Indigenes')
@section('page-title', 'Indigene Registry')
@section('page-subtitle', 'Approved indigenes within your authorised scope.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Indigenes</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('indigenes.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input class="form-control" type="text" name="q" placeholder="Search registry number, name, NIN suffix, phone&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All lifecycle states</option>
                        @foreach (\App\Enums\LifecycleStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @if (auth()->user()->isSystemAdmin() || auth()->user()->activeLga())
                    <div class="col-md-3">
                        <select class="form-select" name="ward_id">
                            <option value="">All wards</option>
                            @foreach (\App\Models\Ward::where('lga_id', auth()->user()->activeLga()?->id ?? '00000000-0000-0000-0000-000000000000')->where('status', 'active')->get() as $ward)
                                <option value="{{ $ward->id }}" @selected(request('ward_id') === $ward->id)>{{ $ward->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-search-line me-1"></i> Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Registry number</th>
                            <th>Full name</th>
                            <th>Origin LGA</th>
                            <th>Ward / unit</th>
                            <th>Status</th>
                            <th>Approved</th>
                            <th>Certificate</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($indigenes as $record)
                            @php $profile = $record->currentProfile; $cert = $record->certificates->whereIn('status', ['active','suspended'])->first() ?? $record->certificates->first(); @endphp
                            <tr>
                                <td>
                                    @if ($profile?->photoFile)
                                        <img src="{{ route('documents.photo', ['file' => $profile->photoFile]) }}" alt=""
                                             class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" onerror="this.style.display='none'">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                            <span class="material-symbols-outlined text-secondary" style="font-size:20px;">person</span>
                                        </div>
                                    @endif
                                </td>
                                <td><a href="{{ route('indigenes.show', $record) }}" class="fw-semibold">{{ $record->registry_number }}</a></td>
                                <td>{{ $profile?->displayName() ?? '—' }}</td>
                                <td>{{ $record->originLga->name }}</td>
                                <td>{{ $profile?->ward?->name ?? '—' }} / {{ $profile?->unit?->name ?? '—' }}</td>
                                <td>@include('partials.status-badge', ['status' => $record->lifecycle_status])</td>
                                <td>{{ optional($record->approved_at)->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @if ($cert && $cert->certificate_number)
                                        @include('partials.status-badge', ['status' => $cert->status->value])
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('indigenes.show', $record) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">groups</span>
                                    <p class="mb-1 fw-semibold">No indigenes found</p>
                                    <p class="small mb-0">Adjust the search or register a new indigene.</p>
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
