<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\IndigeneApplication;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', IndigeneApplication::class);

        $user = auth()->user();
        $lga = $user->activeLga();

        $query = IndigeneApplication::with(['profile.ward', 'profile.unit', 'profile.district', 'indigene', 'lga', 'creator'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id));

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('application_number', 'like', "%{$term}%")
                    ->orWhereHas('indigene', fn ($i) => $i
                        ->where('registry_number', 'like', "%{$term}%")
                        ->orWhere('nin_last4', $term)
                        ->orWhereHas('currentProfile', fn ($p) => $p
                            ->where('surname', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('application_type', $request->input('type'));
        }

        if ($request->filled('ward_id')) {
            $query->whereHas('profile', fn ($p) => $p->where('ward_id', $request->input('ward_id')));
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->input('created_by'));
        }

        if ($request->filled('duplicate')) {
            $query->whereHas('duplicateFlags', fn ($d) => $d->where('status', 'open'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->input('from').' 00:00:00', $request->input('to').' 23:59:59']);
        }

        $tab = $request->input('tab', 'all');

        if ($tab === 'awaiting-review') {
            $query->whereIn('status', [ApplicationStatus::PendingChairman, ApplicationStatus::PendingSystemAdmin])
                ->where('created_by', '!=', $user->id);
        } elseif ($tab === 'corrections') {
            $query->where('status', ApplicationStatus::ChangesRequested)->where('created_by', $user->id);
        }

        $applications = $query->orderByDesc('created_at')->paginate($request->input('per_page', 25))->withQueryString();

        return view('applications.index', compact('applications', 'tab'));
    }

    public function show(IndigeneApplication $application)
    {
        $this->authorize('view', $application);

        $application->load([
            'indigene.currentProfile.relations',
            'indigene.currentProfile.photoFile',
            'indigene.currentProfile.originLga.state',
            'indigene.currentProfile.ward',
            'indigene.currentProfile.unit',
            'indigene.currentProfile.district',
            'lga',
            'creator',
            'submitter',
            'decider',
            'statusHistories.actor',
            'documents.fileAsset',
            'duplicateFlags.candidate.currentProfile',
            'reviews.reviewer',
            'consentRecords',
            'certificate',
        ]);

        $canDecide = auth()->user()->can('decide', $application);

        return view('applications.show', compact('application', 'canDecide'));
    }

    public function destroy(IndigeneApplication $application)
    {
        $this->authorize('delete', $application);

        if ($application->status === \App\Enums\ApplicationStatus::Approved) {
            abort(409, 'An approved application cannot be deleted.');
        }

        app(\App\Services\AuditService::class)->record('application.deleted', IndigeneApplication::class, $application->id, [], [], 'high');

        \Illuminate\Support\Facades\DB::transaction(function () use ($application) {
            $application->statusHistories()->delete();
            $application->reviews()->delete();
            $application->duplicateFlags()->delete();
            $application->consentRecords()->delete();
            $application->documents()->delete();

            $profile = $application->profile;
            $indigene = $application->indigene;

            $application->delete();

            if ($profile) {
                $profile->relations()->delete();
                $profile->delete();
            }

            // Only remove the applicant when this was their last application;
            // other applications, certificates and consent records still reference it.
            if ($indigene && ! IndigeneApplication::where('indigene_id', $indigene->id)->exists()) {
                $indigene->delete();
            }
        });

        return redirect()->route('applications.index')->with('status', 'Application deleted.');
    }
}
