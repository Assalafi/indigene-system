<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 | NIMCS</title>
    @include('partials.styles')
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
        <div class="text-center" style="max-width: 560px;">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10"
                 style="width:110px;height:110px;">
                <span class="material-symbols-outlined text-info" style="font-size:56px;">search_off</span>
            </div>
            <h1 class="display-3 fw-bold text-brand-navy">404</h1>
            <h4 class="fw-semibold mb-2">The page or record could not be found</h4>
            <p class="text-secondary mb-4">
                The record may not exist, or it may be outside your authorised LGA scope.
                For security, we do not reveal which of the two applies.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-primary-div text-white rounded-3 fw-semibold px-4">
                    Back to dashboard
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-3 fw-semibold">Public home</a>
            </div>
        </div>
    </div>
</body>
</html>
