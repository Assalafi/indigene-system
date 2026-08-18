<?php

namespace App\Http\Controllers;

use App\Models\DuplicateFlag;
use App\Models\IndigeneApplication;
use Illuminate\Http\Request;

/**
 * SRD 20.5 - duplicate review with minimal comparison fields.
 */
class DuplicateReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('reviewDuplicates', IndigeneApplication::class);

        $user = auth()->user();
        $lga = $user->activeLga();

        $query = DuplicateFlag::with(['application.indigene.currentProfile', 'application.lga', 'candidate.currentProfile'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->whereHas('application', fn ($a) => $a->where('lga_id', $lga->id)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));

        $flags = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('applications.duplicate-index', compact('flags'));
    }

    public function resolve(DuplicateFlag $flag, Request $request)
    {
        $application = $flag->application;
        $this->authorize('resolveDuplicate', $application);

        $data = $request->validate([
            'resolution' => ['required', 'in:same_person,false_positive,escalate'],
            'review_reason' => ['required', 'string', 'max:2000'],
        ]);

        $flag->status = $data['resolution'];
        $flag->reviewed_by = auth()->id();
        $flag->review_reason = $data['review_reason'];
        $flag->reviewed_at = now();
        $flag->save();

        app(\App\Services\AuditService::class)->record('duplicate.reviewed', DuplicateFlag::class, $flag->id, [
            'status' => 'open',
        ], [
            'status' => $data['resolution'],
        ], 'medium');

        return back()->with('status', 'Duplicate flag resolved as '.str_replace('_', ' ', $data['resolution']).'.');
    }
}
