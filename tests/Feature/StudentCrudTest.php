<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(Admin::create([
            'name' => 'Test Admin',
            'email' => 'crud-admin@example.com',
            'password' => Hash::make('test-password'),
        ]), 'web');
    }

    public function test_student_listing_page_loads_successfully(): void
    {
        Student::create([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->get('/students')
            ->assertStatus(200)
            ->assertSee('Asha Patel')
            ->assertSee('asha@example.com');
    }

    public function test_registration_form_loads_successfully(): void
    {
        $this->get('/students/create')->assertStatus(200);
    }

    public function test_a_student_can_be_registered(): void
    {
        $this->post('/students', [
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'phone' => '9876543210',
            'course' => 'B.Tech',
            'age' => 20,
        ])->assertRedirect('/students');

        $this->assertDatabaseHas('students', [
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'phone' => '9876543210',
            'course' => 'B.Tech',
            'age' => 20,
        ]);
    }

    public function test_invalid_student_details_are_rejected(): void
    {
        $this->from('/students/create')
            ->post('/students', [
                'name' => 'Asha2',
                'email' => 'not-an-email',
                'phone' => '',
                'course' => '12345',
                'age' => 0,
            ])
            ->assertRedirect('/students/create')
            ->assertSessionHasErrors(['name', 'email', 'course', 'age']);

        $this->assertDatabaseCount('students', 0);
    }

    public function test_duplicate_email_is_rejected_during_registration(): void
    {
        Student::create([
            'name' => 'Existing Student',
            'email' => 'same@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->from('/students/create')
            ->post('/students', [
                'name' => 'Another Student',
                'email' => 'same@example.com',
                'phone' => '',
                'course' => 'B.Tech',
                'age' => 21,
            ])
            ->assertRedirect('/students/create')
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('students', 1);
    }

    public function test_a_student_can_be_edited(): void
    {
        $student = Student::create([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->put("/students/{$student->id}", [
            'name' => 'Asha Patel Updated',
            'email' => 'asha.updated@example.com',
            'phone' => '9876543210',
            'course' => 'PHP Laravel',
            'age' => 21,
        ])->assertRedirect('/students');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Asha Patel Updated',
            'email' => 'asha.updated@example.com',
            'course' => 'PHP Laravel',
            'age' => 21,
        ]);
    }

    public function test_existing_email_can_remain_unchanged_during_editing(): void
    {
        $student = Student::create([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->put("/students/{$student->id}", [
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'phone' => '',
            'course' => 'BCA',
            'age' => 20,
        ])->assertRedirect('/students');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'email' => 'asha@example.com',
        ]);
    }

    public function test_a_student_can_be_deleted(): void
    {
        $student = Student::create([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->delete("/students/{$student->id}")
            ->assertRedirect('/students');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_invalid_phone_numbers_are_rejected_on_registration_and_update(): void
    {
        $this->from('/students/create')
            ->post('/students', [
                'name' => 'Asha Patel',
                'email' => 'asha@example.com',
                'phone' => '987ABC',
                'course' => 'BCA',
                'age' => 20,
            ])
            ->assertRedirect('/students/create')
            ->assertSessionHasErrors('phone');

        $student = Student::create([
            'name' => 'Existing Student',
            'email' => 'existing@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);

        $this->from("/students/{$student->id}/edit")
            ->put("/students/{$student->id}", [
                'name' => 'Existing Student',
                'email' => 'existing@example.com',
                'phone' => '123-456',
                'course' => 'BCA',
                'age' => 20,
            ])
            ->assertRedirect("/students/{$student->id}/edit")
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'phone' => null,
        ]);
    }
}
