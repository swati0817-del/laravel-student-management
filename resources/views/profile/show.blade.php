@extends('layouts.admin')

@section('title', 'Admin Profile')
@section('page-heading', 'Admin profile')
@section('page-subtitle', 'View your account details')

@section('content')
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1">Admin Profile</h1>
            <p class="text-secondary mb-0">View the details associated with your admin account.</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit Profile
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success rounded-3" role="status">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-12 col-xl-8">
            <section class="card admin-card">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="metric-icon bg-primary-subtle text-primary"><i class="bi bi-person-vcard-fill" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Account details</h2>
                            <p class="small text-secondary mb-0">Your current profile information.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-secondary mb-2">Name</dt>
                        <dd class="col-sm-9 fw-semibold mb-3">{{ $admin->name }}</dd>

                        <dt class="col-sm-3 text-secondary mb-2">Email</dt>
                        <dd class="col-sm-9 fw-semibold mb-0">{{ $admin->email }}</dd>
                    </dl>
                </div>
            </section>
        </div>
    </div>
@endsection
