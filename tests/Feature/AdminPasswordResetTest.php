<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_forgot_password_form(): void
    {
        $this->get('/forgot-password')
            ->assertStatus(200)
            ->assertSee('Forgot your password?')
            ->assertSee('name="email"', false);
    }

    public function test_forgot_password_request_sends_a_reset_notification_to_the_admin(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $admin->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'If an admin account exists for that email, a reset link will be sent. Links expire after 60 minutes, and requesting a new link replaces older links.');

        Notification::assertSentTo($admin, ResetPassword::class, function (ResetPassword $notification): bool {
            return is_string($notification->token) && $notification->token !== '';
        });
    }

    public function test_forgot_password_request_does_not_reveal_unknown_admin_emails(): void
    {
        Notification::fake();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => 'unknown@example.com'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'If an admin account exists for that email, a reset link will be sent. Links expire after 60 minutes, and requesting a new link replaces older links.');

        Notification::assertNothingSent();
    }

    public function test_admin_can_reset_password_with_a_valid_time_limited_token(): void
    {
        $admin = $this->createAdmin();
        $token = Password::broker('admins')->createToken($admin);

        $this->get(route('password.reset', ['token' => $token, 'email' => $admin->email]))
            ->assertStatus(200)
            ->assertSee('Choose a new password')
            ->assertSee('use the newest one')
            ->assertSee('name="password_confirmation"', false);

        $this->from(route('password.reset', ['token' => $token, 'email' => $admin->email]))
            ->post('/reset-password', [
                'token' => $token,
                'email' => $admin->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'Your password has been reset. You can now sign in.');

        $this->assertTrue(Hash::check('new-secure-password', $admin->fresh()->password));
        $this->assertFalse(Password::broker('admins')->tokenExists($admin, $token));
    }

    public function test_reset_rejects_mismatched_password_confirmation(): void
    {
        $admin = $this->createAdmin();
        $token = Password::broker('admins')->createToken($admin);

        $this->from(route('password.reset', ['token' => $token, 'email' => $admin->email]))
            ->post('/reset-password', [
                'token' => $token,
                'email' => $admin->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('password.reset', ['token' => $token, 'email' => $admin->email]))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-secure-password', $admin->fresh()->password));
    }

    public function test_invalid_or_expired_reset_tokens_cannot_change_password(): void
    {
        $admin = $this->createAdmin();
        Config::set('auth.passwords.admins.expire', 1);

        $this->from('/reset-password/invalid-token?email='.$admin->email)
            ->post('/reset-password', [
                'token' => 'invalid-token',
                'email' => $admin->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertSessionHasErrors('email');

        $expiredToken = Password::broker('admins')->createToken($admin);
        $this->travel(2)->minutes();

        $this->from(route('password.reset', ['token' => $expiredToken, 'email' => $admin->email]))
            ->post('/reset-password', [
                'token' => $expiredToken,
                'email' => $admin->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-secure-password', $admin->fresh()->password));
    }

    public function test_requesting_a_new_token_invalidates_the_previous_token(): void
    {
        $admin = $this->createAdmin();
        $broker = Password::broker('admins');

        $olderToken = $broker->createToken($admin);
        $newestToken = $broker->createToken($admin);

        $this->assertFalse($broker->tokenExists($admin, $olderToken));
        $this->assertTrue($broker->tokenExists($admin, $newestToken));
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Password Reset Admin',
            'email' => 'reset-admin@example.com',
            'password' => Hash::make('old-secure-password'),
        ]);
    }
}
