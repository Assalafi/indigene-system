<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountActivationToken;
use App\Models\Lga;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLgaAssignment;
use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * SRD 25 - user administration.
 */
class UserController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'activeAssignments.lga'])
            ->when($request->filled('q'), fn ($q) => $q
                ->where('full_name', 'like', "%{$request->input('q')}%")
                ->orWhere('email', 'like', "%{$request->input('q')}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $request->input('role'))));

        $users = $query->orderBy('full_name')->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::where('is_system', true)->orderBy('name')->get();
        $states = \App\Models\State::where('status', 'active')->orderBy('name')->get();
        $lgas = Lga::with('state')->where('status', 'active')->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'states', 'lgas'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'lga_id' => ['nullable', 'uuid', 'exists:lgas,id'],
            'appointment_title' => ['nullable', 'string', 'max:120'],
            'appointment_reference' => ['nullable', 'string', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $role = Role::where('name', $data['role'])->firstOrFail();

        if (in_array($data['role'], ['lga_chairman', 'lga_indigene_officer', 'print_officer'], true) && empty($data['lga_id'])) {
            return back()->withErrors(['lga_id' => 'An LGA assignment is required for this role.'])->withInput();
        }

        // FR-USR-003: prevent two active primary Chairman assignments per LGA.
        if ($data['role'] === 'lga_chairman' && $data['lga_id']) {
            $conflict = UserLgaAssignment::where('lga_id', $data['lga_id'])
                ->where('is_primary', true)
                ->where('status', 'active')
                ->whereHas('user', fn ($u) => $u->where('status', 'active'))
                ->whereNull('ends_at')
                ->exists();

            if ($conflict) {
                return back()->withErrors(['lga_id' => 'This LGA already has an active primary Chairman assignment. End it or create an acting appointment.'])->withInput();
            }
        }

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => \Illuminate\Support\Str::random(64),
            'status' => 'invited',
            'must_change_password' => true,
            'created_by' => auth()->id(),
        ]);

        $user->assignRole($role);

        if (! empty($data['lga_id'])) {
            UserLgaAssignment::create([
                'user_id' => $user->id,
                'lga_id' => $data['lga_id'],
                'role_id' => $role->id,
                'assignment_type' => 'primary',
                'appointment_title' => $data['appointment_title'] ?? null,
                'appointment_reference' => $data['appointment_reference'] ?? null,
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['ends_at'] ?? null,
                'is_primary' => true,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);
        }

        // FR-AUTH-002: one-time activation link; never email a permanent password.
        $token = bin2hex(random_bytes(32));

        AccountActivationToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(3),
            'created_by' => auth()->id(),
        ]);

        $url = route('activation.show', ['token' => $token]);
        logger()->info('Staff activation link generated', ['url' => $url, 'user' => $user->email]);

        $this->audit->record('user.created', User::class, $user->id, [], [
            'email' => $user->email,
            'role' => $role->name,
            'lga_id' => $data['lga_id'] ?? null,
        ], 'medium');

        return redirect()->route('admin.users.show', $user)
            ->with('status', 'Account created. Activation link (also in the application log): '.$url);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'assignments.lga', 'assignments.role']);

        $permissions = $user->getAllPermissions()->pluck('name');
        $loginEvents = \App\Models\LoginEvent::where('user_id', $user->id)->latest()->limit(20)->get();
        $authored = \App\Models\AuditLog::where('actor_id', $user->id)->latest('occurred_at')->limit(20)->get();
        $states = \App\Models\State::where('status', 'active')->orderBy('name')->get();
        $lgas = Lga::with('state')->where('status', 'active')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.show', compact('user', 'permissions', 'loginEvents', 'authored', 'states', 'lgas', 'roles'));
    }

    public function toggleStatus(User $user, Request $request)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'action' => ['required', 'in:suspend,reactivate,lock,unlock'],
            'reason' => ['required_if:action,suspend,lock', 'nullable', 'string', 'max:2000'],
        ]);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['action' => 'You cannot change your own account status.']);
        }

        $map = [
            'suspend' => 'suspended',
            'reactivate' => 'active',
            'lock' => 'locked',
            'unlock' => 'active',
        ];

        $user->status = $map[$data['action']];

        if (in_array($data['action'], ['suspend', 'lock'], true)) {
            $user->suspended_by = auth()->id();
            $user->suspended_at = now();
            $user->suspension_reason = $data['reason'] ?? null;
        } else {
            $user->suspended_by = null;
            $user->suspended_at = null;
            $user->suspension_reason = null;
        }

        $user->save();

        $this->audit->record('user.status_changed', User::class, $user->id, [], [
            'status' => $user->status,
            'reason' => $data['reason'] ?? null,
        ], 'high');

        return back()->with('status', 'Account status updated to '.$user->status.'.');
    }

    public function resendActivation(User $user)
    {
        $this->authorize('update', $user);

        if ($user->status !== 'invited') {
            return back()->with('info', 'This account is already active.');
        }

        $token = bin2hex(random_bytes(32));

        AccountActivationToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(3),
            'created_by' => auth()->id(),
        ]);

        $url = route('activation.show', ['token' => $token]);
        logger()->info('Staff activation link regenerated', ['url' => $url, 'user' => $user->email]);

        $this->audit->record('user.activation_resent', User::class, $user->id, [], [], 'medium');

        return back()->with('status', 'Activation link regenerated: '.$url);
    }

    /**
     * Admin resets a user's password directly. Existing sessions are revoked.
     */
    public function resetPassword(User $user, Request $request)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'new_password' => ['required', 'string', 'min:10', 'confirmed'],
        ], [
            'new_password.min' => 'Passwords must be at least 10 characters. Prefer a long passphrase.',
            'new_password.required' => 'Enter a new password for this user.',
        ]);

        $user->password = $data['new_password'];
        $user->must_change_password = false;
        $user->remember_token = null;
        $user->save();

        $this->audit->record('user.password_reset_by_admin', User::class, $user->id, [], [
            'resetting_admin' => auth()->id(),
        ], 'high');

        return back()->with('status', 'Password for '.$user->full_name.' has been reset. Existing sessions are revoked.');
    }

    public function storeAssignment(User $user, Request $request)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'lga_id' => ['required', 'uuid', 'exists:lgas,id'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'assignment_type' => ['required', 'in:primary,acting,temporary'],
            'appointment_title' => ['nullable', 'string', 'max:120'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        UserLgaAssignment::create([
            'user_id' => $user->id,
            'lga_id' => $data['lga_id'],
            'role_id' => $data['role_id'],
            'assignment_type' => $data['assignment_type'],
            'appointment_title' => $data['appointment_title'] ?? null,
            'starts_at' => $data['starts_at'] ?? now(),
            'ends_at' => $data['ends_at'] ?? null,
            'is_primary' => $data['assignment_type'] === 'primary',
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        $this->audit->record('user.assignment_created', UserLgaAssignment::class, $user->id, [], [
            'lga_id' => $data['lga_id'],
            'type' => $data['assignment_type'],
        ], 'medium');

        return back()->with('status', 'LGA assignment created.');
    }

    public function endAssignment(UserLgaAssignment $assignment, Request $request)
    {
        $this->authorize('update', $assignment->user);

        $data = $request->validate([
            'end_reason' => ['required', 'string', 'max:1000'],
        ]);

        $assignment->status = 'ended';
        $assignment->ends_at = now();
        $assignment->ended_by = auth()->id();
        $assignment->end_reason = $data['end_reason'];
        $assignment->save();

        $this->audit->record('user.assignment_ended', UserLgaAssignment::class, $assignment->id, [], [
            'end_reason' => $data['end_reason'],
        ], 'medium');

        return back()->with('status', 'Assignment ended.');
    }

    public function updateRole(User $user, Request $request)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->syncRoles([$data['role']]);

        $this->audit->record('user.role_changed', User::class, $user->id, [], [
            'role' => $data['role'],
        ], 'high');

        return back()->with('status', 'Role updated to '.$data['role'].'.');
    }
}
