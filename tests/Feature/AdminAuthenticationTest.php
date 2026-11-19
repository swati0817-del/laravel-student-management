<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_loads(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Admin login')
            ->assertSee('Sign in');
    }

    public function test_login_requires_an_email_and_password(): void
    {
        $this->from('/login')
            ->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_admin_can_log_in_with_a_valid_password(): void
    {
        $admin = Admin::create([
            'name' => 'Development Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'correct-password',
            ])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_invalid_credentials_are_rejected_with_a_friendly_error(): void
    {
        Admin::create([
            'name' => 'Development Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

        $this->assertGuest('web');
    }

    public function test_guests_are_redirected_from_dashboard_and_every_student_route(): void
    {
        $student = Student::create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/students')->assertRedirect('/login');
        $this->get('/students/create')->assertRedirect('/login');
        $this->post('/students', [])->assertRedirect('/login');
        $this->get("/students/{$student->id}/edit")->assertRedirect('/login');
        $this->put("/students/{$student->id}", [])->assertRedirect('/login');
        $this->delete("/students/{$student->id}")->assertRedirect('/login');
    }

    public function test_authenticated_admin_is_redirected_away_from_login_page(): void
    {
        $admin = Admin::create([
            'name' => 'Development Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($admin, 'web')
            ->get('/login')
            ->assertRedirect('/dashboard');
    }

    public function test_admin_can_log_out_and_must_log_in_again_to_visit_dashboard(): void
    {
        $admin = Admin::create([
            'name' => 'Development Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($admin, 'web')
            ->post('/logout')
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'You have been logged out successfully.');

        $this->assertGuest('web');
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
