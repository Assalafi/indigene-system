@extends('layouts.public')

@section('title', 'Privacy Notice')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 860px;">
            <h1 style="color:#0b1f3a;">Privacy Notice</h1>
            <p class="text-secondary">Effective date: 18 August 2026 &middot; Version 1.0</p>

            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body p-4 p-md-5">
                    <h5>1. Who we are</h5>
                    <p>
                        The Nigerian Indigene Management and Certification System (NIMCS) is operated for
                        participating government authorities by Haigha Tech ("we", "our"). The participating
                        government authority is the data controller; Haigha Tech acts as processor/service
                        provider, except for data Haigha processes independently for its own legitimate
                        administrative purposes.
                    </p>

                    <h5>2. What we collect</h5>
                    <p>When an indigene is registered, we collect:</p>
                    <ul>
                        <li>Identity data: NIN, names, date of birth, sex, marital status, nationality and photograph;</li>
                        <li>Place of origin: state, LGA, district (optional), ward, village/community unit and hometown;</li>
                        <li>Contact and residence details;</li>
                        <li>Family information: parents, guardian (where required) and next of kin; and</li>
                        <li>Supporting documents used to establish indigene status.</li>
                    </ul>

                    <h5>3. Why we process it</h5>
                    <p>
                        Personal data is processed to operate the official indigene register, route applications
                        through LGA approval, issue verifiable indigene certificates and maintain the audit trail
                        required by law. The lawful basis is the performance of a task carried out in the public
                        interest or in the exercise of official authority, with consent or other lawful authority
                        captured where applicable.
                    </p>

                    <h5>4. How we protect it</h5>
                    <ul>
                        <li>NIN is encrypted and masked by default; it never appears in URLs, QR codes, exports, notifications or logs;</li>
                        <li>Access is role-based and restricted to each user's assigned LGA;</li>
                        <li>Sensitive data access requires permission and a reason, and is recorded;</li>
                        <li>Public verification shows only the minimum attributes needed to validate a certificate.</li>
                    </ul>

                    <h5>5. Your rights</h5>
                    <p>
                        Under the Nigeria Data Protection Act 2023 you may request access to, rectification of,
                        restriction of or objection to processing of your personal data, and data portability or
                        erasure where applicable. Submit a request through
                        <a href="{{ route('login') }}">the staff portal</a> or via the support channel below.
                    </p>

                    <h5>6. Retention</h5>
                    <p>
                        Records are retained for the period justified by law and the official function of the
                        register, after which they are safely disposed of in line with the documented retention
                        schedule, unless a legal hold applies.
                    </p>

                    <h5>7. Contact</h5>
                    <p>
                        Data Protection Officer: <a href="mailto:dpo@haighatech.com">dpo@haighatech.com</a><br>
                        Support: <a href="{{ route('support') }}">support page</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection

