<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(Admin::create([
            'name' => 'Test Admin',
            'email' => 'dashboard-admin@example.com',
            'password' => Hash::make('test-password'),
        ]), 'web');
    }

    public function test_dashboard_shows_student_totals_course_counts_and_latest_five_students(): void
    {
        $studentData = [
            ['Student 1', 'student1@example.com', 'BCA'],
            ['Student 2', 'student2@example.com', 'BCA'],
            ['Student 3', 'student3@example.com', 'B.Tech'],
            ['Student 4', 'student4@example.com', 'BCA'],
            ['Student 5', 'student5@example.com', 'PHP Laravel'],
            ['Student 6', 'student6@example.com', 'B.Tech'],
            ['Student 7', 'student7@example.com', 'BCA'],
        ];

        foreach ($studentData as $index => [$name, $email, $course]) {
            $student = Student::create([
                'name' => $name,
                'email' => $email,
                'course' => $course,
                'age' => 20,
            ]);

            $student->created_at = now()->addSeconds($index);
            $student->save();
        }

        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertViewHas('totalStudents', 7)
            ->assertViewHas('totalCourses', 3)
            ->assertViewHas('courseCounts', function ($courseCounts): bool {
                return (int) $courseCounts->firstWhere('course', 'BCA')->student_count === 4
                    && (int) $courseCounts->firstWhere('course', 'B.Tech')->student_count === 2
                    && (int) $courseCounts->firstWhere('course', 'PHP Laravel')->student_count === 1;
            })
            ->assertViewHas('courseChartLabels', fn ($labels): bool => $labels->count() === 3
                && $labels->contains('BCA')
                && $labels->contains('B.Tech')
                && $labels->contains('PHP Laravel'))
            ->assertViewHas('courseChartValues', fn ($values): bool => $values->count() === 3
                && $values->contains(4)
                && $values->contains(2)
                && $values->contains(1))
            ->assertSee('course-distribution-chart', false)
            ->assertViewHas('latestStudents', function ($latestStudents): bool {
                return $latestStudents->count() === 5
                    && $latestStudents->first()->name === 'Student 7'
                    && $latestStudents->last()->name === 'Student 3';
            })
            ->assertSee('Student 7')
            ->assertDontSee('Student 2');
    }

    public function test_dashboard_shows_zero_totals_and_empty_states_when_there_are_no_students(): void
    {
        $this->get('/dashboard')
            ->assertStatus(200)
            ->assertViewHas('totalStudents', 0)
            ->assertViewHas('totalCourses', 0)
            ->assertViewHas('courseChartLabels', fn ($labels): bool => $labels->isEmpty())
            ->assertViewHas('courseChartValues', fn ($values): bool => $values->isEmpty())
            ->assertSee('No course data yet.')
            ->assertSee('No course enrollment data to chart yet.')
            ->assertSee('No students have been registered yet.');
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        Auth::guard('web')->logout();

        $this->get('/dashboard')->assertRedirect('/login');
    }
}
