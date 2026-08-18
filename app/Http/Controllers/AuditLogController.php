<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoginEvent;
use App\Models\SensitiveDataAccessLog;
use Illuminate\Http\Request;

/**
 * SRD 27.1 - read-only audit screens.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $user = auth()->user();

        $query = AuditLog::with(['actor', 'actorLga']);

        if (! $user->isSystemAdmin() && $user->can('audit.view-lga')) {
            $query->where('actor_lga_id', $user->activeLga()?->id);
        } elseif (! $user->isSystemAdmin()) {
            $query->where('actor_id', $user->id);
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('action', 'like', "%{$term}%")
                    ->orWhere('auditable_type', 'like', "%{$term}%")
                    ->orWhere('auditable_id', $term);
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('risk')) {
            $query->where('risk_level', $request->input('risk'));
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->input('actor_id'));
        }

        $logs = $query->orderByDesc('occurred_at')->paginate(25)->withQueryString();

        return view('audit.index', compact('logs'));
    }

    public function show(AuditLog $log)
    {
        $this->authorize('view', $log);

        return view('audit.show', compact('log'));
    }

    public function sensitiveAccess(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = SensitiveDataAccessLog::with('actor');

        if (! auth()->user()->isSystemAdmin()) {
            $query->where('actor_id', auth()->id());
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('audit.sensitive-access', compact('logs'));
    }

    public function loginEvents(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = LoginEvent::with('user')
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->input('event_type')))
            ->when($request->filled('success') !== null, fn ($q) => $q->where('success', $request->boolean('success')));

        $events = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('audit.login-events', compact('events'));
    }
}
