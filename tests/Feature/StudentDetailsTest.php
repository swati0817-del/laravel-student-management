<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_view_student_details(): void
    {
        $admin = $this->createAdmin();
        $student = Student::create([
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'phone' => '1234567890',
            'course' => 'BCA',
            'age' => 21,
        ]);

        $this->actingAs($admin, 'web')
            ->get(route('students.show', $student))
            ->assertOk()
            ->assertSee('Student ID')
            ->assertSee((string) $student->id)
            ->assertSee('Jordan Lee')
            ->assertSee('jordan@example.com')
            ->assertSee('1234567890')
            ->assertSee('BCA')
            ->assertSee('21')
            ->assertSee($student->created_at->format('M j, Y, g:i A'))
            ->assertSee($student->updated_at->format('M j, Y, g:i A'))
            ->assertSee('Back to Students')
            ->assertSee('Edit Student');
    }

    public function test_guest_is_redirected_to_login_from_student_details(): void
    {
        $student = Student::create([
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'course' => 'BCA',
            'age' => 21,
        ]);

        $this->get(route('students.show', $student))
            ->assertRedirect('/login');
    }

    public function test_missing_student_details_return_a_404_response(): void
    {
        $this->actingAs($this->createAdmin(), 'web')
            ->get('/students/999999')
            ->assertNotFound();
    }

    public function test_students_listing_has_view_details_edit_and_delete_actions(): void
    {
        $admin = $this->createAdmin();
        $student = Student::create([
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'course' => 'BCA',
            'age' => 21,
        ]);

        $this->actingAs($admin, 'web')
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee(route('students.show', $student), false)
            ->assertSee(route('students.edit', $student), false)
            ->assertSee(route('students.destroy', $student), false)
            ->assertSee('View Details')
            ->assertSee('Edit')
            ->assertSee('Delete');
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Details Test Admin',
            'email' => 'details-admin@example.com',
            'password' => Hash::make('test-password'),
        ]);
    }
}
