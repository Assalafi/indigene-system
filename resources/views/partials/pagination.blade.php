@php
    $paginator = $paginator ?? $applications ?? $indigenes ?? $certificates ?? $events ?? $logs
        ?? $users ?? $lgas ?? $flags ?? $batches ?? $requests ?? $holds ?? $exports ?? $reports
        ?? $wards ?? $districts ?? $units ?? $states ?? $notifications ?? null;
@endphp

@if ($paginator && $paginator->hasPages())
    {{ $paginator->withQueryString()->links('partials.bootstrap-pagination') }}
@endif
