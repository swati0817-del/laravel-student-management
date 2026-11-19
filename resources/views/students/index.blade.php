@extends('layouts.admin')

@section('title', 'Students')
@section('page-heading', 'Students')
@section('page-subtitle', 'Manage student records and enrollment details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-primary text-uppercase fw-semibold small mb-2">Directory</p>
            <h1 class="display-6 fw-bold mb-1">Student Management</h1>
            <p class="text-secondary mb-0">Search, review, and manage your registered students.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('students.export', $exportFilters) }}" class="btn btn-outline-success rounded-pill px-4">
                <i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>
                Export CSV
            </a>
            <a href="{{ route('students.export.pdf', $exportFilters) }}" class="btn btn-outline-danger rounded-pill px-4">
                <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>
                Export PDF
            </a>
            <a href="{{ route('students.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>
                Add new student
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-3" role="alert">
            <div class="fw-semibold mb-1">Please review the following:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card admin-card mb-4">
        <div class="card-body p-3 p-lg-4">
            <form action="{{ route('students.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="search" class="form-label fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 ps-0 @error('search') is-invalid @enderror" id="search" name="search" value="{{ old('search', $filters['search']) }}" placeholder="Name, email, or course" aria-describedby="search-help">
                        @error('search') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div id="search-help" class="form-text">Search name, email, or course.</div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label for="course" class="form-label fw-semibold">Course</label>
                    <select id="course" name="course" class="form-select @error('course') is-invalid @enderror">
                        <option value="">All courses</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course }}" @selected(old('course', $filters['course']) === $course)>{{ $course }}</option>
                        @endforeach
                    </select>
                    @error('course') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label for="min_age" class="form-label fw-semibold">Minimum age</label>
                    <input type="number" min="1" step="1" inputmode="numeric" id="min_age" name="min_age" value="{{ old('min_age', $filters['min_age']) }}" class="form-control @error('min_age') is-invalid @enderror" placeholder="Any minimum">
                    @error('min_age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label for="max_age" class="form-label fw-semibold">Maximum age</label>
                    <input type="number" min="1" step="1" inputmode="numeric" id="max_age" name="max_age" value="{{ old('max_age', $filters['max_age']) }}" class="form-control @error('max_age') is-invalid @enderror" placeholder="Any maximum">
                    @error('max_age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-funnel me-1" aria-hidden="true"></i>Apply filters
                    </button>
                    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Clear filters</a>
                </div>
            </form>
        </div>
    </section>

    <section class="card admin-card overflow-hidden" aria-labelledby="student-table-heading">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 p-lg-4 pb-3">
            <div>
                <h2 id="student-table-heading" class="h5 fw-bold mb-1">Students</h2>
                <p class="small text-secondary mb-0">
                    {{ $students->total() }} {{ \Illuminate\Support\Str::plural('student', $students->total()) }} found
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-secondary small fw-semibold">Student</th>
                        <th scope="col" class="text-secondary small fw-semibold">Phone</th>
                        <th scope="col" class="text-secondary small fw-semibold">Course</th>
                        <th scope="col" class="text-secondary small fw-semibold">Age</th>
                        <th scope="col" class="text-secondary small fw-semibold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $student->name }}</div>
                                <div class="small text-secondary">{{ $student->email }}</div>
                            </td>
                            <td class="text-nowrap">{{ $student->phone ?: 'Not provided' }}</td>
                            <td><span class="badge rounded-pill bg-primary-subtle text-primary">{{ $student->course }}</span></td>
                            <td>{{ $student->age }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-secondary rounded-pill" aria-label="View details for {{ $student->name }}">
                                        <i class="bi bi-eye me-1" aria-hidden="true"></i>
                                        View Details
                                    </a>
                                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary rounded-pill" aria-label="Edit {{ $student->name }}">
                                        <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('students.destroy', $student) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" aria-label="Delete {{ $student->name }}">
                                            <i class="bi bi-trash3 me-1" aria-hidden="true"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="metric-icon bg-primary-subtle text-primary mx-auto mb-3">
                                    <i class="bi bi-people" aria-hidden="true"></i>
                                </div>
                                <h3 class="h6 fw-semibold mb-1">
                                    @if ($filtersApplied && $filters['search'] !== '')
                                        No students match "{{ $filters['search'] }}". Try another search.
                                    @elseif ($filtersApplied)
                                        No students match these filters.
                                    @else
                                        No students registered yet
                                    @endif
                                </h3>
                                <p class="text-secondary small mb-0">
                                    @if ($filtersApplied)
                                        Adjust your filters or clear them to see more students.
                                    @else
                                        Add a student to get your directory started.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="border-top px-3 px-lg-4 py-3">
                {{ $students->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
