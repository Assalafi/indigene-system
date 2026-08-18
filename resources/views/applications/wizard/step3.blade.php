@extends('layouts.app')

@section('title', 'Step 3: Place of Origin')
@section('page-title', 'New Application - Step 3 of 8')
@section('page-subtitle', 'Place of origin &middot; <span id="autosave-indicator" class="text-success">Draft autosaves after a short pause</span>')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">location_on</span>
                        Place of origin
                    </h5>

                    <div class="alert alert-light border d-flex align-items-start gap-2">
                        <span class="material-symbols-outlined">lock</span>
                        <div>
                            State and LGA are fixed to your assigned scope:
                            <strong>{{ $lga->name }} LGA, {{ $state->name }} State</strong>.
                            Only active wards and village/community units of this LGA are shown.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 3]) }}" data-autosave>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">State</label>
                                <input class="form-control" type="text" value="{{ $state->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LGA</label>
                                <input class="form-control" type="text" value="{{ $lga->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="district_id" class="form-label">District <span class="text-secondary">(optional, where used)</span></label>
                                <select class="form-select" id="district_id" name="district_id">
                                    <option value="">— None —</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}" @selected($profile->district_id === $district->id)>{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ward_id" class="form-label">Ward <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="ward_id" name="ward_id" required>
                                    <option value="">Select ward</option>
                                    @foreach ($wards as $ward)
                                        <option value="{{ $ward->id }}" @selected($profile->ward_id === $ward->id)>{{ $ward->name }}</option>
                                    @endforeach
                                </select>
                                @error('ward_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="unit_id" class="form-label">Village / community unit <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="unit_id" name="unit_id" required>
                                    <option value="">Select unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected($profile->unit_id === $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="hometown" class="form-label">Hometown <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="hometown" name="hometown" type="text" maxlength="180"
                                       value="{{ old('hometown', $profile->hometown) }}">
                                <div class="form-text">Free text never substitutes for selected ward/unit data.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 2]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to contact <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var wards = @json($wards->map(fn ($w) => ['id' => $w->id, 'name' => $w->name, 'district_id' => $w->district_id]));
        var units = @json($units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'ward_id' => $u->ward_id]));
        var districtSel = document.getElementById('district_id');
        var wardSel = document.getElementById('ward_id');
        var unitSel = document.getElementById('unit_id');

        function refreshWards() {
            var district = districtSel.value;
            var current = wardSel.value;
            wardSel.innerHTML = '<option value="">Select ward</option>';
            wards.filter(function (w) { return !district || !w.district_id || w.district_id === district; })
                .forEach(function (w) {
                    var opt = document.createElement('option');
                    opt.value = w.id;
                    opt.textContent = w.name;
                    if (w.id === current) { opt.selected = true; }
                    wardSel.appendChild(opt);
                });
            refreshUnits();
        }

        function refreshUnits() {
            var ward = wardSel.value;
            var current = unitSel.value;
            unitSel.innerHTML = '<option value="">Select unit</option>';
            units.filter(function (u) { return u.ward_id === ward; })
                .forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    if (u.id === current) { opt.selected = true; }
                    unitSel.appendChild(opt);
                });
        }

        districtSel.addEventListener('change', function () {
            wardSel.value = '';
            unitSel.value = '';
            refreshWards();
        });
        wardSel.addEventListener('change', function () {
            unitSel.value = '';
            refreshUnits();
        });
    });
</script>
@endpush
