@extends('layouts.app')

@section('title', $application->application_number)
@section('page-title', $application->application_number)
@section('page-subtitle', ucfirst($application->application_type).' application &middot; '.$application->lga->name.' LGA')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('applications.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Applications</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $application->application_number }}</span></li>
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

    @if ($application->status === \App\Enums\ApplicationStatus::ChangesRequested)
        @php $lastCorrection = $application->reviews->where('outcome', 'changes_requested')->first(); @endphp
        @if ($lastCorrection)
            <div class="alert alert-warning d-flex align-items-start gap-2">
                <span class="material-symbols-outlined">edit_note</span>
                <div>
                    <strong>Corrections requested.</strong>
                    @if ($lastCorrection->checklist)
                        <ul class="mb-1 mt-2">
                            @foreach ($lastCorrection->checklist as $correction)
                                <li>{{ $correction }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($lastCorrection->public_comment)
                        <span>{{ $lastCorrection->public_comment }}</span>
                    @endif
                    <div class="mt-2">
                        @can('update', $application)
                            <a href="{{ route('applications.edit', $application) }}" class="btn btn-sm btn-warning rounded-3 fw-semibold">
                                <i class="ri-edit-line me-1"></i> Correct and resubmit
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <h5 class="mb-0 fw-semibold">Applicant profile</h5>
                        @include('partials.status-badge', ['status' => $application->status->value])
                    </div>

                    @php $profile = $application->profile; $indigene = $application->indigene; @endphp

                    <div class="d-flex align-items-center gap-3 mb-4">
                        @if ($profile->photoFile)
                            <img src="{{ route('documents.photo', ['file' => $profile->photoFile]) }}" alt="Applicant photograph"
                                 class="rounded-3" style="width:96px;height:96px;object-fit:cover;" onerror="this.style.display='none'">
                        @else
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width:96px;height:96px;">
                                <span class="material-symbols-outlined text-secondary">person</span>
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-1">{{ $profile->displayName() ?: 'Draft - name pending' }}</h5>
                            <div class="text-secondary small">
                                Registry {{ $indigene->registry_number }} &middot; NIN <span class="nin-mask">{{ $indigene->maskedNin() }}</span>
                                &middot; {{ $profile->sex ? ucfirst($profile->sex) : '—' }}
                                &middot; DOB {{ $profile->date_of_birth?->format('d/m/Y') ?? '—' }}
                            </div>
                            <div class="text-secondary small">
                                {{ $profile->originLga->name ?? '—' }} LGA, {{ $profile->originState->name ?? '—' }} State
                                &middot; Ward: {{ $profile->ward?->name ?? '—' }}
                                &middot; Unit: {{ $profile->unit?->name ?? '—' }}
                                @if ($profile->district) &middot; District: {{ $profile->district->name }} @endif
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-overview">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-duplicates">Duplicate flags</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-history">History</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-overview">
                            <dl class="review-grid">
                                <div class="review-item"><dt>Full name</dt><dd>{{ $profile->displayName() ?: '—' }}</dd></div>
                                <div class="review-item"><dt>NIN</dt><dd class="nin-mask">{{ $indigene->maskedNin() }}</dd></div>
                                <div class="review-item"><dt>Date of birth</dt><dd>{{ $profile->date_of_birth?->format('d/m/Y') ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Sex</dt><dd>{{ $profile->sex ? ucfirst($profile->sex) : '—' }}</dd></div>
                                <div class="review-item"><dt>Phone</dt><dd>{{ $profile->phone ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Email</dt><dd>{{ $profile->email ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Ward</dt><dd>{{ $profile->ward?->name ?? '—' }}</dd></div>
                                <div class="review-item"><dt>Village / unit</dt><dd>{{ $profile->unit?->name ?? '—' }}</dd></div>
                                @php $guardian = $profile->relations->firstWhere('relation_type', 'guardian'); @endphp
                                <div class="review-item"><dt>Guardian</dt>
                                    <dd>
                                        @if ($guardian)
                                            {{ $guardian->full_name }}@if ($guardian->phone) &middot; {{ $guardian->phone }}@endif
                                        @else
                                            —
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="tab-duplicates">
                            @forelse ($application->duplicateFlags as $flag)
                                <div class="border rounded-3 p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <span class="risk-chip {{ $flag->score >= 80 ? 'high' : ($flag->score >= 30 ? 'medium' : 'low') }}">
                                                {{ $flag->matchTypeLabel() }} @if ($flag->score) &middot; {{ $flag->score }}% @endif
                                            </span>
                                            <span class="status-badge status-{{ $flag->status === 'open' ? 'pending_chairman' : ($flag->status === 'false_positive' ? 'approved' : 'draft') }}">
                                                {{ str_replace('_', ' ', $flag->status) }}
                                            </span>
                                        </div>
                                        <span class="small text-secondary">{{ $flag->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if ($flag->candidate)
                                        <div class="small text-secondary mt-2">
                                            Candidate: {{ $flag->candidate->currentProfile?->displayName() }}
                                            ({{ $flag->candidate->registry_number }}) - {{ $flag->candidate->currentProfile?->originLga?->name }} LGA
                                        </div>
                                    @endif
                                    <div class="small mt-1">{{ data_get($flag->evidence, 'message') }}</div>
                                </div>
                            @empty
                                <p class="text-secondary mb-0">No duplicate flags for this application.</p>
                            @endforelse
                        </div>
                        <div class="tab-pane fade" id="tab-history">
                            <ul class="activity-timeline list-unstyled mb-0">
                                @forelse ($application->statusHistories as $entry)
                                    <li class="tl-item {{ $entry->to_status === 'approved' ? '' : 'muted-dot' }}">
                                        <div class="fw-semibold small">
                                            {{ ucfirst(str_replace('_', ' ', $entry->action)) }}:
                                            {{ str_replace('_', ' ', $entry->from_status) }} &rarr; {{ str_replace('_', ' ', $entry->to_status) }}
                                        </div>
                                        <div class="small text-secondary">
                                            {{ optional($entry->actor)->full_name }} &middot; {{ $entry->created_at->format('d/m/Y H:i') }}
                                        </div>
                                        @if ($entry->public_comment)
                                            <div class="small mt-1">{{ $entry->public_comment }}</div>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-secondary">No status changes yet.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @if ($application->certificate)
                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Certificate</h5>
                        <p class="small text-secondary mb-3">
                            This approval created certificate eligibility. Issue the certificate
                            to allocate its unique number and create version 1.
                        </p>
                        <a href="{{ route('certificates.show', $application->certificate) }}" class="btn btn-brand-green w-100 rounded-3 fw-semibold">
                            <i class="ri-verified-badge-line me-1"></i>
                            @if ($application->certificate->certificate_number)
                                Certificate {{ $application->certificate->certificate_number }}
                            @else
                                Issue certificate
                            @endif
                        </a>
                    </div>
                </div>
            @endif

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Application facts</h5>
                    <dl class="review-grid">
                        <div class="review-item"><dt>Type</dt><dd>{{ ucfirst($application->application_type) }}</dd></div>
                        <div class="review-item"><dt>Created by</dt><dd>{{ $application->creator->full_name }}</dd></div>
                        <div class="review-item"><dt>Submitted by</dt><dd>{{ optional($application->submitter)->full_name ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Route</dt><dd>{{ $application->routeTarget() }}</dd></div>
                        <div class="review-item"><dt>Due by</dt><dd>{{ optional($application->due_at)->format('d/m/Y') ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Declaration</dt><dd>{{ $application->declaration_version ? 'Captured (v'.$application->declaration_version.')' : 'Not yet' }}</dd></div>
                    </dl>
                </div>
            </div>

            @can('update', $application)
                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Actions</h5>
                        @if ($application->canBeSubmitted())
                            <a href="{{ route('applications.edit', $application) }}"
                               class="btn btn-primary-div text-white w-100 mb-2 rounded-3 fw-semibold">
                                <i class="ri-edit-line me-1"></i> Edit and submit
                            </a>
                        @else
                            <p class="small text-secondary mb-0">This application is no longer editable.</p>
                        @endif
                    </div>
                </div>
            @endcan

            @if ($canDecide)
                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Decision</h5>

                        @if ($application->created_by === auth()->id())
                            <div class="alert alert-warning d-flex align-items-start gap-2">
                                <span class="material-symbols-outlined">warning</span>
                                <div class="small">
                                    You created this application. Separation of duties means
                                    @if (auth()->user()->isSystemAdmin())
                                        approval requires an authorised override with a written reason.
                                    @else
                                        it cannot be approved by you; it will be decided by a System Admin.
                                    @endif
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('applications.decide', $application) }}" id="decision-form"
                              data-confirm="Confirm this decision. Decisions are immutable and recorded in the audit trail.">
                            @csrf
                            <div class="mb-3">
                                <label for="decision" class="form-label">Decision <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="decision" name="decision" required>
                                    <option value="">Select decision</option>
                                    <option value="approve">Approve</option>
                                    <option value="request_correction">Request correction</option>
                                    <option value="reject">Reject</option>
                                </select>
                            </div>

                            <div id="approve-checklist" class="d-none mb-3">
                                <label class="form-label fw-semibold">Approval checklist <span class="required-indicator">Required</span></label>
                                @php $items = [
                                    'Photograph is clear and belongs to the applicant',
                                    'NIN format and verification state meet policy',
                                    'Name and date of birth are consistent with evidence',
                                    'Selected LGA, ward and unit are valid',
                                    'Guardian details are complete',
                                    'Duplicate flags are resolved or accepted',
                                    'Applicant/operator declaration was captured',
                                    'I am not the creator of this application',
                                ]; @endphp
                                @foreach ($items as $index => $item)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="checklist[]" value="{{ $index + 1 }}" id="check-{{ $index }}">
                                        <label class="form-check-label small" for="check-{{ $index }}">{{ $item }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label for="reason_code" class="form-label">Reason code <span class="text-secondary">(reject only)</span></label>
                                <select class="form-select" id="reason_code" name="reason_code">
                                    <option value="">—</option>
                                    <option value="insufficient_evidence">Insufficient evidence</option>
                                    <option value="identity_mismatch">Identity mismatch</option>
                                    <option value="nin_issue">NIN issue</option>
                                    <option value="geography_invalid">Invalid geography</option>
                                    <option value="duplicate">Duplicate record</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="public_comment" class="form-label">Public comment <span class="text-secondary">(shown to the creator)</span></label>
                                <textarea class="form-control" id="public_comment" name="public_comment" rows="3" maxlength="2000"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="internal_comment" class="form-label">Internal note <span class="text-secondary">(not shown publicly)</span></label>
                                <textarea class="form-control" id="internal_comment" name="internal_comment" rows="2" maxlength="2000"></textarea>
                            </div>

                            <button class="btn btn-brand-green w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-how-to-vote-line me-1"></i> Record decision
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var decision = document.getElementById('decision');
        var checklist = document.getElementById('approve-checklist');
        decision.addEventListener('change', function () {
            checklist.classList.toggle('d-none', decision.value !== 'approve');
        });
    });
</script>
@endpush
