@extends('layouts.public')

@section('title', 'System Status')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 720px;">
            <div class="section-heading">
                <h2 style="color:#0b1f3a;">System Status</h2>
                <p style="color:#66746e;">Current operational state of the NIMCS platform.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span><i class="ri-global-line me-2"></i> Public verification</span>
                            <span class="status-badge status-active"><span class="material-symbols-outlined">verified</span>Operational</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span><i class="ri-login-box-line me-2"></i> Staff portal</span>
                            <span class="status-badge status-active"><span class="material-symbols-outlined">verified</span>Operational</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2">
                            <span><i class="ri-fingerprint-line me-2"></i> NIN verification (NINAuth)</span>
                            <span class="status-badge status-pending_chairman"><span class="material-symbols-outlined">hourglass_top</span>Integration pending</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection

