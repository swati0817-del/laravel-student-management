@extends('layouts.admin')

@section('title', 'Student Details')
@section('page-heading', 'Student details')
@section('page-subtitle', 'Review this student record')

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('students.index') }}">Students</a></li>
            <li class="breadcrumb-item active" aria-current="page">Student details</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-primary text-uppercase fw-semibold small mb-2">Student record #{{ $student->id }}</p>
            <h1 class="h2 fw-bold mb-1">{{ $student->name }}</h1>
            <p class="text-secondary mb-0">Review the student's registration and contact information.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to Students
            </a>
            <a href="{{ route('students.edit', $student) }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit Student
            </a>
        </div>
    </div>

    <section class="card admin-card overflow-hidden" aria-labelledby="student-details-heading">
        <div class="card-header bg-white border-0 p-4 p-lg-5 pb-0">
            <div class="d-flex align-items-center gap-3">
                <span class="metric-icon bg-primary-subtle text-primary">
                    <i class="bi bi-person-vcard-fill" aria-hidden="true"></i>
                </span>
                <div>
                    <h2 id="student-details-heading" class="h5 fw-bold mb-1">Student information</h2>
                    <p class="small text-secondary mb-0">Registration details and record timestamps.</p>
                </div>
            </div>
        </div>

        <div class="card-body p-4 p-lg-5">
            <dl class="row g-3 mb-0">
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Student ID</dt>
                    <dd class="mb-0 fw-semibold">{{ $student->id }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Name</dt>
                    <dd class="mb-0 fw-semibold">{{ $student->name }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Email</dt>
                    <dd class="mb-0"><a class="text-decoration-none" href="mailto:{{ $student->email }}">{{ $student->email }}</a></dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Phone</dt>
                    <dd class="mb-0">{{ $student->phone ?: 'Not provided' }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Course</dt>
                    <dd class="mb-0"><span class="badge rounded-pill bg-primary-subtle text-primary">{{ $student->course }}</span></dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Age</dt>
                    <dd class="mb-0">{{ $student->age }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Created</dt>
                    <dd class="mb-0">{{ $student->created_at?->format('M j, Y, g:i A') ?? 'Not available' }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="small text-secondary fw-semibold text-uppercase mb-1">Last updated</dt>
                    <dd class="mb-0">{{ $student->updated_at?->format('M j, Y, g:i A') ?? 'Not available' }}</dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
