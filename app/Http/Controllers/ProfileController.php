<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('profile.show', [
            'admin' => $request->user(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'admin' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'That email address is already used by another admin.',
        ]);

        $admin->update($validated);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Your profile details have been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Enter your current password.',
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.min' => 'Choose a new password with at least 8 characters.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        /** @var Admin $admin */
        $admin = $request->user();
        $admin->password = Hash::make($validated['password']);
        $admin->save();

        return redirect()
            ->route('profile.show')
            ->with('status', 'Your password has been changed.');
    }
}
