@extends('layouts.admin')

@section('title', 'Admin Profile')
@section('page-heading', 'Admin profile')
@section('page-subtitle', 'Manage your account details and password')

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h1 class="h2 fw-bold mb-1">Admin profile</h1>
        <p class="text-secondary mb-0">Update the details associated with your admin account.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success rounded-3" role="status">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <section class="card admin-card h-100">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="metric-icon bg-primary-subtle text-primary"><i class="bi bi-person-vcard-fill" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Update Profile</h2>
                            <p class="small text-secondary mb-0">Your current name and email are shown below.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" value="{{ old('name', $admin->name) }}" class="form-control rounded-3 @error('name') is-invalid @enderror" autocomplete="name" maxlength="255" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" class="form-control rounded-3 @error('email') is-invalid @enderror" autocomplete="email" maxlength="255" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Save profile
                        </button>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section id="change-password" class="card admin-card h-100" style="scroll-margin-top: 6rem;">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="metric-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Change Password</h2>
                            <p class="small text-secondary mb-0">Confirm your current password first.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.password.update') }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current password <span class="text-danger">*</span></label>
                            <input id="current_password" name="current_password" type="password" class="form-control rounded-3 @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">New password <span class="text-danger">*</span></label>
                            <input id="password" name="password" type="password" class="form-control rounded-3 @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm new password <span class="text-danger">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control rounded-3" autocomplete="new-password" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-key-fill me-1" aria-hidden="true"></i>Change password
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
