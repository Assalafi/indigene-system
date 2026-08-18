@extends('layouts.app')

@section('title', 'Print Copy '.$event->print_number)
@section('page-title', 'Print Copy Generated')
@section('page-subtitle', $event->copyLabel().' &middot; '.$certificate->certificate_number)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('certificates.index') }}" class="text-decoration-none"><span class="text-secondary fw-medium">Certificates</span></a></li>
    <li class="breadcrumb-item"><a href="{{ route('certificates.show', $certificate) }}" class="text-decoration-none"><span class="text-secondary fw-medium">{{ $certificate->certificate_number }}</span></a></li>
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Print copy {{ $event->print_number }}</span></li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4 text-center">
                    <span class="material-symbols-outlined text-brand-green" style="font-size:56px;">task_alt</span>
                    <h4 class="mt-2 mb-1">{{ $event->copyLabel() }}</h4>
                    <p class="text-secondary">
                        Print event {{ $event->id }} recorded at {{ $event->created_at->format('d/m/Y H:i') }}.
                        This occurrence is now part of the immutable print history.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <a href="{{ route('certificates.download', ['certificate' => $certificate, 'event' => $event]) }}"
                           class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold">
                            <i class="ri-download-2-line me-1"></i> Download PDF copy
                        </a>
                        <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-outline-secondary rounded-3 fw-semibold">
                            Back to certificate
                        </a>
                    </div>

                    <p class="small text-secondary mt-4 mb-0">
                        Note: the system counts server-authorised printable copies; a browser cannot prove
                        that paper physically left a printer. Reprints require a reason and are numbered
                        sequentially.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
