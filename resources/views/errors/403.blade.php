<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 | NIMCS</title>
    @include('partials.styles')
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
        <div class="text-center" style="max-width: 560px;">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                 style="width:110px;height:110px;">
                <span class="material-symbols-outlined text-danger" style="font-size:56px;">gpp_maybe</span>
            </div>
            <h1 class="display-3 fw-bold text-brand-navy">403</h1>
            <h4 class="fw-semibold mb-2">You are not authorised to view this page</h4>
            <p class="text-secondary mb-4">
                This action may be outside your role, or the record belongs to a different LGA scope.
                Unauthorised access attempts are recorded.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-primary-div text-white rounded-3 fw-semibold px-4">
                    Back to dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary rounded-3 fw-semibold" type="submit">Sign out</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
