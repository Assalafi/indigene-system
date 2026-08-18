@extends('layouts.public')

@section('title', 'Accessibility Statement')

@section('content')
    <section class="py-5" style="padding-top: 11.5rem;">
        <div class="container" style="max-width: 860px;">
            <h1 style="color:#0b1f3a;">Accessibility Statement</h1>
            <p class="text-secondary">Effective date: 18 August 2026</p>

            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body p-4 p-md-5">
                    <p>
                        NIMCS is built to meet WCAG 2.2 AA for public and core staff flows. Conformance features include:
                    </p>
                    <ul>
                        <li>Full keyboard operation with a visible focus indicator;</li>
                        <li>Programmatic labels on every input, with error summaries that link to the invalid field;</li>
                        <li>Minimum 4.5:1 contrast for normal text and a 44&times;44 px minimum touch target;</li>
                        <li>Status information communicated with text and icons, never colour alone;</li>
                        <li>Responsive layouts from 360 px width upward with no workflow that depends on hover or drag-only interaction;</li>
                        <li>Screen-reader semantics for tables, dialogs and status announcements.</li>
                    </ul>
                    <p>
                        If you encounter a barrier, contact
                        <a href="mailto:support@haighatech.com">support@haighatech.com</a> or use the
                        <a href="{{ route('support') }}">support page</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection

