<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(Admin::create([
            'name' => 'Test Admin',
            'email' => 'validation-admin@example.com',
            'password' => Hash::make('test-password'),
        ]), 'web');
    }

    public function test_registration_rejects_invalid_name_and_course_and_shows_field_errors(): void
    {
        $this->from('/students/create')
            ->followingRedirects()
            ->post('/students', [
                'name' => 'Student7',
                'email' => 'student@example.com',
                'phone' => '',
                'course' => '12345',
                'age' => 20,
            ])
            ->assertOk()
            ->assertSee('The name may contain letters, spaces, hyphens, and apostrophes only.')
            ->assertSee('The course must contain at least one letter.');

        $this->assertDatabaseCount('students', 0);
    }

    public function test_registration_accepts_names_and_courses_with_allowed_characters(): void
    {
        $this->post('/students', [
            'name' => "Anne-Marie O'Connor",
            'email' => 'anne@example.com',
            'phone' => '',
            'course' => '12th Science',
            'age' => 20,
        ])->assertRedirect('/students');

        $this->assertDatabaseHas('students', [
            'name' => "Anne-Marie O'Connor",
            'course' => '12th Science',
        ]);
    }

    public function test_update_rejects_invalid_name_and_course_and_shows_field_errors(): void
    {
        $student = Student::create([
            'name' => 'Valid Student',
            'email' => 'valid@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->from("/students/{$student->id}/edit")
            ->followingRedirects()
            ->put("/students/{$student->id}", [
                'name' => 'Student7',
                'email' => 'valid@example.com',
                'phone' => '',
                'course' => '12345',
                'age' => 20,
            ])
            ->assertOk()
            ->assertSee('The name may contain letters, spaces, hyphens, and apostrophes only.')
            ->assertSee('The course must contain at least one letter.');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Valid Student',
            'course' => 'BCA',
        ]);
    }

    public function test_update_allows_the_current_students_email_but_rejects_another_students_email(): void
    {
        $student = Student::create([
            'name' => 'First Student',
            'email' => 'first@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);
        $otherStudent = Student::create([
            'name' => 'Other Student',
            'email' => 'other@example.com',
            'course' => 'B.Tech',
            'age' => 21,
        ]);

        $this->put("/students/{$student->id}", [
            'name' => 'First Student Updated',
            'email' => 'first@example.com',
            'phone' => '',
            'course' => 'PHP Laravel',
            'age' => 22,
        ])->assertRedirect('/students');

        $this->from("/students/{$student->id}/edit")
            ->followingRedirects()
            ->put("/students/{$student->id}", [
                'name' => 'First Student Updated',
                'email' => 'other@example.com',
                'phone' => '',
                'course' => 'PHP Laravel',
                'age' => 22,
            ])
            ->assertOk()
            ->assertSee('The email has already been taken.');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'email' => 'first@example.com',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $otherStudent->id,
            'email' => 'other@example.com',
        ]);
    }
}
