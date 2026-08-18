@extends('layouts.app')

@section('title', $privacyRequest->reference_number)
@section('page-title', $privacyRequest->reference_number)
@section('page-subtitle', $privacyRequest->requestTypeLabel().' request &middot; '.ucfirst($privacyRequest->status))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('privacy.requests.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Privacy Requests</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">{{ $privacyRequest->reference_number }}</span></li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Request</h5>
                    <dl class="review-grid">
                        <div class="review-item"><dt>Reference</dt><dd>{{ $privacyRequest->reference_number }}</dd></div>
                        <div class="review-item"><dt>Type</dt><dd>{{ $privacyRequest->requestTypeLabel() }}</dd></div>
                        <div class="review-item"><dt>Channel</dt><dd>{{ ucfirst($privacyRequest->channel) }}</dd></div>
                        <div class="review-item"><dt>Received</dt><dd>{{ optional($privacyRequest->received_at)->format('d/m/Y H:i') ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Due</dt><dd>{{ optional($privacyRequest->due_at)->format('d/m/Y') ?? '—' }}</dd></div>
                        <div class="review-item"><dt>Verification</dt><dd>{{ str_replace('_', ' ', ucfirst($privacyRequest->verification_status)) }}</dd></div>
                        <div class="review-item"><dt>Indigene</dt>
                            <dd>
                                @if ($privacyRequest->indigene)
                                    <a href="{{ route('indigenes.show', $privacyRequest->indigene) }}" class="fw-semibold">{{ $privacyRequest->indigene->fullName() }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="review-item"><dt>Requester identity</dt>
                            <dd>
                                @php
                                    try { $identity = $privacyRequest->requester_identity_ciphertext ? decrypt($privacyRequest->requester_identity_ciphertext) : null; }
                                    catch (\Throwable) { $identity = null; }
                                @endphp
                                {{ $identity ?? '—' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($privacyRequest->status === 'completed')
                        <div class="alert alert-success mt-3">
                            <div class="small text-secondary">Completed {{ optional($privacyRequest->completed_at)->format('d/m/Y H:i') }}</div>
                            <p class="mb-0 mt-1">{{ $privacyRequest->decision }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            @can('decide', $privacyRequest)
                <div class="card bg-white border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Record decision</h5>
                        <form method="POST" action="{{ route('privacy.requests.decide', $privacyRequest) }}"
                              data-confirm="Record this privacy decision? It is appended to the case audit trail.">
                            @csrf
                            <div class="mb-2">
                                <label for="verification_status" class="form-label small">Identity verification</label>
                                <select class="form-select form-select-sm" id="verification_status" name="verification_status" required>
                                    <option value="unverified">Unverified</option>
                                    <option value="verified">Verified</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label for="assigned_to" class="form-label small">Assign DPO</label>
                                <select class="form-select form-select-sm" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    @foreach ($dpos as $dpo)
                                        <option value="{{ $dpo->id }}" @selected($privacyRequest->assigned_to === $dpo->id)>{{ $dpo->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label for="decision" class="form-label small">Decision</label>
                                <textarea class="form-control form-control-sm" id="decision" name="decision" rows="4" maxlength="4000" required>{{ $privacyRequest->decision }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="lawful_exception" class="form-label small">Lawful exception <span class="text-secondary">(optional)</span></label>
                                <input class="form-control form-control-sm" id="lawful_exception" name="lawful_exception" type="text" maxlength="2000"
                                       value="{{ $privacyRequest->lawful_exception }}">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="complete" name="complete" value="1">
                                <label class="form-check-label small" for="complete">Complete the case</label>
                            </div>
                            <button class="btn btn-primary-div text-white w-100 rounded-3 fw-semibold" type="submit">
                                <i class="ri-gavel-line me-1"></i> Record decision
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
