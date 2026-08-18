<?php

namespace App\Http\Controllers;

use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\IndigeneProfile;
use App\Services\AuditService;
use App\Services\IndigeneProfileVersionService;
use App\Services\NinProtectionService;
use Illuminate\Http\Request;

class IndigeneController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Indigene::class);

        $user = auth()->user();
        $lga = $user->activeLga();

        $query = Indigene::with(['currentProfile', 'originLga', 'originState', 'certificates'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('origin_lga_id', $lga->id));

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('registry_number', 'like', "%{$term}%")
                    ->orWhere('nin_last4', $term)
                    ->orWhereHas('currentProfile', fn ($p) => $p
                        ->where('surname', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->input('status'));
        }

        if ($request->filled('ward_id')) {
            $query->whereHas('currentProfile', fn ($p) => $p->where('ward_id', $request->input('ward_id')));
        }

        $indigenes = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25))->withQueryString();

        return view('indigenes.index', compact('indigenes'));
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Indigene::class);

        $results = collect();

        if ($request->filled('q')) {
            $term = $request->input('q');
            $user = auth()->user();
            $lga = $user->activeLga();

            $results = Indigene::with(['currentProfile', 'originLga', 'certificates'])
                ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('origin_lga_id', $lga->id))
                ->where(function ($q) use ($term) {
                    $q->where('registry_number', 'like', "%{$term}%")
                        ->orWhere('nin_last4', $term)
                        ->orWhereHas('currentProfile', fn ($p) => $p
                            ->where('surname', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                })
                ->limit(50)
                ->get();
        }

        return view('indigenes.search', compact('results'));
    }

    public function show(Indigene $indigene)
    {
        $this->authorize('view', $indigene);

        $indigene->load([
            'currentProfile.relations',
            'currentProfile.photoFile',
            'currentProfile.originState',
            'currentProfile.originLga',
            'currentProfile.ward',
            'currentProfile.unit',
            'currentProfile.district',
            'currentProfile.residenceState',
            'currentProfile.residenceLga',
            'originLga',
            'originState',
            'certificates.statusEvents.actor',
            'certificates.printEvents.requester',
            'certificates.versions',
            'applications.statusHistories',
            'applications.documents.fileAsset',
            'applications.duplicateFlags',
        ]);

        $accessLogs = \App\Models\SensitiveDataAccessLog::where('subject_id', $indigene->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('indigenes.show', compact('indigene', 'accessLogs'));
    }

    public function startAmendment(Indigene $indigene, IndigeneProfileVersionService $profiles)
    {
        $this->authorize('amend', $indigene);

        $profile = $profiles->copyCurrentProfileForAmendment($indigene);

        $application = IndigeneApplication::create([
            'application_number' => $profiles->applicationNumber(),
            'indigene_id' => $indigene->id,
            'profile_id' => $profile->id,
            'lga_id' => $indigene->origin_lga_id,
            'application_type' => 'amendment',
            'status' => 'draft',
            'approval_route' => auth()->user()->hasRole('lga_chairman') ? 'admin_only' : 'chairman_or_admin',
            'created_by' => auth()->id(),
            'last_saved_step' => 3,
        ]);

        app(AuditService::class)->record('indigene.amendment_started', Indigene::class, $indigene->id, [], [
            'application_id' => $application->id,
            'profile_version' => $profile->version_no,
        ], 'medium');

        return redirect()->route('applications.wizard', ['application' => $application, 'step' => 3])
            ->with('info', 'Amendment started. The active certificate remains based on the previous approved version until this amendment is approved.');
    }

    public function revealNin(Indigene $indigene, Request $request, NinProtectionService $nin, AuditService $audit)
    {
        $this->authorize('revealNin', $indigene);

        $request->validate([
            'purpose' => ['required', 'string', 'max:1000'],
        ], [
            'purpose.required' => 'A reason is required to reveal a full NIN.',
        ]);

        $revealed = $nin->reveal($indigene->nin_ciphertext);

        $audit->recordSensitiveAccess(
            Indigene::class,
            $indigene->id,
            'nin',
            'reveal',
            $request->input('purpose')
        );

        $audit->record('indigene.nin_revealed', Indigene::class, $indigene->id, [], [], 'high');

        return back()->with('revealed_nin', $revealed)->with('status', 'Full NIN revealed. This access has been recorded.');
    }
}
