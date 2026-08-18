<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AccountActivationToken;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function show(string $token): View
    {
        $activation = $this->validToken($token);

        if (! $activation) {
            abort(404, 'This activation link is invalid or has expired.');
        }

        return view('auth.activate', [
            'token' => $token,
            'user' => $activation->user,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $activation = $this->validToken($token);

        if (! $activation) {
            abort(404, 'This activation link is invalid or has expired.');
        }

        $user = $activation->user;

        $request->validate([
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'accept_terms' => ['accepted'],
        ], [
            'password.min' => 'Passwords must be at least 10 characters. Prefer a long passphrase.',
            'accept_terms.accepted' => 'You must acknowledge the acceptable-use notice to continue.',
        ]);

        $user->password = $request->input('password');
        $user->status = 'active';
        $user->must_change_password = false;
        $user->email_verified_at = now();

        if ($request->filled('phone')) {
            $user->phone = $request->input('phone');
            $user->phone_verified_at = now();
        }

        $user->save();

        $activation->used_at = now();
        $activation->save();

        $this->audit->record('account.activated', User::class, $user->id, [
            'status' => 'invited',
        ], [
            'status' => 'active',
        ], 'medium', $user);

        Auth::login($user, false);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Your account is now active. Welcome to NIMCS.');
    }

    private function validToken(string $token): ?AccountActivationToken
    {
        $hash = hash('sha256', $token);

        return AccountActivationToken::with('user')
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();
    }
}
