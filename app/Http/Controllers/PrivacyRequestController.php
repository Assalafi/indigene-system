<?php

namespace App\Http\Controllers;

use App\Models\LegalHold;
use App\Models\PrivacyRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\IndigeneProfileVersionService;
use Illuminate\Http\Request;

/**
 * SRD 27.2 / 39.3-39.4 - privacy requests and legal holds.
 */
class PrivacyRequestController extends Controller
{
    public function __construct(private AuditService $audit, private IndigeneProfileVersionService $references) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PrivacyRequest::class);

        $requests = PrivacyRequest::with(['indigene.currentProfile', 'assignee'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('request_type', $request->input('type')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('privacy.requests-index', compact('requests'));
    }

    public function create()
    {
        return view('privacy.request-create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'indigene_id' => ['nullable', 'uuid', 'exists:indigenes,id'],
            'request_type' => ['required', 'in:access,rectification,objection,restriction,portability,erasure'],
            'requester_note' => ['required', 'string', 'max:4000'],
            'requester_identity' => ['required', 'string', 'max:1000'],
        ]);

        $privacyRequest = PrivacyRequest::create([
            'reference_number' => $this->references->privacyReference(),
            'indigene_id' => $data['indigene_id'] ?? null,
            'requester_identity_ciphertext' => encrypt($data['requester_identity']),
            'request_type' => $data['request_type'],
            'channel' => 'portal',
            'received_at' => now(),
            'verification_status' => 'unverified',
            'status' => 'open',
            'due_at' => now()->addDays(30),
        ]);

        $this->audit->record('privacy.request_created', PrivacyRequest::class, $privacyRequest->id, [], [
            'request_type' => $privacyRequest->request_type,
        ], 'medium');

        return redirect()->route('privacy.requests.index')
            ->with('status', 'Privacy request recorded with reference '.$privacyRequest->reference_number.'.');
    }

    public function show(PrivacyRequest $privacyRequest)
    {
        $this->authorize('view', $privacyRequest);

        $dpos = User::role('data_protection_officer')->get();

        return view('privacy.request-show', compact('privacyRequest', 'dpos'));
    }

    public function decide(PrivacyRequest $privacyRequest, Request $request)
    {
        $this->authorize('decide', $privacyRequest);

        $data = $request->validate([
            'verification_status' => ['required', 'in:unverified,verified,failed'],
            'decision' => ['required', 'string', 'max:4000'],
            'lawful_exception' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
            'complete' => ['nullable', 'boolean'],
        ]);

        $privacyRequest->verification_status = $data['verification_status'];
        $privacyRequest->decision = $data['decision'];
        $privacyRequest->lawful_exception = $data['lawful_exception'] ?? null;
        $privacyRequest->assigned_to = $data['assigned_to'] ?? $privacyRequest->assigned_to;

        if ($request->boolean('complete')) {
            $privacyRequest->status = 'completed';
            $privacyRequest->completed_at = now();
        } else {
            $privacyRequest->status = 'in_progress';
        }

        $privacyRequest->save();

        $this->audit->record('privacy.request_decided', PrivacyRequest::class, $privacyRequest->id, [], [
            'verification_status' => $privacyRequest->verification_status,
            'status' => $privacyRequest->status,
        ], 'high');

        return redirect()->route('privacy.requests.show', $privacyRequest)->with('status', 'Privacy case updated.');
    }

    // ------------------------------------------------------------ Legal holds

    public function holds()
    {
        $this->authorize('viewAny', PrivacyRequest::class);

        $holds = LegalHold::orderByDesc('created_at')->paginate(25);

        return view('privacy.holds-index', compact('holds'));
    }

    public function storeHold(Request $request)
    {
        $this->authorize('viewAny', PrivacyRequest::class);

        $data = $request->validate([
            'subject_type' => ['required', 'string', 'max:60'],
            'subject_id' => ['required', 'uuid'],
            'reason' => ['required', 'string', 'max:4000'],
            'authority_reference' => ['nullable', 'string', 'max:180'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $hold = LegalHold::create([
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'reason' => $data['reason'],
            'authority_reference' => $data['authority_reference'] ?? null,
            'starts_at' => now(),
            'ends_at' => $data['ends_at'] ?? null,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        $this->audit->record('legal_hold.created', LegalHold::class, $hold->id, [], [
            'subject_type' => $hold->subject_type,
        ], 'high');

        return back()->with('status', 'Legal hold applied. Normal disposal is suspended for this subject.');
    }

    public function releaseHold(LegalHold $hold)
    {
        $this->authorize('viewAny', PrivacyRequest::class);

        $hold->status = 'released';
        $hold->ends_at = now();
        $hold->released_by = auth()->id();
        $hold->save();

        $this->audit->record('legal_hold.released', LegalHold::class, $hold->id, [], [], 'high');

        return back()->with('status', 'Legal hold released.');
    }
}
