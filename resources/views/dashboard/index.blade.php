@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')
@section('page-subtitle', 'A clear overview of your student records')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 mb-xl-5">
        <div>
            <p class="text-primary text-uppercase fw-semibold small mb-2">Overview</p>
            <h1 class="display-6 fw-bold mb-1">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-secondary mb-0">Here is what is happening with your students.</p>
        </div>
        <a href="{{ route('students.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
            Add student
        </a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 g-3 g-xl-4 mb-4 mb-xl-5">
        <div class="col">
            <section class="card admin-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-secondary fw-medium mb-2">Total students</p>
                        <p class="display-5 fw-bold mb-0">{{ $totalStudents }}</p>
                    </div>
                    <span class="metric-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                    </span>
                </div>
            </section>
        </div>
        <div class="col">
            <section class="card admin-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-secondary fw-medium mb-2">Distinct courses</p>
                        <p class="display-5 fw-bold mb-0">{{ $totalCourses }}</p>
                    </div>
                    <span class="metric-icon bg-success-subtle text-success">
                        <i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i>
                    </span>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 g-xl-4">
        <section class="col-12 col-xl-5" aria-labelledby="course-counts-heading">
            <div class="card admin-card h-100 overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-4 pb-3">
                        <div>
                            <h2 id="course-counts-heading" class="h5 fw-bold mb-1">Students by course</h2>
                            <p class="small text-secondary mb-0">Enrollment across each course</p>
                        </div>
                        <span class="badge rounded-pill text-bg-light">{{ $totalCourses }} courses</span>
                    </div>
                    <div class="px-4 pb-4">
                        @if ($courseChartLabels->isNotEmpty())
                            <div class="position-relative mx-auto" style="height: 280px; max-width: 420px;">
                                <canvas id="course-distribution-chart" role="img" aria-label="Doughnut chart showing student enrollment by course"></canvas>
                            </div>
                        @else
                            <div class="rounded-3 bg-light text-center text-secondary p-4" role="status">
                                <i class="bi bi-pie-chart fs-3 d-block mb-2" aria-hidden="true"></i>
                                No course enrollment data to chart yet.
                            </div>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-secondary small fw-semibold">Course</th>
                                    <th scope="col" class="text-end text-secondary small fw-semibold">Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courseCounts as $courseCount)
                                    <tr>
                                        <td class="fw-medium">{{ $courseCount->course }}</td>
                                        <td class="text-end">
                                            <span class="badge rounded-pill bg-primary-subtle text-primary">{{ $courseCount->student_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary py-5">No course data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="col-12 col-xl-7" aria-labelledby="latest-students-heading">
            <div class="card admin-card h-100 overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-4 pb-3">
                        <div>
                            <h2 id="latest-students-heading" class="h5 fw-bold mb-1">Latest students</h2>
                            <p class="small text-secondary mb-0">The five most recently registered</p>
                        </div>
                        <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-secondary small fw-semibold">Student</th>
                                    <th scope="col" class="text-secondary small fw-semibold">Course</th>
                                    <th scope="col" class="text-secondary small fw-semibold">Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestStudents as $student)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $student->name }}</div>
                                            <div class="small text-secondary">{{ $student->email }}</div>
                                        </td>
                                        <td>{{ $student->course }}</td>
                                        <td class="text-secondary text-nowrap">{{ $student->created_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-secondary py-5">No students have been registered yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @if ($courseChartLabels->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const courseChartLabels = {{ \Illuminate\Support\Js::from($courseChartLabels) }};
            const courseChartValues = {{ \Illuminate\Support\Js::from($courseChartValues) }};
            const courseChartCanvas = document.getElementById('course-distribution-chart');

            new Chart(courseChartCanvas, {
                type: 'doughnut',
                data: {
                    labels: courseChartLabels,
                    datasets: [{
                        label: 'Students',
                        data: courseChartValues,
                        backgroundColor: courseChartLabels.map((_, index) =>
                            `hsl(${Math.round(index * 360 / courseChartLabels.length)}, 68%, 58%)`
                        ),
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 18
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.label}: ${context.raw} ${context.raw === 1 ? 'student' : 'students'}`
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
