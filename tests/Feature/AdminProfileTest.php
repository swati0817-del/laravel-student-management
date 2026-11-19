<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_view_profile(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->get('/profile')
            ->assertOk()
            ->assertSee('Admin Profile')
            ->assertSee('Edit Profile')
            ->assertSee('profile/edit')
            ->assertSee('profile@example.com');
    }

    public function test_admin_can_open_the_edit_profile_form(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->get('/profile/edit')
            ->assertOk()
            ->assertSee('Update Profile')
            ->assertSee('Change Password')
            ->assertSee('value="Profile Admin"', false)
            ->assertSee('value="profile@example.com"', false);
    }

    public function test_admin_can_update_name_and_email(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->put('/profile', [
                'name' => 'Updated Admin',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'Your profile details have been updated.');

        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_can_keep_their_existing_email(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->put('/profile', [
                'name' => 'Renamed Admin',
                'email' => $admin->email,
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'name' => 'Renamed Admin',
            'email' => 'profile@example.com',
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->createAdmin();
        Admin::create([
            'name' => 'Another Admin',
            'email' => 'another@example.com',
            'password' => 'another-password',
        ]);

        $this->actingAs($admin, 'web')
            ->from('/profile')
            ->put('/profile', [
                'name' => 'Updated Admin',
                'email' => 'another@example.com',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('email');
    }

    public function test_incorrect_current_password_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->from('/profile')
            ->put('/profile/password', [
                'current_password' => 'incorrect-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_admin_can_change_password_without_being_logged_out(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->put('/profile/password', [
                'current_password' => 'current-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'Your password has been changed.');

        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertTrue(Hash::check('new-secure-password', $admin->fresh()->password));
    }

    public function test_password_confirmation_mismatch_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web')
            ->from('/profile')
            ->put('/profile/password', [
                'current_password' => 'current-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Profile Admin',
            'email' => 'profile@example.com',
            'password' => 'current-password',
        ]);
    }
}
