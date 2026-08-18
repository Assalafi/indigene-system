<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Self-service password reset.
 *
 * Identity is verified by matching the staff email AND phone on file. On a match
 * the user proceeds directly to a new-password page; no reset link is used.
 * Verification is session-bound, short-lived and rate limited.
 */
class PasswordResetController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $throttleKey = 'password-reset:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many attempts. Try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $data = $request->validate([
            'email' => ['required', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'email.required' => 'Enter the staff email address.',
            'phone.required' => 'Enter the phone number on file.',
        ]);

        $user = User::where('email', strtolower($data['email']))->first();
        $matched = $user
            && $user->phone
            && $this->phonesMatch($user->phone, $data['phone']);

        // Generic response: do not reveal which field was wrong.
        if (! $matched) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withErrors([
                'email' => 'The email and phone could not be matched to a staff account.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);

        session([
            'password.reset.verified_user_id' => $user->id,
            'password.reset.expires' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('password.new');
    }

    public function showNewPassword(): View
    {
        if (! $this->verifiedUserId()) {
            return redirect()->route('password.request');
        }

        return view('auth.new-password');
    }

    public function setNewPassword(Request $request)
    {
        $userId = $this->verifiedUserId();

        if (! $userId) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ], [
            'password.min' => 'Passwords must be at least 10 characters. Prefer a long passphrase.',
        ]);

        $user = User::findOrFail($userId);

        $user->password = $data['password'];
        $user->must_change_password = false;
        $user->remember_token = null;
        $user->save();

        session()->forget(['password.reset.verified_user_id', 'password.reset.expires']);

        $this->audit->record('account.password_reset', User::class, $user->id, [], [], 'high');

        return redirect()->route('login')->with('status', 'Your password has been reset. Sign in with your new password.');
    }

    private function verifiedUserId(): ?string
    {
        $userId = session('password.reset.verified_user_id');
        $expires = session('password.reset.expires');

        if (! $userId || ! $expires || now()->timestamp > (int) $expires) {
            session()->forget(['password.reset.verified_user_id', 'password.reset.expires']);

            return null;
        }

        return $userId;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '234'.substr($digits, 1);
        }

        return $digits;
    }

    private function phonesMatch(string $stored, string $input): bool
    {
        return $this->normalizePhone($stored) === $this->normalizePhone($input);
    }
}
