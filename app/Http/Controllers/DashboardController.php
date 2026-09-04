<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificatePrintEvent;
use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('lga_chairman')) {
            return $this->chairmanDashboard($user);
        }

        return $this->officerDashboard($user);
    }

    private function adminDashboard()
    {
        $monthStart = now()->startOfMonth();

        $stats = [
            'total_indigenes' => Indigene::count(),
            'pending_approvals' => IndigeneApplication::whereIn('status', ['pending_chairman', 'pending_system_admin'])->count(),
            'approved_this_month' => IndigeneApplication::where('status', 'approved')->where('decided_at', '>=', $monthStart)->count(),
            'certificates_this_month' => Certificate::whereNotNull('issued_at')->where('issued_at', '>=', $monthStart)->count(),
            'prints_this_month' => CertificatePrintEvent::where('created_at', '>=', $monthStart)->count(),
            'active_lgas' => \App\Models\Lga::where('status', 'active')->count(),
            'active_users' => User::where('status', 'active')->count(),
        ];

        $trend = IndigeneApplication::where('created_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $byState = Indigene::join('states', 'indigenes.origin_state_id', '=', 'states.id')
            ->selectRaw('states.name, COUNT(*) as total')
            ->groupBy('states.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $waitingLongest = IndigeneApplication::with(['indigene.currentProfile', 'lga'])
            ->whereIn('status', ['pending_chairman', 'pending_system_admin'])
            ->orderBy('submitted_at')
            ->limit(10)
            ->get();

        $recentActivity = \App\Models\AuditLog::whereIn('action', [
            'application.approved', 'application.rejected', 'certificate.revoked', 'certificate.reinstated',
        ])->latest('occurred_at')->limit(10)->get();

        $openFlags = \App\Models\DuplicateFlag::where('status', 'open')->count();
        $openFraud = \App\Models\FraudReport::where('status', 'open')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        return view('dashboard.admin', compact('stats', 'trend', 'byState', 'waitingLongest', 'recentActivity', 'openFlags', 'openFraud', 'failedJobs'));
    }

    private function chairmanDashboard(User $user)
    {
        $lga = $user->activeLga();
        abort_if(! $lga, 403, 'No active LGA assignment.');

        $monthStart = now()->startOfMonth();

        $awaitingReview = IndigeneApplication::where('lga_id', $lga->id)
            ->where('status', 'pending_chairman')
            ->orderBy('submitted_at')
            ->get();

        $stats = [
            'awaiting_review' => $awaitingReview->count(),
            'oldest_pending_days' => $awaitingReview->min('submitted_at') ? now()->diffInDays($awaitingReview->min('submitted_at')) : 0,
            'approved_this_month' => IndigeneApplication::where('lga_id', $lga->id)->where('status', 'approved')->where('decided_at', '>=', $monthStart)->count(),
            'rejected_this_month' => IndigeneApplication::where('lga_id', $lga->id)->where('status', 'rejected')->where('decided_at', '>=', $monthStart)->count(),
            'certificates_this_month' => Certificate::where('lga_id', $lga->id)->where('issued_at', '>=', $monthStart)->count(),
            'reprints_this_month' => CertificatePrintEvent::where('requester_lga_id', $lga->id)->where('copy_type', 'reprint')->where('created_at', '>=', $monthStart)->count(),
            'indigenes_total' => Indigene::where('origin_lga_id', $lga->id)->count(),
            'wards_total' => \App\Models\Ward::where('lga_id', $lga->id)->where('status', 'active')->count(),
            'units_total' => \App\Models\Unit::where('lga_id', $lga->id)->where('status', 'active')->count(),
        ];

        $byWard = Indigene::join('indigene_profiles', 'indigenes.current_profile_id', '=', 'indigene_profiles.id')
            ->join('wards', 'indigene_profiles.ward_id', '=', 'wards.id')
            ->where('indigenes.origin_lga_id', $lga->id)
            ->selectRaw('wards.name, COUNT(*) as total')
            ->groupBy('wards.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $incompleteGeography = ($stats['wards_total'] === 0 || $stats['units_total'] === 0);

        $recentActions = \App\Models\AuditLog::where('actor_lga_id', $lga->id)
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        return view('dashboard.chairman', compact('stats', 'awaitingReview', 'byWard', 'incompleteGeography', 'recentActions', 'lga'));
    }

    private function officerDashboard(User $user)
    {
        $lga = $user->activeLga();
        abort_if(! $lga, 403, 'No active LGA assignment.');

        $stats = [
            'submitted_pending' => IndigeneApplication::where('lga_id', $lga->id)->whereIn('status', ['pending_chairman', 'pending_system_admin'])->count(),
            'correction_required' => IndigeneApplication::where('created_by', $user->id)->where('status', 'changes_requested')->count(),
            'approved_ready_to_print' => IndigeneApplication::where('lga_id', $lga->id)->where('status', 'approved')->count(),
            'registered_total' => Indigene::where('origin_lga_id', $lga->id)->count(),
        ];

        $recent = IndigeneApplication::with(['indigene.currentProfile'])
            ->where('created_by', $user->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $avgApprovalDays = IndigeneApplication::where('lga_id', $lga->id)
            ->where('status', 'approved')
            ->whereNotNull('submitted_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, decided_at)) as avg_hours')
            ->value('avg_hours');

        $avgApprovalDays = $avgApprovalDays ? round($avgApprovalDays / 24, 1) : null;

        return view('dashboard.officer', compact('stats', 'recent', 'avgApprovalDays', 'lga'));
    }
}
