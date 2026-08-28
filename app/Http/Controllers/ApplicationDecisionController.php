<?php

namespace App\Http\Controllers;

use App\Models\IndigeneApplication;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\Request;

/**
 * SRD 22 - approval queues and decisions.
 */
class ApplicationDecisionController extends Controller
{
    public function __construct(private ApplicationWorkflowService $workflow) {}

    public function queue(Request $request)
    {
        $user = auth()->user();
        $lga = $user->activeLga();

        $query = IndigeneApplication::with(['indigene.currentProfile', 'lga', 'creator'])
            ->whereIn('status', ['pending_chairman', 'pending_system_admin'])
            ->where('created_by', '!=', $user->id);

        if (! $user->isSystemAdmin()) {
            $query->where('lga_id', $lga->id);
        }

        $tab = $request->input('tab', 'all');

        if ($tab === 'chairman-created' && $user->isSystemAdmin()) {
            $query->where('approval_route', 'admin_only');
        } elseif ($tab === 'overdue') {
            $query->whereNotNull('due_at')->where('due_at', '<', now());
        } elseif ($tab === 'flagged') {
            $query->whereHas('duplicateFlags', fn ($d) => $d->where('status', 'open'));
        } elseif ($tab === 'escalated') {
            $query->where('priority', 'high');
        }

        // Bulk approval is prohibited (SRD 22.1); rows are ordered by submission age.
        $applications = $query->orderBy('submitted_at')->paginate(25)->withQueryString();

        return view('approvals.queue', compact('applications', 'tab'));
    }

    public function decide(IndigeneApplication $application, Request $request)
    {
        $this->authorize('decide', $application);

        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject,request_correction'],
            'public_comment' => ['nullable', 'string', 'max:2000'],
            'internal_comment' => ['nullable', 'string', 'max:2000'],
            'reason_code' => ['nullable', 'string', 'max:60'],
            'corrections' => ['nullable', 'array'],
            'corrections.*' => ['string', 'max:200'],
        ]);

        $user = auth()->user();
        $isOverride = $user->isSystemAdmin() && $application->created_by === $user->id;

        match ($data['decision']) {
            'approve' => $this->workflow->approve(
                $application,
                $user,
                [],
                $data['public_comment'] ?? null,
                $data['internal_comment'] ?? null,
                $isOverride
            ),
            'reject' => $this->workflow->reject(
                $application,
                $user,
                $data['reason_code'] ?? 'other',
                $data['public_comment'] ?? 'Application rejected.',
                $data['internal_comment'] ?? null
            ),
            'request_correction' => $this->workflow->requestCorrection(
                $application,
                $user,
                $data['corrections'] ?? [],
                $data['public_comment'] ?? 'Corrections required.',
                $data['internal_comment'] ?? null
            ),
        };

        return redirect()->route('applications.show', $application)
            ->with('status', 'Decision recorded for '.$application->application_number.'.');
    }
}
