<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CertificateVerificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCertificateVerificationController extends Controller
{
    public function __construct(private CertificateVerificationService $verification) {}

    public function create(): View
    {
        return view('public.verify');
    }

    public function store(Request $request): View
    {
        $request->validate([
            'certificate_number' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9\-]+$/'],
        ], [
            'certificate_number.regex' => 'Enter the certificate number exactly as printed, without spaces.',
        ]);

        if ($this->verification->assertRateLimited()) {
            abort(429, 'Too many verification attempts. Please try again in a few minutes.');
        }

        $this->verification->hitRateLimit();

        $result = $this->verification->verifyByNumber($request->input('certificate_number'));

        // FR-PUB-004 / SRD 17.2: generic invalid response that does not enable enumeration.
        return view('public.verify', [
            'result' => $result,
            'queriedNumber' => strtoupper(trim($request->input('certificate_number'))),
        ]);
    }

    public function show(string $token): View
    {
        if ($this->verification->assertRateLimited()) {
            abort(429, 'Too many verification attempts. Please try again in a few minutes.');
        }

        $this->verification->hitRateLimit();

        $result = $this->verification->verifyByToken($token);

        return view('public.token-result', ['result' => $result]);
    }
}
