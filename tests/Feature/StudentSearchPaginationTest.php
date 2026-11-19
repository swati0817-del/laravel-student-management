<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentSearchPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(Admin::create([
            'name' => 'Test Admin',
            'email' => 'search-admin@example.com',
            'password' => Hash::make('test-password'),
        ]), 'web');
    }

    public function test_students_can_be_searched_by_name_email_and_course(): void
    {
        Student::create([
            'name' => 'Leena Sharma',
            'email' => 'leena@example.com',
            'course' => 'Computer Science',
            'age' => 20,
        ]);
        Student::create([
            'name' => 'Maya Patel',
            'email' => 'maya@example.com',
            'course' => 'BCA',
            'age' => 21,
        ]);

        $this->get('/students?search=Leena')
            ->assertStatus(200)
            ->assertSee('Leena Sharma')
            ->assertDontSee('Maya Patel');

        $this->get('/students?search=maya@example.com')
            ->assertStatus(200)
            ->assertSee('Maya Patel')
            ->assertDontSee('Leena Sharma');

        $this->get('/students?search=Computer')
            ->assertStatus(200)
            ->assertSee('Leena Sharma')
            ->assertDontSee('Maya Patel');
    }

    public function test_search_term_is_preserved_and_no_match_message_is_shown(): void
    {
        $this->get('/students?search=NoMatchingStudent')
            ->assertStatus(200)
            ->assertSee('value="NoMatchingStudent"', false)
            ->assertSee('No students match "NoMatchingStudent". Try another search.', false);
    }

    public function test_course_filter_only_shows_students_in_the_selected_course(): void
    {
        Student::create([
            'name' => 'Asha Patel',
            'email' => 'asha-course@example.com',
            'course' => 'BCA',
            'age' => 20,
        ]);
        Student::create([
            'name' => 'Maya Singh',
            'email' => 'maya-course@example.com',
            'course' => 'B.Tech',
            'age' => 21,
        ]);

        $this->get('/students?course=BCA')
            ->assertOk()
            ->assertSee('Asha Patel')
            ->assertDontSee('Maya Singh')
            ->assertSee('value="BCA" selected', false);
    }

    public function test_course_dropdown_contains_distinct_non_empty_existing_courses(): void
    {
        foreach (['BCA', 'BCA', 'B.Tech', '', '   '] as $index => $course) {
            Student::create([
                'name' => "Course Option {$index}",
                'email' => "course-option{$index}@example.com",
                'course' => $course,
                'age' => 20,
            ]);
        }

        $this->get('/students')
            ->assertOk()
            ->assertViewHas('courses', fn ($courses): bool => $courses->count() === 2
                && $courses->contains('BCA')
                && $courses->contains('B.Tech')
                && $courses->every(fn ($course): bool => trim($course) !== ''));
    }

    public function test_minimum_age_filter_excludes_younger_students(): void
    {
        Student::create(['name' => 'Age 17 Student', 'email' => 'age17@example.com', 'course' => 'BCA', 'age' => 17]);
        Student::create(['name' => 'Age 20 Student', 'email' => 'age20@example.com', 'course' => 'BCA', 'age' => 20]);

        $this->get('/students?min_age=20')
            ->assertOk()
            ->assertSee('Age 20 Student')
            ->assertDontSee('Age 17 Student')
            ->assertSee('id="min_age" name="min_age" value="20"', false);
    }

    public function test_maximum_age_filter_excludes_older_students(): void
    {
        Student::create(['name' => 'Age 20 Student', 'email' => 'maxage20@example.com', 'course' => 'BCA', 'age' => 20]);
        Student::create(['name' => 'Age 25 Student', 'email' => 'maxage25@example.com', 'course' => 'BCA', 'age' => 25]);

        $this->get('/students?max_age=20')
            ->assertOk()
            ->assertSee('Age 20 Student')
            ->assertDontSee('Age 25 Student')
            ->assertSee('id="max_age" name="max_age" value="20"', false);
    }

    public function test_search_course_and_age_filters_can_be_combined(): void
    {
        Student::create([
            'name' => 'Matching Student One',
            'email' => 'combined-one@example.com',
            'course' => 'BCA',
            'age' => 21,
        ]);
        Student::create([
            'name' => 'Matching Student Two',
            'email' => 'combined-two@example.com',
            'course' => 'B.Tech',
            'age' => 21,
        ]);
        Student::create([
            'name' => 'Matching Student Three',
            'email' => 'combined-three@example.com',
            'course' => 'BCA',
            'age' => 30,
        ]);

        $this->get('/students?search=Matching&course=BCA&min_age=20&max_age=25')
            ->assertOk()
            ->assertSee('Matching Student One')
            ->assertDontSee('Matching Student Two')
            ->assertDontSee('Matching Student Three')
            ->assertSee('value="Matching"', false)
            ->assertSee('value="BCA" selected', false)
            ->assertSee('value="20"', false)
            ->assertSee('value="25"', false);
    }

    public function test_clear_filters_link_removes_filters_and_unfiltered_list_shows_all_students(): void
    {
        Student::create(['name' => 'Filter Match', 'email' => 'filtermatch@example.com', 'course' => 'BCA', 'age' => 20]);
        Student::create(['name' => 'Other Student', 'email' => 'otherfilter@example.com', 'course' => 'B.Tech', 'age' => 30]);

        $this->get('/students?search=Filter&course=BCA&min_age=18&max_age=25')
            ->assertOk()
            ->assertSee('Clear filters')
            ->assertSee('href="'.route('students.index').'"', false)
            ->assertDontSee('Other Student');

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('Filter Match')
            ->assertSee('Other Student');
    }

    public function test_pagination_links_retain_all_active_filters(): void
    {
        for ($number = 1; $number <= 11; $number++) {
            Student::create([
                'name' => "Matching Filter Student {$number}",
                'email' => "matching-filter{$number}@example.com",
                'course' => 'BCA',
                'age' => 21,
            ]);
        }

        Student::create([
            'name' => 'Unmatched Filter Student',
            'email' => 'unmatched-filter@example.com',
            'course' => 'B.Tech',
            'age' => 35,
        ]);

        $this->get('/students?search=Matching&course=BCA&min_age=20&max_age=25')
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('search=Matching', false)
            ->assertSee('course=BCA', false)
            ->assertSee('min_age=20', false)
            ->assertSee('max_age=25', false);

        $this->get('/students?search=Matching&course=BCA&min_age=20&max_age=25&page=2')
            ->assertOk()
            ->assertSee('Matching Filter Student 1')
            ->assertDontSee('Unmatched Filter Student');
    }

    public function test_invalid_age_inputs_and_reversed_age_range_redirect_with_clear_errors(): void
    {
        $this->from('/students')
            ->get('/students?min_age=not-a-number&max_age=20')
            ->assertRedirect('/students')
            ->assertSessionHasErrors('min_age')
            ->assertSessionHas('_old_input.min_age', 'not-a-number')
            ->assertSessionHas('_old_input.max_age', '20');

        $this->from('/students')
            ->get('/students?min_age=30&max_age=20')
            ->assertRedirect('/students')
            ->assertSessionHasErrors('max_age', 'Maximum age must be greater than or equal to minimum age.')
            ->assertSessionHas('_old_input.min_age', '30')
            ->assertSessionHas('_old_input.max_age', '20');
    }

    public function test_student_list_is_paginated_with_ten_students_per_page(): void
    {
        for ($number = 1; $number <= 12; $number++) {
            $student = Student::create([
                'name' => "Student {$number}",
                'email' => "student{$number}@example.com",
                'course' => 'BCA',
                'age' => 20,
            ]);

            $student->created_at = now()->addSeconds($number);
            $student->save();
        }

        $this->get('/students')
            ->assertStatus(200)
            ->assertSee('Student 12')
            ->assertSee('Student 3')
            ->assertDontSee('Student 2')
            ->assertSee('page=2', false);

        $this->get('/students?page=2')
            ->assertStatus(200)
            ->assertSee('Student 2')
            ->assertSee('Student 1')
            ->assertDontSee('Student 3');
    }

    public function test_search_is_preserved_when_navigating_between_pages(): void
    {
        for ($number = 1; $number <= 11; $number++) {
            $student = Student::create([
                'name' => "Matching Student {$number}",
                'email' => "matching{$number}@example.com",
                'course' => 'Computer Science',
                'age' => 20,
            ]);

            $student->created_at = now()->addSeconds($number);
            $student->save();
        }

        Student::create([
            'name' => 'Unrelated Student',
            'email' => 'unrelated@example.com',
            'course' => 'BCA',
            'age' => 22,
        ]);

        $this->get('/students?search=Matching')
            ->assertStatus(200)
            ->assertSee('value="Matching"', false)
            ->assertSee('search=Matching', false)
            ->assertSee('page=2', false)
            ->assertDontSee('Unrelated Student');

        $this->get('/students?search=Matching&page=2')
            ->assertStatus(200)
            ->assertSee('value="Matching"', false)
            ->assertSee('Matching Student 1')
            ->assertDontSee('Unrelated Student');
    }
}
