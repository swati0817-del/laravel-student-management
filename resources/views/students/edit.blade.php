@extends('layouts.admin')

@section('title', 'Edit Student')
@section('page-heading', 'Edit student')
@section('page-subtitle', 'Update a student record')

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('students.index') }}">Students</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit student</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9 col-xxl-8">
            <div class="mb-4">
                <h1 class="h2 fw-bold mb-1">Edit student</h1>
                <p class="text-secondary mb-0">Update {{ $student->name }} student information. Fields marked with <span class="text-danger">*</span> are required.</p>
            </div>

            <section class="card admin-card">
                <div class="card-header bg-white border-0 px-4 px-lg-5 pt-4 pt-lg-5 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="metric-icon bg-primary-subtle text-primary">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Student information</h2>
                            <p class="small text-secondary mb-0">Make changes to the selected student record.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3" role="alert">
                            <div class="fw-semibold mb-1">Please correct the highlighted fields.</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('students.update', $student) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $student->name) }}" pattern="[\p{L} '-]+" maxlength="255" title="Use letters, spaces, hyphens, and apostrophes only." autocomplete="name" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $student->email) }}" maxlength="255" autocomplete="email" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone <span class="text-secondary fw-normal">(optional)</span></label>
                                <input type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" class="form-control form-control-lg rounded-3 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $student->phone) }}" autocomplete="tel">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="course" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg rounded-3 @error('course') is-invalid @enderror" id="course" name="course" value="{{ old('course', $student->course) }}" pattern=".*\p{L}.*" maxlength="255" title="Include at least one letter. Numbers and punctuation are allowed." required>
                                @error('course') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6 col-xl-5">
                                <label for="age" class="form-label fw-semibold">Age <span class="text-danger">*</span></label>
                                <input type="number" min="1" step="1" class="form-control form-control-lg rounded-3 @error('age') is-invalid @enderror" id="age" name="age" value="{{ old('age', $student->age) }}" required>
                                @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-end border-top mt-4 pt-4">
                            <a href="{{ route('students.index') }}" class="btn btn-light border rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                                Update student
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
