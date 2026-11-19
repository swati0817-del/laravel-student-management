<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class StudentPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_download_the_student_pdf(): void
    {
        $this->createStudent();

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export.pdf'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('students.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_guest_is_redirected_to_login_from_student_pdf_export(): void
    {
        $this->get(route('students.export.pdf'))->assertRedirect('/login');
    }

    public function test_pdf_export_only_passes_students_matching_the_search_term(): void
    {
        $matchingStudent = $this->createStudent([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'course' => 'Computer Science',
        ]);
        $this->createStudent([
            'name' => 'Maya Singh',
            'email' => 'maya@example.com',
            'course' => 'BCA',
        ]);

        $actualPdf = app('dompdf.wrapper');

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('students.pdf', Mockery::on(function (array $data) use ($matchingStudent): bool {
                $this->assertSame('Computer', $data['search']);
                $this->assertCount(1, $data['students']);
                $this->assertSame($matchingStudent->id, $data['students']->first()->id);

                return true;
            }))
            ->andReturnUsing(fn (string $view, array $data) => $actualPdf->loadView($view, $data));

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export.pdf', ['search' => 'Computer']));

        $response->assertOk()->assertDownload('students.pdf');
    }

    public function test_pdf_export_respects_all_filters_and_ignores_page_number(): void
    {
        $matchingStudent = $this->createStudent([
            'name' => 'Asha Matching',
            'email' => 'asha-matching@example.com',
            'course' => 'BCA',
            'age' => 22,
        ]);
        $this->createStudent([
            'name' => 'Maya Matching',
            'email' => 'maya-matching@example.com',
            'course' => 'B.Tech',
            'age' => 22,
        ]);
        $this->createStudent([
            'name' => 'Young Matching',
            'email' => 'young-matching@example.com',
            'course' => 'BCA',
            'age' => 17,
        ]);

        $actualPdf = app('dompdf.wrapper');

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('students.pdf', Mockery::on(function (array $data) use ($matchingStudent): bool {
                $this->assertSame('Matching', $data['search']);
                $this->assertSame([
                    'search' => 'Matching',
                    'course' => 'BCA',
                    'min_age' => '20',
                    'max_age' => '25',
                ], $data['filters']);
                $this->assertCount(1, $data['students']);
                $this->assertSame($matchingStudent->id, $data['students']->first()->id);

                return true;
            }))
            ->andReturnUsing(fn (string $view, array $data) => $actualPdf->loadView($view, $data));

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export.pdf', [
                'search' => 'Matching',
                'course' => 'BCA',
                'min_age' => 20,
                'max_age' => 25,
                'page' => 99,
            ]));

        $response->assertOk()->assertDownload('students.pdf');
    }

    public function test_pdf_shows_a_friendly_message_when_search_has_no_matches(): void
    {
        $this->createStudent();
        $actualPdf = app('dompdf.wrapper');

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('students.pdf', Mockery::on(function (array $data): bool {
                $this->assertSame('NoSuchCourse', $data['search']);
                $this->assertTrue($data['filtersApplied']);
                $this->assertCount(0, $data['students']);

                return true;
            }))
            ->andReturnUsing(fn (string $view, array $data) => $actualPdf->loadView($view, $data));

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export.pdf', ['search' => 'NoSuchCourse']));

        $response->assertOk()->assertDownload('students.pdf');
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'PDF Export Admin',
            'email' => 'pdf-admin@example.com',
            'password' => Hash::make('test-password'),
        ]);
    }

    private function createStudent(array $attributes = []): Student
    {
        static $number = 0;
        $number++;

        return Student::create(array_merge([
            'name' => "PDF Student {$number}",
            'email' => "pdf-student{$number}@example.com",
            'phone' => '1234567890',
            'course' => 'BCA',
            'age' => 20,
        ], $attributes));
    }
}
