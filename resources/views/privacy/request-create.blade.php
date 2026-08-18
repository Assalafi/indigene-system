@extends('layouts.app')

@section('title', 'New Privacy Request')
@section('page-title', 'New Privacy Request')
@section('page-subtitle', 'Recorded with a reference number and a 30-day response window.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('privacy.requests.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Privacy Requests</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">New</span></li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    @include('partials.flash-messages')

                    <form method="POST" action="{{ route('privacy.requests.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="request_type" class="form-label">Request type <span class="required-indicator">Required</span></label>
                            <select class="form-select" id="request_type" name="request_type" required>
                                @foreach (['access', 'rectification', 'objection', 'restriction', 'portability', 'erasure'] as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="indigene_id" class="form-label">Indigene ID <span class="text-secondary">(optional, UUID)</span></label>
                            <input class="form-control" id="indigene_id" name="indigene_id" type="text" maxlength="40"
                                   placeholder="Registry record UUID if known">
                        </div>
                        <div class="mb-3">
                            <label for="requester_identity" class="form-label">Requester identity <span class="required-indicator">Required</span></label>
                            <textarea class="form-control" id="requester_identity" name="requester_identity" rows="2" maxlength="1000" required
                                      placeholder="Who is requesting and how identity was verified. Stored encrypted.">{{ old('requester_identity') }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label for="requester_note" class="form-label">Request details <span class="required-indicator">Required</span></label>
                            <textarea class="form-control" id="requester_note" name="requester_note" rows="5" maxlength="4000" required
                                      placeholder="What the data subject is asking for&hellip;">{{ old('requester_note') }}</textarea>
                        </div>
                        <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                            <i class="ri-save-line me-1"></i> Record request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
