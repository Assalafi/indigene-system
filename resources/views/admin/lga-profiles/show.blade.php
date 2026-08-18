@extends('layouts.app')

@section('title', $lga->name.' LGA Profile')
@section('page-title', $lga->name.' LGA Profile')
@section('page-subtitle', $lga->state->name.' State &middot; Certificate branding and signatory authority')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.lga-profiles.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">LGA Profiles</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $lga->name }}</span></li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Current profile</h5>
                    @if ($lga->profile)
                        <dl class="review-grid">
                            <div class="review-item"><dt>Display name</dt><dd>{{ $lga->profile->display_name ?? $lga->name }}</dd></div>
                            <div class="review-item"><dt>Version</dt><dd>v{{ $lga->profile->version_no }} &middot; {{ $lga->profile->status }}</dd></div>
                            <div class="review-item"><dt>Office address</dt><dd>{{ $lga->profile->office_address ?? '—' }}</dd></div>
                            <div class="review-item"><dt>Support</dt><dd>{{ $lga->profile->support_phone ?? '—' }} &middot; {{ $lga->profile->support_email ?? '—' }}</dd></div>
                            <div class="review-item"><dt>Colours</dt>
                                <dd class="d-flex gap-2 align-items-center">
                                    <span class="rounded-2 border d-inline-block" style="width:28px;height:18px;background:{{ $lga->profile->primary_colour ?? '#087A4B' }};" title="Primary"></span>
                                    <span class="rounded-2 border d-inline-block" style="width:28px;height:18px;background:{{ $lga->profile->secondary_colour ?? '#0B1F3A' }};" title="Secondary"></span>
                                </dd>
                            </div>
                            <div class="review-item"><dt>Certificate heading</dt><dd>{{ $lga->profile->certificate_heading ?? '—' }}</dd></div>
                            <div class="review-item"><dt>Footer text</dt><dd>{{ $lga->profile->footer_text ?? '—' }}</dd></div>
                        </dl>
                    @else
                        <div class="alert alert-warning d-flex align-items-start gap-2">
                            <span class="material-symbols-outlined">warning</span>
                            <div>
                                No published profile yet. Certificates cannot be issued until LGA branding
                                and an active signatory exist.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Signatories</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead><tr><th>Name</th><th>Office title</th><th>Effective from</th><th>To</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($lga->signatories as $signatory)
                                    <tr>
                                        <td class="fw-semibold">{{ $signatory->full_name }}</td>
                                        <td>{{ $signatory->office_title }}</td>
                                        <td>{{ optional($signatory->effective_from)->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ optional($signatory->effective_to)->format('d/m/Y') ?? 'Open' }}</td>
                                        <td>@include('partials.status-badge', ['status' => $signatory->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary py-3">No signatories.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            @can('lga-profile.manage')
                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Publish new profile version</h5>
                        <form method="POST" action="{{ route('admin.lga-profiles.store', $lga) }}" enctype="multipart/form-data"
                              data-confirm="Publish a new profile version? Historical certificate versions remain unchanged.">
                            @csrf
                            <input class="form-control form-control-sm mb-2" name="display_name" type="text" placeholder="Display name" maxlength="180">
                            <input class="form-control form-control-sm mb-2" name="office_address" type="text" placeholder="Office address" maxlength="1000">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <input class="form-control form-control-sm" name="support_phone" type="text" placeholder="Support phone" maxlength="20">
                                </div>
                                <div class="col-6">
                                    <input class="form-control form-control-sm" name="support_email" type="email" placeholder="Support email" maxlength="190">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small mb-1">Primary colour</label>
                                    <input class="form-control form-control-sm" name="primary_colour" type="color" value="#087A4B">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small mb-1">Secondary colour</label>
                                    <input class="form-control form-control-sm" name="secondary_colour" type="color" value="#0B1F3A">
                                </div>
                            </div>
                            <input class="form-control form-control-sm mb-2" name="certificate_heading" type="text" placeholder="Certificate heading" maxlength="1000">
                            <input class="form-control form-control-sm mb-2" name="footer_text" type="text" placeholder="Certificate footer text" maxlength="1000">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Logo (JPEG/PNG/WebP, max 2 MB)</label>
                                <input class="form-control form-control-sm" name="logo" type="file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small mb-1">Coat of arms / seal image</label>
                                <input class="form-control form-control-sm" name="coat_of_arms" type="file" accept="image/*">
                            </div>
                            <button class="btn btn-primary-div text-white w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-upload-cloud-2-line me-1"></i> Publish profile version
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Publish new signatory</h5>
                        <form method="POST" action="{{ route('admin.lga-profiles.signatories.store', $lga) }}" enctype="multipart/form-data"
                              data-confirm="Publish this signatory? The previous active signatory is end-dated automatically.">
                            @csrf
                            <input class="form-control form-control-sm mb-2" name="full_name" type="text" placeholder="Full name" maxlength="180" required>
                            <input class="form-control form-control-sm mb-2" name="office_title" type="text" placeholder="Office title" maxlength="150" required>
                            <input class="form-control form-control-sm mb-2" name="appointment_reference" type="text" placeholder="Appointment reference" maxlength="100">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small mb-1">Effective from</label>
                                    <input class="form-control form-control-sm" name="effective_from" type="date" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small mb-1">Effective to</label>
                                    <input class="form-control form-control-sm" name="effective_to" type="date">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">Signature image</label>
                                <input class="form-control form-control-sm" name="signature" type="file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small mb-1">Seal image</label>
                                <input class="form-control form-control-sm" name="seal" type="file" accept="image/*">
                            </div>
                            <button class="btn btn-brand-green w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-how-to-vote-line me-1"></i> Publish signatory
                            </button>
                            <p class="small text-secondary mt-2 mb-0">Only one active primary signatory per LGA.</p>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
