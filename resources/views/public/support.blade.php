@extends('layouts.public')

@section('title', 'Support')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 720px;">
            <div class="section-heading">
                <h2 style="color:#0b1f3a;">Support</h2>
                <p style="color:#66746e;">Staff support is provided by Haigha Tech for participating authorities.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h5>How to get help</h5>
                    <ul>
                        <li>Staff users: use the in-portal Help Centre after signing in.</li>
                        <li>Email: <a href="mailto:support@haighatech.com">support@haighatech.com</a></li>
                        <li>Phone: the support line published by your issuing authority.</li>
                    </ul>

                    <div class="alert alert-warning d-flex align-items-start gap-2 mt-4">
                        <span class="material-symbols-outlined">shield_lock</span>
                        <div>
                            <strong>Protect your NIN.</strong>
                            Never paste your full NIN into a support message or email. Staff can locate records using
                            your registry or certificate number.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

