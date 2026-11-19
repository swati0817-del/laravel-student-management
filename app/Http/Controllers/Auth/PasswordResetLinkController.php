<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Enter the email address for your admin account.',
            'email.email' => 'Enter a valid email address.',
        ]);

        $status = Password::broker('admins')->sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => 'A reset link was requested recently. Please wait before trying again.',
            ]);
        }

        // Use the same response whether or not the address belongs to an admin.
        return back()->with(
            'status',
            'If an admin account exists for that email, a reset link will be sent. Links expire after 60 minutes, and requesting a new link replaces older links.'
        );
    }
}
