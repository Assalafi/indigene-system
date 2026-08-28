@extends('layouts.app')

@section('title', $indigene->registry_number)
@section('page-title', $indigene->fullName())
@section('page-subtitle', 'Registry '.$indigene->registry_number.' &middot; '.$indigene->originLga->name.' LGA, '.$indigene->originState->name.' State')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('indigenes.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Indigenes</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $indigene->registry_number }}</span></li>
@endsection

@section('content')
    @if (session('revealed_nin'))
        <div class="alert alert-danger d-flex align-items-start gap-2">
            <span class="material-symbols-outlined">visibility</span>
            <div>
                <strong>Full NIN: {{ session('revealed_nin') }}</strong><br>
                This reveal has been recorded in the sensitive-access log.
            </div>
        </div>
    @endif

    @php $profile = $indigene->currentProfile; @endphp

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if ($profile?->photoFile)
                        <img src="{{ route('documents.photo', ['file' => $profile->photoFile]) }}" alt="Applicant photograph"
                             class="rounded-3" style="width:110px;height:110px;object-fit:cover;" onerror="this.style.display='none'">
                    @else
                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width:110px;height:110px;">
                            <span class="material-symbols-outlined text-secondary" style="font-size:36px;">person</span>
                        </div>
                    @endif
                    <div>
                        <h4 class="mb-1">{{ $profile?->displayName() ?? '—' }}</h4>
                        <div class="text-secondary">
                            NIN <span class="nin-mask">{{ $indigene->maskedNin() }}</span>
                            &middot; {{ $profile?->sex ? ucfirst($profile->sex) : '—' }}
                            &middot; DOB {{ $profile?->date_of_birth?->format('d/m/Y') ?? '—' }}
                        </div>
                        <div class="mt-2">
                            @include('partials.status-badge', ['status' => $indigene->lifecycle_status])
                            @foreach ($indigene->certificates->where('certificate_number', '!=', null) as $cert)
                                @include('partials.status-badge', ['status' => $cert->status->value])
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @can('amend', $indigene)
                        <form method="POST" action="{{ route('indigenes.amendments', $indigene) }}"
                              data-confirm="Start an amendment? The active certificate stays based on the current approved version until the amendment is approved.">
                            @csrf
                            <button class="btn btn-outline-primary-div rounded-3 fw-semibold" type="submit">
                                <i class="ri-edit-line me-1"></i> Start amendment
                            </button>
                        </form>
                    @endcan
                    @foreach ($indigene->certificates->whereIn('status', ['active', 'suspended']) as $cert)
                        <a href="{{ route('certificates.show', $cert) }}" class="btn btn-primary-div text-white rounded-3 fw-semibold">
                            <i class="ri-verified-badge-line me-1"></i> Certificate {{ $cert->certificate_number }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-bio">Bio data</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-origin">Origin &amp; residence</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-family">Family / guardian / kin</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-apps">Applications &amp; versions</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-certs">Certificates &amp; prints</a></li>
                        @can('audit.view', \App\Models\AuditLog::class)
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-access">Access history</a></li>
                        @endcan
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-bio">
                            <dl class="review-grid">
                                <div class="review-item"><dt>Full name</dt><dd>{{ $profile?->displayName() ?? '—' }}</dd></div>
                                <div class="review-item"><dt>NIN</dt><dd class="nin-mask">{{ $indigene->maskedNin() }}</dd></div>
                                <div class="review-item"><dt>NIN verification</dt><dd>{{ str_replace('_', ' ', ucfirst($indigene->nin_verification_status)) }}</dd></div>
                                <div class="review-item"><dt>Date of birth</dt><dd>{{ $profile?->date_of_birth?->format('d/m/Y') ?? '—' }} ({{ $profile?->age() ?? '—' }} years)</dd></div>
                                <div class="review-item"><dt>Sex</dt><dd>{{ $profile?->sex ? ucfirst($profile->sex) : '—' }}</dd></div>
                                <div class="review-item"><dt>Marital status</dt><dd>{{ $profile?->marital_status ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Nationality</dt><dd>{{ $profile?->nationality ?? 'Nigerian' }}</dd></div>
                                <div class="review-item"><dt>Occupation</dt><dd>{{ $profile?->occupation ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Phone</dt><dd>{{ $profile?->phone ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Email</dt><dd>{{ $profile?->email ?? '—' }}</dd></div>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="tab-origin">
                            <dl class="review-grid">
                                <div class="review-item"><dt>State / LGA</dt><dd>{{ $indigene->originState->name }} / {{ $indigene->originLga->name }}</dd></div>
                                <div class="review-item"><dt>District</dt><dd>{{ $profile?->district?->name ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Ward</dt><dd>{{ $profile?->ward?->name ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Village / community unit</dt><dd>{{ $profile?->unit?->name ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Indigene basis</dt><dd>{{ $profile?->indigene_basis ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Residential address</dt><dd>{{ $profile?->residential_address ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Residence</dt><dd>{{ $profile?->residence_town ?? '—' }}{{ $profile?->residenceState ? ', '.$profile->residenceState->name : '' }}</dd></div>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="tab-family">
                            <dl class="review-grid">
                                @forelse ($profile?->relations ?? [] as $relation)
                                    <div class="review-item">
                                        <dt>{{ $relation->relationTypeLabel() }}</dt>
                                        <dd>{{ $relation->full_name }}
                                            @if ($relation->relationship) &middot; {{ $relation->relationship }} @endif
                                            @if ($relation->phone) &middot; {{ $relation->phone }} @endif
                                            @if ($relation->address) &middot; {{ $relation->address }} @endif
                                        </dd>
                                    </div>
                                @empty
                                    <div class="review-item"><dt>Relations</dt><dd>None recorded</dd></div>
                                @endforelse
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="tab-apps">
                            @forelse ($indigene->applications as $app)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <a href="{{ route('applications.show', $app) }}" class="fw-semibold">{{ $app->application_number }}</a>
                                        <span class="small text-secondary ms-2">{{ ucfirst($app->application_type) }} &middot; v{{ $app->profile->version_no }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @include('partials.status-badge', ['status' => $app->status->value])
                                        <span class="small text-secondary">{{ $app->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-secondary">No applications.</p>
                            @endforelse
                        </div>
                        <div class="tab-pane fade" id="tab-certs">
                            @forelse ($indigene->certificates as $cert)
                                <div class="border rounded-3 p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <a href="{{ route('certificates.show', $cert) }}" class="fw-semibold">{{ $cert->certificate_number ?? 'Eligible (not yet issued)' }}</a>
                                            <div class="small text-secondary">
                                                Issued {{ optional($cert->issued_at)->format('d/m/Y') ?? '—' }}
                                                &middot; Prints: {{ $cert->total_prints_cached }}
                                                &middot; Versions: {{ $cert->versions->count() }}
                                            </div>
                                        </div>
                                        @include('partials.status-badge', ['status' => $cert->status->value])
                                    </div>
                                    @if ($cert->printEvents->isNotEmpty())
                                        <div class="small text-secondary mt-2">Print history:</div>
                                        <ul class="small list-unstyled mb-0 mt-1">
                                            @foreach ($cert->printEvents as $print)
                                                <li>
                                                    {{ $print->copyLabel() }} &middot; {{ optional($print->requester)->full_name }}
                                                    &middot; {{ $print->created_at->format('d/m/Y H:i') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @empty
                                <p class="text-secondary">No certificates yet.</p>
                            @endforelse
                        </div>
                        @can('audit.view', \App\Models\AuditLog::class)
                            <div class="tab-pane fade" id="tab-access">
                                <ul class="activity-timeline list-unstyled mb-0">
                                    @forelse ($accessLogs as $log)
                                        <li class="tl-item">
                                            <div class="fw-semibold small">{{ str_replace('_', ' ', ucfirst($log->action)) }} &middot; {{ $log->data_category }}</div>
                                            <div class="small text-secondary">{{ optional($log->actor)->full_name }} &middot; {{ $log->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="small mt-1">{{ $log->purpose }}</div>
                                        </li>
                                    @empty
                                        <li class="text-secondary">No sensitive access recorded.</li>
                                    @endforelse
                                </ul>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @can('revealNin', $indigene)
                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">NIN reveal</h5>
                        <p class="small text-secondary">
                            Revealing a full NIN requires a reason.
                            Every reveal is recorded in the sensitive-access log.
                        </p>
                        <form method="POST" action="{{ route('indigenes.reveal-nin', $indigene) }}"
                              data-confirm="Reveal the full NIN? This privileged access is logged.">
                            @csrf
                            <div class="mb-3">
                                <input class="form-control" name="purpose" type="text" placeholder="Reason (required)" maxlength="1000" required>
                            </div>
                            <button class="btn btn-outline-danger w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-eye-line me-1"></i> Reveal full NIN
                            </button>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Registry facts</h5>
                    <dl class="review-grid">
                        <div class="review-item"><dt>Registry number</dt><dd>{{ $indigene->registry_number }}</dd></div>
                        <div class="review-item"><dt>Registered by</dt><dd>{{ optional($indigene->creator)->full_name ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Approved</dt><dd>{{ optional($indigene->approved_at)->format('d/m/Y H:i') ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Lifecycle</dt><dd>{{ ucfirst($indigene->lifecycle_status) }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
