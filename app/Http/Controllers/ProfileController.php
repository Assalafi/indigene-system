<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function show()
    {
        $user = auth()->user()->load(['roles', 'activeAssignments.lga', 'assignments.lga.state']);

        $recentSecurity = \App\Models\LoginEvent::where('user_id', $user->id)->latest()->limit(10)->get();

        return view('profile.show', compact('user', 'recentSecurity'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,'.auth()->id()],
        ]);

        $user = auth()->user();
        $before = ['full_name' => $user->full_name, 'phone' => $user->phone];

        $user->update([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
        ]);

        $this->audit->record('profile.updated', User::class, $user->id, $before, [
            'full_name' => $user->full_name,
            'phone' => $user->phone,
        ], 'low', $user);

        return back()->with('status', 'Profile updated.');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ], [
            'password.min' => 'Passwords must be at least 10 characters. Prefer a long passphrase.',
        ]);

        $user = auth()->user();
        $user->password = $data['password'];
        $user->save();

        $this->audit->record('profile.password_changed', User::class, $user->id, [], [], 'medium', $user);

        return back()->with('status', 'Password changed.');
    }
}
