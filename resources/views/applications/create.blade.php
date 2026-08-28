@extends('layouts.app')

@section('title', $creating ? 'Register Indigene' : 'Edit Application')
@section('page-title', $creating ? 'Register Indigene' : 'Edit Application')
@section('page-subtitle', 'Complete the details and submit for approval in one step.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('applications.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Applications</span></a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <span class="fw-medium">{{ $creating ? 'New Application' : $application->application_number }}</span>
    </li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            @include('partials.flash-messages')

            <form method="POST" action="{{ $creating ? route('applications.store') : route('applications.save', $application) }}"
                  enctype="multipart/form-data"
                  data-confirm="Submit this application for approval?">
                @csrf
                @if ($creating && auth()->user()->isSystemAdmin())
                    <input type="hidden" name="lga_id" value="{{ $lga->id }}">
                @endif

                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="form-section-title">
                            <span class="material-symbols-outlined">badge</span>
                            Identity
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="nin" class="form-label">NIN <span class="text-secondary">(optional)</span></label>
                                <input class="form-control nin-input" id="nin" name="nin" type="text" inputmode="numeric"
                                       maxlength="11" placeholder="11 digits" value="{{ old('nin') }}">
                                @error('nin')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="surname" class="form-label">Surname <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="surname" name="surname" type="text" maxlength="100"
                                       value="{{ old('surname', $profile?->surname) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="first_name" name="first_name" type="text" maxlength="100"
                                       value="{{ old('first_name', $profile?->first_name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle name <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="middle_name" name="middle_name" type="text" maxlength="100"
                                       value="{{ old('middle_name', $profile?->middle_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label">Date of birth <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="date_of_birth" name="date_of_birth" type="date"
                                       max="{{ now()->toDateString() }}" value="{{ old('date_of_birth', $profile?->date_of_birth?->toDateString()) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sex" class="form-label">Sex <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="sex" name="sex" required>
                                    <option value="">Select</option>
                                    <option value="male" @selected(old('sex', $profile?->sex) === 'male')>Male</option>
                                    <option value="female" @selected(old('sex', $profile?->sex) === 'female')>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="marital_status" class="form-label">Marital status <span class="text-secondary">(optional)</span></label>
                                <select class="form-select" id="marital_status" name="marital_status">
                                    <option value="">—</option>
                                    @foreach (['Single', 'Married', 'Divorced', 'Separated', 'Widowed'] as $ms)
                                        <option value="{{ $ms }}" @selected(old('marital_status', $profile?->marital_status) === $ms)>{{ $ms }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="phone" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                                <input class="form-control phone-input" id="phone" name="phone" type="text" maxlength="20"
                                       placeholder="+2348012345678" value="{{ old('phone', $profile?->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="email" name="email" type="email" maxlength="190"
                                       value="{{ old('email', $profile?->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="photo" class="form-label">Photograph <span class="text-secondary">{{ $creating ? '(recommended)' : '(optional)' }}</span></label>
                                @if (! $creating && $profile?->photoFile)
                                    @php $existingPhoto = \Illuminate\Support\Facades\Storage::disk($profile->photoFile->storage_disk)->exists($profile->photoFile->object_key); @endphp
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <img src="{{ route('documents.photo', ['file' => $profile->photoFile]) }}" alt="Current photograph"
                                             class="rounded-3" style="width:64px;height:64px;object-fit:cover;"
                                             @if (! $existingPhoto) onerror="this.style.display='none'" @endif>
                                        <div class="small">
                                            <span class="text-secondary">Current photo</span>
                                            @if (! $existingPhoto)
                                                <span class="text-danger d-block">File missing — upload a replacement.</span>
                                            @else
                                                <span class="text-secondary d-block">Upload a new file to replace it.</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <input class="form-control" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">JPEG, PNG or WebP, max 5 MB. Face clearly visible.</div>
                                @error('photo')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <input class="form-control" type="text" value="{{ $profile?->nationality ?? 'Nigerian' }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="form-section-title">
                            <span class="material-symbols-outlined">location_on</span>
                            Place of origin
                        </h5>
                        <div class="alert alert-light border small">
                            Issuing authority: <strong>{{ $lga->name }} LGA, {{ $state->name }} State</strong>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="district_id" class="form-label">District <span class="required-indicator">Required</span></label>
                                <select class="form-select searchable-select" id="district_id" name="district_id" required>
                                    <option value="">Select district</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}" @selected(old('district_id', $profile?->district_id) === $district->id)>{{ $district->name }}</option>
                                    @endforeach
                                </select>
                                @error('district_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="ward_id" class="form-label">Ward <span class="text-secondary">(optional)</span></label>
                                <select class="form-select searchable-select" id="ward_id" name="ward_id">
                                    <option value="">Select ward (optional)</option>
                                    @foreach ($wards as $ward)
                                        <option value="{{ $ward->id }}" @selected(old('ward_id', $profile?->ward_id) === $ward->id)>{{ $ward->name }}</option>
                                    @endforeach
                                </select>
                                @error('ward_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="unit_id" class="form-label">Village / community unit <span class="required-indicator">Required</span></label>
                                <select class="form-select searchable-select" id="unit_id" name="unit_id" required>
                                    <option value="">Select unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('unit_id', $profile?->unit_id) === $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="hometown" class="form-label">Hometown <span class="text-secondary">(optional)</span></label>
                                <input class="form-control" id="hometown" name="hometown" type="text" maxlength="180"
                                       value="{{ old('hometown', $profile?->hometown) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="form-section-title">
                            <span class="material-symbols-outlined">escalator_warning</span>
                            Guardian / Parent
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="guardian_name" class="form-label">Guardian / parent name <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="guardian_name" name="guardian_name" type="text" maxlength="180"
                                       value="{{ old('guardian_name', $guardian?->full_name) }}" required>
                                @error('guardian_name')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="guardian_phone" class="form-label">Guardian / parent phone <span class="text-secondary">(optional)</span></label>
                                <input class="form-control phone-input" id="guardian_phone" name="guardian_phone" type="text" maxlength="20"
                                       placeholder="+2348012345678" value="{{ old('guardian_phone', $guardian?->phone) }}">
                                @error('guardian_phone')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="acknowledge" name="acknowledge" value="1" required>
                            <label class="form-check-label" for="acknowledge">
                                I confirm the information above is accurate, the applicant has been informed of how
                                their data is used, and I authorise this application for LGA approval and certificate
                                issuance. <span class="required-indicator">Required</span>
                            </label>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary rounded-3 fw-semibold">Cancel</a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                <i class="ri-send-plane-line me-1"></i> Submit for approval
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var units = @json($unitOptions);
        var districtSel = document.getElementById('district_id');
        var wardSel = document.getElementById('ward_id');
        var unitSel = document.getElementById('unit_id');

        // No hierarchy mapping: the village list is independent. Choosing a village
        // auto-fills its ward and district so the record stays consistent.
        unitSel.addEventListener('change', function () {
            var u = units.find(function (x) { return x.id === unitSel.value; });
            if (!u) { return; }
            if (u.district_id) {
                districtSel.value = u.district_id;
                districtSel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (u.ward_id) {
                wardSel.value = u.ward_id;
                wardSel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
</script>
@endpush
