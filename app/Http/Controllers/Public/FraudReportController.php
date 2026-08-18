<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FraudReport;
use App\Services\IndigeneProfileVersionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FraudReportController extends Controller
{
    public function __construct(private IndigeneProfileVersionService $references) {}

    public function create(): View
    {
        return view('public.fraud-report');
    }

    public function store(Request $request)
    {
        $request->validate([
            'certificate_number' => ['nullable', 'string', 'max:80'],
            'reporter_name' => ['nullable', 'string', 'max:180'],
            'reporter_contact' => ['nullable', 'string', 'max:180'],
            'report_text' => ['required', 'string', 'max:4000'],
        ]);

        $fraudReport = FraudReport::create([
            'reference_number' => $this->references->fraudReference(),
            'reporter_name_ciphertext' => $request->filled('reporter_name') ? encrypt($request->input('reporter_name')) : null,
            'reporter_contact_ciphertext' => $request->filled('reporter_contact') ? encrypt($request->input('reporter_contact')) : null,
            'report_text' => $request->input('report_text'),
            'status' => 'open',
        ]);

        if ($request->filled('certificate_number')) {
            $certificate = \App\Models\Certificate::where('certificate_number', strtoupper(trim($request->input('certificate_number'))))->first();
            $fraudReport->certificate_id = $certificate?->id;
            $fraudReport->save();
        }

        return redirect()->route('fraud-reports.create')->with('status', 'Your report has been received with reference '.$fraudReport->reference_number.'. Our team will review it.');
    }
}
