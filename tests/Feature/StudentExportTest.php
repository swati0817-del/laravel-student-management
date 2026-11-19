<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_download_the_student_csv(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export'));

        $response->assertOk()->assertDownload('students.csv');
        $rows = $this->parseCsv($response->streamedContent());

        $this->assertCount(2, $rows);
        $this->assertSame(
            ['Student ID', 'Name', 'Email', 'Phone', 'Course', 'Age'],
            $rows[0]
        );
        $this->assertSame([
            (string) $student->id,
            $student->name,
            $student->email,
            $student->phone,
            $student->course,
            (string) $student->age,
        ], $rows[1]);
    }

    public function test_guest_is_redirected_to_login_from_student_export(): void
    {
        $this->get(route('students.export'))->assertRedirect('/login');
    }

    public function test_csv_contains_the_required_column_headings(): void
    {
        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export'));

        $rows = $this->parseCsv($response->streamedContent());

        $this->assertSame(
            ['Student ID', 'Name', 'Email', 'Phone', 'Course', 'Age'],
            $rows[0]
        );
    }

    public function test_export_includes_all_students_when_search_is_not_applied(): void
    {
        $first = $this->createStudent(['name' => 'Asha Patel', 'email' => 'asha@example.com']);
        $second = $this->createStudent(['name' => 'Maya Singh', 'email' => 'maya@example.com']);

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export'));
        $rows = $this->parseCsv($response->streamedContent());
        $exportedIds = array_column(array_slice($rows, 1), 0);

        $this->assertCount(3, $rows);
        $this->assertEqualsCanonicalizing([(string) $first->id, (string) $second->id], $exportedIds);
    }

    public function test_export_only_includes_students_matching_the_search_term(): void
    {
        $matching = $this->createStudent([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'course' => 'Computer Science',
        ]);
        $this->createStudent([
            'name' => 'Maya Singh',
            'email' => 'maya@example.com',
            'course' => 'BCA',
        ]);

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export', ['search' => 'Computer']));
        $rows = $this->parseCsv($response->streamedContent());

        $this->assertCount(2, $rows);
        $this->assertSame((string) $matching->id, $rows[1][0]);
        $this->assertSame('Computer Science', $rows[1][4]);
    }

    public function test_csv_export_respects_course_and_age_filters_and_ignores_page_number(): void
    {
        $matching = $this->createStudent([
            'name' => 'Matching BCA Student',
            'email' => 'matching-bca@example.com',
            'course' => 'BCA',
            'age' => 22,
        ]);
        $this->createStudent([
            'name' => 'Too Young BCA Student',
            'email' => 'young-bca@example.com',
            'course' => 'BCA',
            'age' => 17,
        ]);
        $this->createStudent([
            'name' => 'Matching BTech Student',
            'email' => 'matching-btech@example.com',
            'course' => 'B.Tech',
            'age' => 22,
        ]);

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export', [
                'search' => 'Matching',
                'course' => 'BCA',
                'min_age' => 20,
                'max_age' => 25,
                'page' => 99,
            ]));
        $rows = $this->parseCsv($response->streamedContent());

        $this->assertCount(2, $rows);
        $this->assertSame((string) $matching->id, $rows[1][0]);
        $this->assertSame('Matching BCA Student', $rows[1][1]);
    }

    public function test_csv_escapes_commas_quotes_and_utf8_characters(): void
    {
        $student = $this->createStudent([
            'name' => 'Zoë, "Jo" O’Neil',
            'email' => 'zoe@example.com',
            'course' => 'BCA, "Data & Analytics"',
        ]);

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export'));
        $rows = $this->parseCsv($response->streamedContent());

        $this->assertSame($student->name, $rows[1][1]);
        $this->assertSame($student->course, $rows[1][4]);
    }

    public function test_csv_prevents_formula_like_text_from_being_interpreted_by_spreadsheets(): void
    {
        $this->createStudent(['course' => '=SUM(1,2)']);

        $response = $this->actingAs($this->createAdmin(), 'web')
            ->get(route('students.export'));
        $rows = $this->parseCsv($response->streamedContent());

        $this->assertSame("'=SUM(1,2)", $rows[1][4]);
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Export Test Admin',
            'email' => 'export-admin@example.com',
            'password' => Hash::make('test-password'),
        ]);
    }

    private function createStudent(array $attributes = []): Student
    {
        static $number = 0;
        $number++;

        return Student::create(array_merge([
            'name' => "Export Student {$number}",
            'email' => "export-student{$number}@example.com",
            'phone' => '1234567890',
            'course' => 'BCA',
            'age' => 20,
        ], $attributes));
    }

    private function parseCsv(string $csv): array
    {
        // Remove the optional UTF-8 BOM before parsing the first heading.
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }
}
