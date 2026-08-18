@extends('layouts.public')

@section('title', 'Terms of Use')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 860px;">
            <h1 style="color:#0b1f3a;">Terms of Use</h1>
            <p class="text-secondary">Effective date: 18 August 2026 &middot; Version 1.0</p>

            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body p-4 p-md-5">
                    <h5>1. The service</h5>
                    <p>
                        This platform supports the registration of Nigerian indigenes, Local Government Area
                        approval workflows, issuance of indigene certificates and public certificate verification.
                    </p>

                    <h5>2. Public verification</h5>
                    <p>
                        The verification service is provided free of charge and returns only the minimum public
                        information required to validate a certificate. A valid result confirms only that the
                        certificate was issued by the stated authority through the approved workflow.
                    </p>

                    <h5>3. Staff access</h5>
                    <p>
                        Staff access is restricted to authorised personnel. Each user is bound by an acceptable-use
                        policy and may only access records within their assigned LGA and role. Every action is
                        recorded in the audit trail.
                    </p>

                    <h5>4. Acceptable use</h5>
                    <p>
                        You must not attempt to enumerate records, probe the system, or access data beyond your
                        authorised scope. Abuse, including automated scraping or repeated invalid lookups, may be
                        rate-limited or reported to the relevant authority.
                    </p>

                    <h5>5. Accuracy</h5>
                    <p>
                        Information provided during registration must be accurate. Material changes to identity or
                        place of origin after approval require a formal amendment application.
                    </p>

                    <h5>6. Liability</h5>
                    <p>
                        The issuing government authority remains the authority for the accuracy of indigene
                        determinations. Haigha Tech provides and operates the technology service and is not the
                        issuing authority.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection

