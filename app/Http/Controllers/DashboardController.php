<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalStudents = Student::count();
        $totalCourses = Student::query()->distinct()->count('course');

        $courseCounts = Student::query()
            ->select('course')
            ->selectRaw('COUNT(*) as student_count')
            ->groupBy('course')
            ->orderBy('course')
            ->get();

        $courseChartLabels = $courseCounts->pluck('course')->values();
        $courseChartValues = $courseCounts
            ->pluck('student_count')
            ->map(fn ($count): int => (int) $count)
            ->values();

        $latestStudents = Student::latest()->take(5)->get();

        return view('dashboard.index', compact(
            'totalStudents',
            'totalCourses',
            'courseCounts',
            'courseChartLabels',
            'courseChartValues',
            'latestStudents',
        ));
    }
}
