<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificatePrintEvent;
use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * SRD 26 - report catalogue and report experience.
 */
class ReportController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('report.view'), 403);

        $catalogue = [
            ['code' => 'registrations', 'name' => 'Registrations by period, status and geography', 'icon' => 'group_add'],
            ['code' => 'approved_indigenes', 'name' => 'Approved indigenes by demographic range', 'icon' => 'task_alt'],
            ['code' => 'turnaround', 'name' => 'Application turnaround and backlog', 'icon' => 'schedule'],
            ['code' => 'decisions', 'name' => 'Chairman/Admin decisions', 'icon' => 'how_to_vote'],
            ['code' => 'rejection_reasons', 'name' => 'Rejected/correction reasons', 'icon' => 'feedback'],
            ['code' => 'certificates', 'name' => 'Certificates issued and by status', 'icon' => 'verified'],
            ['code' => 'prints', 'name' => 'Print/reprint occurrences by user, LGA and reason', 'icon' => 'print'],
            ['code' => 'duplicates', 'name' => 'Duplicate flags and outcomes', 'icon' => 'content_copy'],
            ['code' => 'geography_completeness', 'name' => 'Geography completeness', 'icon' => 'map'],
            ['code' => 'staff_activity', 'name' => 'Staff activity and inactive accounts', 'icon' => 'manage_accounts'],
            ['code' => 'privacy_access', 'name' => 'Privacy access/export/document-download report', 'icon' => 'privacy_tip'],
        ];

        return view('reports.index', compact('catalogue'));
    }

    public function show(string $code, Request $request)
    {
        abort_unless(auth()->user()->can('report.view'), 403);

        $user = auth()->user();
        $lga = $user->activeLga();

        $result = match ($code) {
            'registrations' => $this->registrations($request, $user, $lga),
            'approved_indigenes' => $this->approvedIndigenes($request, $user, $lga),
            'turnaround' => $this->turnaround($request, $user, $lga),
            'decisions' => $this->decisions($request, $user, $lga),
            'rejection_reasons' => $this->rejectionReasons($request, $user, $lga),
            'certificates' => $this->certificates($request, $user, $lga),
            'prints' => $this->prints($request, $user, $lga),
            'duplicates' => $this->duplicates($request, $user, $lga),
            'geography_completeness' => $this->geographyCompleteness($request, $user, $lga),
            'staff_activity' => $this->staffActivity($request, $user, $lga),
            'privacy_access' => $this->privacyAccess($request, $user, $lga),
            default => abort(404),
        };

        return view('reports.show', array_merge([
            'code' => $code,
            'name' => collect($this->catalogueNames())->firstWhere('code', $code)['name'] ?? $code,
        ], $result));
    }

    private function catalogueNames(): array
    {
        return [
            ['code' => 'registrations', 'name' => 'Registrations'],
            ['code' => 'approved_indigenes', 'name' => 'Approved indigenes'],
            ['code' => 'turnaround', 'name' => 'Turnaround'],
            ['code' => 'decisions', 'name' => 'Decisions'],
            ['code' => 'rejection_reasons', 'name' => 'Rejection reasons'],
            ['code' => 'certificates', 'name' => 'Certificates'],
            ['code' => 'prints', 'name' => 'Prints'],
            ['code' => 'duplicates', 'name' => 'Duplicates'],
            ['code' => 'geography_completeness', 'name' => 'Geography completeness'],
            ['code' => 'staff_activity', 'name' => 'Staff activity'],
            ['code' => 'privacy_access', 'name' => 'Privacy access'],
        ];
    }

    private function registrations(Request $request, User $user, $lga): array
    {
        $rows = Indigene::query()
            ->with(['currentProfile', 'originLga.state'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('origin_lga_id', $lga->id))
            ->when($request->filled('from') && $request->filled('to'), fn ($q) => $q->whereBetween('created_at', [$request->input('from').' 00:00:00', $request->input('to').' 23:59:59']))
            ->when($request->filled('status'), fn ($q) => $q->where('lifecycle_status', $request->input('status')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Registry number', 'Name', 'LGA', 'Status', 'Registered']];
    }

    private function approvedIndigenes(Request $request, User $user, $lga): array
    {
        $rows = Indigene::query()
            ->with(['currentProfile', 'originLga.state'])
            ->whereNotNull('approved_at')
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('origin_lga_id', $lga->id))
            ->when($request->filled('from') && $request->filled('to'), fn ($q) => $q->whereBetween('approved_at', [$request->input('from').' 00:00:00', $request->input('to').' 23:59:59']))
            ->when($request->filled('sex'), fn ($q) => $q->whereHas('currentProfile', fn ($p) => $p->where('sex', $request->input('sex'))))
            ->orderByDesc('approved_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Registry number', 'Name', 'Sex', 'LGA', 'Approved']];
    }

    private function turnaround(Request $request, User $user, $lga): array
    {
        $rows = IndigeneApplication::query()
            ->with(['indigene.currentProfile', 'lga'])
            ->whereNotNull('decided_at')
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('decided_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Application', 'Name', 'LGA', 'Submitted', 'Decided', 'Status']];
    }

    private function decisions(Request $request, User $user, $lga): array
    {
        $rows = \App\Models\ApplicationReview::with(['application.indigene.currentProfile', 'reviewer'])
            ->whereIn('outcome', ['approved', 'rejected', 'changes_requested'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->whereHas('application', fn ($a) => $a->where('lga_id', $lga->id)))
            ->orderByDesc('reviewed_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Application', 'Name', 'Reviewer', 'Outcome', 'Reviewed']];
    }

    private function rejectionReasons(Request $request, User $user, $lga): array
    {
        $rows = IndigeneApplication::with(['indigene.currentProfile', 'lga'])
            ->whereIn('status', ['rejected', 'changes_requested'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id))
            ->orderByDesc('decided_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Application', 'Name', 'LGA', 'Reason code', 'Status', 'Decided']];
    }

    private function certificates(Request $request, User $user, $lga): array
    {
        $rows = Certificate::with(['indigene.currentProfile', 'lga.state'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from') && $request->filled('to'), fn ($q) => $q->whereBetween('issued_at', [$request->input('from').' 00:00:00', $request->input('to').' 23:59:59']))
            ->orderByDesc('issued_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Certificate number', 'Holder', 'LGA', 'Status', 'Issued', 'Prints']];
    }

    private function prints(Request $request, User $user, $lga): array
    {
        $rows = CertificatePrintEvent::with(['certificate.indigene.currentProfile', 'requester'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('requester_lga_id', $lga->id))
            ->when($request->filled('user_id'), fn ($q) => $q->where('requested_by', $request->input('user_id')))
            ->when($request->filled('reason_code'), fn ($q) => $q->where('reason_code', $request->input('reason_code')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Certificate', 'Holder', 'Copy', 'Reason', 'Requested by', 'Date']];
    }

    private function duplicates(Request $request, User $user, $lga): array
    {
        $rows = \App\Models\DuplicateFlag::with(['application.indigene.currentProfile', 'candidate.currentProfile'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->whereHas('application', fn ($a) => $a->where('lga_id', $lga->id)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Application', 'Match type', 'Score', 'Status', 'Detected']];
    }

    private function geographyCompleteness(Request $request, User $user, $lga): array
    {
        $rows = \App\Models\Lga::with('state')
            ->withCount(['wards' => fn ($q) => $q->where('status', 'active'), 'units' => fn ($q) => $q->where('status', 'active'), 'districts' => fn ($q) => $q->where('status', 'active')])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('id', $lga->id))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['LGA', 'State', 'Wards', 'Units', 'Districts']];
    }

    private function staffActivity(Request $request, User $user, $lga): array
    {
        $rows = User::with(['roles', 'activeAssignments.lga'])
            ->withCount(['notifications'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->whereHas('assignments', fn ($a) => $a->where('lga_id', $lga->id)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('full_name')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Name', 'Email', 'Role', 'Status', 'Last login']];
    }

    private function privacyAccess(Request $request, User $user, $lga): array
    {
        $rows = \App\Models\SensitiveDataAccessLog::with('actor')
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('actor_id', $user->id))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return ['rows' => $rows, 'columns' => ['Actor', 'Subject type', 'Category', 'Action', 'Purpose', 'Date']];
    }
}

