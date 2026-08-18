@extends('layouts.app')

@section('title', 'Wards & Units')
@section('page-title', 'Wards, Districts & Units')
@section('page-subtitle', 'Wards are official Registration Areas. Districts are optional local groupings. Units are village/community/administrative units. Polling units are stored separately and never used as certificate village text.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Wards, Districts &amp; Units</span></li>
@endsection

@php
    $canManage = auth()->user()->can('geography.manage-local') || auth()->user()->can('geography.manage-national');
    $tab = $tab ?? 'districts';
    $tabQuery = fn (string $t) => array_merge(request()->except(['tab', 'page', 'page_d', 'page_w', 'page_u']), ['tab' => $t]);
@endphp

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0 fw-semibold">Geography records</h5>
                <span class="small text-secondary">
                    {{ number_format($districts->total()) }} districts &middot;
                    {{ number_format($wards->total()) }} wards &middot;
                    {{ number_format($units->total()) }} units
                </span>
            </div>

            <form method="GET" action="{{ route('geography.wards') }}" class="row g-2 mb-3 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-3">
                    <input class="form-control" type="text" name="q" placeholder="Search name&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All categories</option>
                        @foreach (['village', 'community', 'village_unit', 'administrative_unit', 'polling_unit'] as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ str_replace('_', ' ', ucfirst($category)) }}</option>
                        @endforeach
                    </select>
                </div>
                @if (auth()->user()->isSystemAdmin())
                    <div class="col-md-3">
                        <select class="form-select" name="state_id"
                                data-state-cascade data-lga-target="#geo_filter_lga"
                                data-lga-url="{{ route('api.geography.lgas-by-state') }}">
                            <option value="">All states</option>
                            @foreach ($states as $stateOption)
                                <option value="{{ $stateOption->id }}" @selected(request('state_id') === $stateOption->id)>{{ $stateOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="geo_filter_lga" name="lga_id">
                            <option value="">All LGAs</option>
                            @foreach (\App\Models\Lga::with('state')->where('status', 'active')->orderBy('name')->get() as $lgaOption)
                                <option value="{{ $lgaOption->id }}" @selected(request('lga_id') === $lgaOption->id)>{{ $lgaOption->name }} ({{ $lgaOption->state->name }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-1">
                    <button class="btn btn-primary-div text-white w-100" type="submit" title="Apply filters">
                        <i class="ri-filter-line"></i>
                    </button>
                </div>
            </form>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'districts' ? 'active' : '' }}" href="{{ route('geography.wards', $tabQuery('districts')) }}">
                        Districts <span class="badge bg-light text-secondary ms-1">{{ number_format($districts->total()) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'wards' ? 'active' : '' }}" href="{{ route('geography.wards', $tabQuery('wards')) }}">
                        Wards <span class="badge bg-light text-secondary ms-1">{{ number_format($wards->total()) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'units' ? 'active' : '' }}" href="{{ route('geography.wards', $tabQuery('units')) }}">
                        Units <span class="badge bg-light text-secondary ms-1">{{ number_format($units->total()) }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Districts --}}
                <div class="tab-pane fade {{ $tab === 'districts' ? 'show active' : '' }}" id="tab-districts">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th><th>LGA</th><th>Code</th><th>Status</th>
                                    @if ($canManage)<th class="text-end">Actions</th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($districts as $district)
                                    <tr>
                                        <td class="fw-semibold">{{ $district->name }}</td>
                                        <td>{{ $district->lga->name }}</td>
                                        <td><code>{{ $district->code }}</code></td>
                                        <td>@include('partials.status-badge', ['status' => $district->status])</td>
                                        @if ($canManage)
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-record"
                                                            data-modal="#editDistrictModal"
                                                            data-id="{{ $district->id }}"
                                                            data-name="{{ $district->name }}"
                                                            data-code="{{ $district->code }}"
                                                            data-status="{{ $district->status }}">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('geography.destroy') }}"
                                                          data-confirm="Delete this district? If it is referenced by applications or certificates it will be retired instead.">
                                                        @csrf
                                                        <input type="hidden" name="type" value="district">
                                                        <input type="hidden" name="id" value="{{ $district->id }}">
                                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="ri-delete-bin-line"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $canManage ? 5 : 4 }}" class="text-center text-secondary py-4">
                                        No districts yet. Add them in the "Add local geography" section below.
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $districts])
                </div>

                {{-- Wards --}}
                <div class="tab-pane fade {{ $tab === 'wards' ? 'show active' : '' }}" id="tab-wards">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th><th>LGA</th><th>District</th><th>Active units</th><th>Status</th>
                                    @if ($canManage)<th class="text-end">Actions</th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($wards as $ward)
                                    <tr>
                                        <td class="fw-semibold">{{ $ward->name }}</td>
                                        <td>{{ $ward->lga->name }}</td>
                                        <td>{{ $ward->district?->name ?? '—' }}</td>
                                        <td>{{ $ward->units->count() }}</td>
                                        <td>@include('partials.status-badge', ['status' => $ward->status])</td>
                                        @if ($canManage)
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-record"
                                                            data-modal="#editWardModal"
                                                            data-id="{{ $ward->id }}"
                                                            data-name="{{ $ward->name }}"
                                                            data-code="{{ $ward->code }}"
                                                            data-status="{{ $ward->status }}">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('geography.destroy') }}"
                                                          data-confirm="Delete this ward? If it is referenced by units, applications or certificates it will be retired instead.">
                                                        @csrf
                                                        <input type="hidden" name="type" value="ward">
                                                        <input type="hidden" name="id" value="{{ $ward->id }}">
                                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="ri-delete-bin-line"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $canManage ? 6 : 5 }}" class="text-center text-secondary py-4">No wards found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $wards])
                </div>

                {{-- Units --}}
                <div class="tab-pane fade {{ $tab === 'units' ? 'show active' : '' }}" id="tab-units">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th><th>Category</th><th>Ward</th><th>LGA</th><th>Status</th>
                                    @if ($canManage)<th class="text-end">Actions</th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($units as $unit)
                                    <tr>
                                        <td class="fw-semibold">{{ $unit->name }}</td>
                                        <td>
                                            <span class="status-badge {{ $unit->isPollingUnit() ? 'status-superseded' : 'status-draft' }}">
                                                {{ $unit->categoryLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ $unit->ward->name }}</td>
                                        <td>{{ $unit->lga->name }}</td>
                                        <td>@include('partials.status-badge', ['status' => $unit->status])</td>
                                        @if ($canManage)
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-record"
                                                            data-modal="#editUnitModal"
                                                            data-id="{{ $unit->id }}"
                                                            data-name="{{ $unit->name }}"
                                                            data-code="{{ $unit->code }}"
                                                            data-category="{{ $unit->category }}"
                                                            data-status="{{ $unit->status }}">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('geography.destroy') }}"
                                                          data-confirm="Delete this unit? If it is referenced by applications or certificates it will be retired instead.">
                                                        @csrf
                                                        <input type="hidden" name="type" value="unit">
                                                        <input type="hidden" name="id" value="{{ $unit->id }}">
                                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="ri-delete-bin-line"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $canManage ? 6 : 5 }}" class="text-center text-secondary py-4">No units found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $units])
                </div>
            </div>
        </div>
    </div>

    @if ($canManage)
        @php
            $assignedLga = auth()->user()->activeLga();
            $manageLgas = auth()->user()->isSystemAdmin()
                ? \App\Models\Lga::where('status', 'active')->orderBy('name')->get()
                : collect([$assignedLga]);
        @endphp
        <div class="card bg-white border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-1">Add local geography</h5>
                <p class="small text-secondary mb-3">
                    One name per line &middot; codes optional (auto-generated when blank).
                    {{ auth()->user()->isSystemAdmin() ? 'Select a state to populate the LGA dropdowns.' : 'Records are added to your assigned LGA. Only active records can be selected on applications.' }}
                </p>
                @unless ($assignedLga || auth()->user()->isSystemAdmin())
                    <div class="alert alert-warning">No active LGA assignment.</div>
                @else
                    @if (auth()->user()->isSystemAdmin())
                        <div class="row g-3 mb-4 align-items-end">
                            <div class="col-md-5">
                                <label for="geo_state" class="form-label small fw-semibold">State / FCT</label>
                                <select class="form-select form-select-sm" id="geo_state"
                                        data-state-cascade data-lga-targets="#geo_lga_district,#geo_lga_ward,#geo_lga_unit"
                                        data-lga-url="{{ route('api.geography.lgas-by-state') }}">
                                    <option value="">Select state&hellip;</option>
                                    @foreach ($states as $stateOption)
                                        <option value="{{ $stateOption->id }}">{{ $stateOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold mb-3"><i class="ri-layout-2-line me-2 text-brand-green"></i>Add districts</h6>
                                <form method="POST" action="{{ route('geography.districts.store') }}"
                                      data-confirm="Add these districts? The action is recorded in the audit log.">
                                    @csrf
                                    @if (auth()->user()->isSystemAdmin())
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm" id="geo_lga_district" name="lga_id" required>
                                                <option value="">Select state first, then LGA</option>
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="lga_id" value="{{ $assignedLga->id }}">
                                    @endif
                                    <textarea class="form-control form-control-sm mb-2" name="names" rows="3"
                                              placeholder="One district name per line&#10;e.g. North&#10;South" required></textarea>
                                    <textarea class="form-control form-control-sm mb-2" name="codes" rows="2"
                                              placeholder="Optional codes, one per line (blank = auto-generated)"></textarea>
                                    @error('names')<span class="text-danger small">{{ $message }}</span>@enderror
                                    <button class="btn btn-sm btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Add district(s)</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold mb-3"><i class="ri-community-line me-2 text-brand-green"></i>Add wards</h6>
                                <form method="POST" action="{{ route('geography.wards.store') }}"
                                      data-confirm="Add these wards? The action is recorded in the audit log.">
                                    @csrf
                                    @if (auth()->user()->isSystemAdmin())
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm" id="geo_lga_ward" name="lga_id" required>
                                                <option value="">Select state first, then LGA</option>
                                            </select>
                                        </div>
                                        <input class="form-control form-control-sm mb-2" name="district_id" placeholder="District ID (optional UUID)">
                                    @else
                                        <input type="hidden" name="lga_id" value="{{ $assignedLga->id }}">
                                    @endif
                                    <textarea class="form-control form-control-sm mb-2" name="names" rows="3"
                                              placeholder="One ward name per line&#10;e.g. Ajigin&#10;Bulabulin" required></textarea>
                                    <textarea class="form-control form-control-sm mb-2" name="codes" rows="2"
                                              placeholder="Optional codes, one per line (blank = auto-generated)"></textarea>
                                    @error('names')<span class="text-danger small">{{ $message }}</span>@enderror
                                    <button class="btn btn-sm btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Add ward(s)</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold mb-3"><i class="ri-map-pin-line me-2 text-brand-green"></i>Add village / community units</h6>
                                <form method="POST" action="{{ route('geography.units.store') }}"
                                      data-confirm="Add these units? The action is recorded in the audit log.">
                                    @csrf
                                    @if (auth()->user()->isSystemAdmin())
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm" id="geo_lga_unit" name="lga_id" required
                                                    data-ward-cascade data-ward-target="#geo_ward_unit"
                                                    data-ward-url="{{ route('api.geography.wards-by-lga') }}">
                                                <option value="">Select state first, then LGA</option>
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="lga_id" value="{{ $assignedLga->id }}">
                                    @endif
                                    <div class="mb-2">
                                        <select class="form-select form-select-sm" id="geo_ward_unit" name="ward_id" required>
                                            <option value="">Select ward</option>
                                            @if (!auth()->user()->isSystemAdmin())
                                                @foreach (\App\Models\Ward::where('lga_id', $assignedLga?->id ?? '00000000-0000-0000-0000-000000000000')->where('status', 'active')->orderBy('name')->get() as $ward)
                                                    <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <textarea class="form-control form-control-sm mb-2" name="names" rows="2"
                                              placeholder="One village/community name per line&#10;e.g. Ajigin&#10;Kuboa" required></textarea>
                                    <textarea class="form-control form-control-sm mb-2" name="codes" rows="1"
                                              placeholder="Optional codes, one per line (blank = auto-generated)"></textarea>
                                    <select class="form-select form-select-sm mb-2" name="category" required>
                                        @foreach (['village', 'community', 'village_unit', 'administrative_unit', 'polling_unit'] as $category)
                                            <option value="{{ $category }}">{{ str_replace('_', ' ', ucfirst($category)) }}</option>
                                        @endforeach
                                    </select>
                                    <p class="small text-secondary mb-2">Polling units are not used as certificate village text unless explicitly mapped.</p>
                                    @error('names')<span class="text-danger small">{{ $message }}</span>@enderror
                                    <button class="btn btn-sm btn-outline-primary-div w-100 rounded-3 fw-semibold" type="submit">Add unit(s)</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    @endif

    @if ($canManage)
        {{-- Edit district modal --}}
        <div class="modal fade" id="editDistrictModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('geography.update') }}">
                    @csrf
                    <input type="hidden" name="type" value="district">
                    <input type="hidden" name="id" data-field="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit district</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Name <span class="required-indicator">Required</span></label>
                            <input class="form-control form-control-sm" name="name" data-field="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Code</label>
                            <input class="form-control form-control-sm" name="code" data-field="code" maxlength="40">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Status</label>
                            <select class="form-select form-select-sm" name="status" data-field="status">
                                <option value="active">Active</option>
                                <option value="retired">Retired</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-div text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit ward modal --}}
        <div class="modal fade" id="editWardModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('geography.update') }}">
                    @csrf
                    <input type="hidden" name="type" value="ward">
                    <input type="hidden" name="id" data-field="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit ward</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Name <span class="required-indicator">Required</span></label>
                            <input class="form-control form-control-sm" name="name" data-field="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Code</label>
                            <input class="form-control form-control-sm" name="code" data-field="code" maxlength="40">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Status</label>
                            <select class="form-select form-select-sm" name="status" data-field="status">
                                <option value="active">Active</option>
                                <option value="retired">Retired</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-div text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit unit modal --}}
        <div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('geography.update') }}">
                    @csrf
                    <input type="hidden" name="type" value="unit">
                    <input type="hidden" name="id" data-field="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit village / community unit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Name <span class="required-indicator">Required</span></label>
                            <input class="form-control form-control-sm" name="name" data-field="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Code</label>
                            <input class="form-control form-control-sm" name="code" data-field="code" maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Category</label>
                            <select class="form-select form-select-sm" name="category" data-field="category">
                                @foreach (['village', 'community', 'village_unit', 'administrative_unit', 'polling_unit'] as $category)
                                    <option value="{{ $category }}">{{ str_replace('_', ' ', ucfirst($category)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Status</label>
                            <select class="form-select form-select-sm" name="status" data-field="status">
                                <option value="active">Active</option>
                                <option value="retired">Retired</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-div text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Populate the edit modals from the clicked row's data attributes.
    document.querySelectorAll('.edit-record').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = document.querySelector(btn.getAttribute('data-modal'));
            if (!modal) { return; }
            ['id', 'name', 'code', 'category', 'status'].forEach(function (field) {
                var val = btn.getAttribute('data-' + field);
                var el = modal.querySelector('[data-field="' + field + '"]');
                if (el && val !== null && val !== '') { el.value = val; }
            });
        });
    });
</script>
@endpush
