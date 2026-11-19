<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedStudentFilters($request);
        $students = $this->studentQuery($filters)->latest()->paginate(10)->withQueryString();
        $courses = $this->availableCourses();
        $filtersApplied = $this->filtersApplied($filters);
        $exportFilters = array_filter(
            $filters,
            static fn ($value): bool => $value !== null && $value !== '',
        );

        return view('students.index', compact(
            'students',
            'filters',
            'courses',
            'filtersApplied',
            'exportFilters',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedStudentFilters($request);
        $students = $this->studentQuery($filters)->latest()->cursor();

        return response()->streamDownload(function () use ($students): void {
            $output = fopen('php://output', 'wb');

            // The BOM helps spreadsheet apps recognize UTF-8 text correctly.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Student ID', 'Name', 'Email', 'Phone', 'Course', 'Age'], ',', '"', '', "\r\n");

            foreach ($students as $student) {
                fputcsv($output, [
                    $student->id,
                    $this->safeCsvText($student->name),
                    $this->safeCsvText($student->email),
                    $this->safeCsvText($student->phone),
                    $this->safeCsvText($student->course),
                    $student->age,
                ], ',', '"', '', "\r\n");
            }

            fclose($output);
        }, 'students.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $this->validatedStudentFilters($request);
        $students = $this->studentQuery($filters)->latest()->get();
        $search = $filters['search'];
        $filtersApplied = $this->filtersApplied($filters);

        $pdf = Pdf::loadView('students.pdf', compact('students', 'search', 'filters', 'filtersApplied'))
            ->setPaper('a4', 'landscape');

        // Render first so Dompdf's canvas is ready to stamp page numbers on every page.
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();
        $fontMetrics = $domPdf->getFontMetrics();
        $font = $fontMetrics->getFont('Helvetica', 'normal');
        $pageText = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $pageTextWidth = $fontMetrics->getTextWidth($pageText, $font, 9);

        $canvas->page_text(
            $canvas->get_width() - 32 - $pageTextWidth,
            $canvas->get_height() - 20,
            $pageText,
            $font,
            9,
            [0.35, 0.39, 0.45],
        );

        return $pdf->download('students.pdf');
    }

    private function studentQuery(array $filters): Builder
    {
        $query = Student::query();

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function (Builder $studentQuery) use ($search): void {
                $studentQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%");
            });
        }

        if ($filters['course'] !== '') {
            $query->where('course', $filters['course']);
        }

        if ($filters['min_age'] !== null) {
            $query->where('age', '>=', $filters['min_age']);
        }

        if ($filters['max_age'] !== null) {
            $query->where('age', '<=', $filters['max_age']);
        }

        return $query;
    }

    private function validatedStudentFilters(Request $request): array
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:255'],
            'course' => ['nullable', 'string', 'max:255'],
            'min_age' => ['nullable', 'integer', 'min:1'],
            'max_age' => ['nullable', 'integer', 'min:1'],
        ], [
            'search.string' => 'Search must be text.',
            'course.string' => 'Please choose a valid course.',
            'min_age.integer' => 'Minimum age must be a whole number.',
            'min_age.min' => 'Minimum age must be at least 1.',
            'max_age.integer' => 'Maximum age must be a whole number.',
            'max_age.min' => 'Maximum age must be at least 1.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $minAge = $request->query('min_age');
            $maxAge = $request->query('max_age');
            $minAge = is_string($minAge) ? filter_var($minAge, FILTER_VALIDATE_INT) : false;
            $maxAge = is_string($maxAge) ? filter_var($maxAge, FILTER_VALIDATE_INT) : false;

            if ($minAge !== false && $maxAge !== false && $minAge > $maxAge) {
                $validator->errors()->add(
                    'max_age',
                    'Maximum age must be greater than or equal to minimum age.',
                );
            }
        });

        $validated = $validator->validate();

        return [
            'search' => trim($validated['search'] ?? ''),
            'course' => $validated['course'] ?? '',
            'min_age' => $validated['min_age'] ?? null,
            'max_age' => $validated['max_age'] ?? null,
        ];
    }

    private function availableCourses()
    {
        return Student::query()
            ->whereNotNull('course')
            ->select('course')
            ->distinct()
            ->orderBy('course')
            ->pluck('course')
            ->filter(static fn ($course): bool => is_string($course) && trim($course) !== '')
            ->values();
    }

    private function filtersApplied(array $filters): bool
    {
        foreach ($filters as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    private function safeCsvText(?string $value): string
    {
        $value ??= '';

        // Prevent spreadsheet applications from evaluating untrusted text as a formula.
        if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1) {
            return "'{$value}";
        }

        return $value;
    }

    public function create(): View
    {
        return view('students.create');
    }

    public function show(Student $student): View
    {
        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', "regex:/^[\\p{L}\\s'-]+$/u"],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['nullable', 'string', 'max:15', 'regex:/^[0-9]+$/'],
            'course' => ['required', 'string', 'max:255', 'regex:/\p{L}/u'],
            'age' => ['required', 'integer', 'min:1'],
        ], [
            'name.regex' => 'The name may contain letters, spaces, hyphens, and apostrophes only.',
            'course.regex' => 'The course must contain at least one letter.',
            'phone.regex' => 'The phone number may contain digits 0-9 only.',
        ]);

        Student::create($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student registered successfully.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', "regex:/^[\\p{L}\\s'-]+$/u"],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student),
            ],
            'phone' => ['nullable', 'string', 'max:15', 'regex:/^[0-9]+$/'],
            'course' => ['required', 'string', 'max:255', 'regex:/\p{L}/u'],
            'age' => ['required', 'integer', 'min:1'],
        ], [
            'name.regex' => 'The name may contain letters, spaces, hyphens, and apostrophes only.',
            'course.regex' => 'The course must contain at least one letter.',
            'phone.regex' => 'The phone number may contain digits 0-9 only.',
        ]);

        $student->update($validated);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
