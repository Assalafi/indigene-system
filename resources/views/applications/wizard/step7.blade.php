@extends('layouts.app')

@section('title', 'Step 7: Supporting Documents')
@section('page-title', 'New Application - Step 7 of 8')
@section('page-subtitle', 'Upload supporting evidence')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('partials.wizard-progress')
            @include('partials.flash-messages')

            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="form-section-title">
                        <span class="material-symbols-outlined">folder_shared</span>
                        Supporting documents
                    </h5>
                    <p class="text-secondary small">
                        PDF, JPEG, PNG or WebP, up to 10 MB each. Files are MIME-inspected and stored
                        privately. At least one document is required.
                    </p>

                    @if ($application->documents->isNotEmpty())
                        <h6 class="fw-semibold mb-2">Uploaded so far</h6>
                        <ul class="list-unstyled mb-4">
                            @foreach ($application->documents as $doc)
                                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <i class="ri-file-line me-1"></i>
                                        <span class="fw-semibold">{{ $doc->documentTypeLabel() }}</span>
                                        <span class="small text-secondary ms-2">{{ $doc->fileAsset->original_name }}
                                            ({{ number_format($doc->fileAsset->size_bytes / 1024, 0) }} KB)</span>
                                    </div>
                                    <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('applications.wizard.store', ['application' => $application, 'step' => 7]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="document_types_0" class="form-label">Document type <span class="required-indicator">Required</span></label>
                                <select class="form-select" id="document_types_0" name="document_types[]">
                                    @foreach ($documentTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="documents_0" class="form-label">File <span class="required-indicator">Required</span></label>
                                <input class="form-control" id="documents_0" name="documents[]" type="file"
                                       accept="application/pdf,image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <select class="form-select" name="document_types[]">
                                    @foreach ($documentTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" name="documents[]" type="file"
                                       accept="application/pdf,image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <select class="form-select" name="document_types[]">
                                    @foreach ($documentTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" name="documents[]" type="file"
                                       accept="application/pdf,image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        @error('documents')<span class="text-danger small">{{ $message }}</span>@enderror

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('applications.wizard', ['application' => $application, 'step' => 6]) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                                Continue to review <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
