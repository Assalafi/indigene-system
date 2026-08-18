<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $throttleKey = 'login:'.strtolower($request->input('email', 'unknown')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower($credentials['email']))->first();

        // FR-AUTH-005: generic failure message that does not disclose account existence.
        if (! $user || ! \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            $this->logEvent($user, 'login_failed', false, 'bad_credentials');

            return back()->withErrors([
                'email' => 'The credentials you provided could not be verified.',
            ])->onlyInput('email');
        }

        if (! $user->isActive()) {
            $this->logEvent($user, 'login_blocked', false, 'account_inactive');

            return back()->withErrors([
                'email' => 'This account is not active. Contact your System Administrator.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, false);
        $request->session()->regenerate();
        $this->logEvent($user, 'login_success', true, null);
        $user->update(['last_login_at' => now(), 'last_login_ip_hash' => hash('sha256', $request->ip().'|'.config('app.key'))]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->logEvent($user, 'logout', true, null);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function logEvent(?User $user, string $type, bool $success, ?string $riskFlag): void
    {
        try {
            LoginEvent::create([
                'user_id' => $user?->id,
                'identity_hash' => $user ? hash('sha256', $user->email) : null,
                'event_type' => $type,
                'success' => $success,
                'ip_hash' => request() ? hash('sha256', request()->ip().'|'.config('app.key')) : null,
                'user_agent' => request()?->userAgent(),
                'risk_flags' => $riskFlag ? [$riskFlag] : null,
            ]);
        } catch (\Throwable) {
            // Login event logging must never break the login flow.
        }
    }
}
