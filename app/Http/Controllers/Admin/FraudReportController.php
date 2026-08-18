<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudReport;
use App\Services\AuditService;
use Illuminate\Http\Request;

class FraudReportController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('fraud.view') || auth()->user()->can('fraud.manage'), 403);

        $reports = FraudReport::with(['certificate.indigene.currentProfile', 'assignee'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.fraud-reports.index', compact('reports'));
    }

    public function show(FraudReport $report)
    {
        abort_unless(auth()->user()->can('fraud.view') || auth()->user()->can('fraud.manage'), 403);

        return view('admin.fraud-reports.show', compact('report'));
    }

    public function resolve(FraudReport $report, Request $request)
    {
        abort_unless(auth()->user()->can('fraud.manage'), 403);

        $data = $request->validate([
            'resolution' => ['required', 'string', 'max:4000'],
        ]);

        $report->status = 'resolved';
        $report->resolution = $data['resolution'];
        $report->resolved_at = now();
        $report->save();

        $this->audit->record('fraud.report_resolved', FraudReport::class, $report->id, [
            'status' => 'open',
        ], [
            'status' => 'resolved',
        ], 'medium');

        return redirect()->route('admin.fraud-reports.index')->with('status', 'Fraud report resolved.');
    }
}

