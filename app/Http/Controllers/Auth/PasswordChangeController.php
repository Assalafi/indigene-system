<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function showForced(): View
    {
        return view('auth.forced-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ], [
            'password.min' => 'Passwords must be at least 10 characters. Prefer a long passphrase.',
        ]);

        $user = auth()->user();
        $user->password = $request->input('password');
        $user->must_change_password = false;
        $user->save();

        $this->audit->record('account.password_changed', \App\Models\User::class, $user->id, [], [], 'medium', $user);

        return redirect()->route('dashboard')->with('status', 'Your password has been updated.');
    }
}
