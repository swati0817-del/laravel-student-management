<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | {{ config('app.name', 'Student Management') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-ink: #17243a;
            --admin-accent: #5b6cf9;
            --admin-canvas: #f4f6fb;
        }

        body {
            min-height: 100vh;
            background: var(--admin-canvas);
            color: var(--admin-ink);
        }

        .admin-sidebar {
            --bs-offcanvas-width: 276px;
            background: var(--admin-ink);
            color: #fff;
        }

        .admin-brand,
        .admin-brand:hover,
        .admin-brand:focus {
            color: #fff;
            letter-spacing: -.02em;
        }

        .sidebar-label {
            color: #93a0b5;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .8rem;
            border-radius: .8rem;
            color: #c3ccda;
            font-weight: 500;
            padding: .75rem .9rem;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .sidebar-link.active {
            background: var(--admin-accent);
            box-shadow: 0 .35rem 1rem rgba(41, 57, 178, .25);
            color: #fff;
        }

        .admin-topbar {
            min-height: 76px;
        }

        .admin-card {
            border: 1px solid #e9edf4;
            border-radius: 1rem;
            box-shadow: 0 .35rem 1.2rem rgba(25, 39, 66, .045);
        }

        .metric-icon {
            align-items: center;
            border-radius: .9rem;
            display: inline-flex;
            font-size: 1.2rem;
            height: 2.9rem;
            justify-content: center;
            width: 2.9rem;
        }

        .table > :not(caption) > * > * {
            padding: .85rem 1rem;
        }

        @media (min-width: 992px) {
            .admin-sidebar {
                flex: 0 0 276px;
                min-height: 100vh;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-lg-flex min-vh-100">
        <aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header d-lg-none border-bottom border-secondary border-opacity-25">
                <span class="fw-semibold text-white">Student Management</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close menu"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-3 p-lg-4">
                <a href="{{ route('dashboard') }}" class="admin-brand d-flex align-items-center gap-2 fs-5 fw-bold mb-5 text-decoration-none" id="adminSidebarLabel">
                    <span class="metric-icon bg-white bg-opacity-10 text-white"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                    <span>Student<span class="fw-normal">Desk</span></span>
                </a>

                <div class="sidebar-label px-3 mb-2">Workspace</div>
                <nav class="nav flex-column gap-1" aria-label="Admin navigation">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('students.index') }}" class="sidebar-link {{ request()->routeIs('students.*') ? 'active' : '' }}" @if (request()->routeIs('students.*')) aria-current="page" @endif>
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                        <span>Students</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif>
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                        <span>Profile</span>
                    </a>
                </nav>

                <div class="mt-auto pt-5 px-3 small text-white-50">Student Management System</div>
            </div>
        </aside>

        <div class="flex-grow-1 min-vh-100">
            <header class="admin-topbar navbar bg-white border-bottom sticky-top">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-light border d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open navigation menu">
                            <i class="bi bi-list fs-5" aria-hidden="true"></i>
                        </button>
                        <div>
                            <div class="fw-semibold">@yield('page-heading', 'Admin')</div>
                            <div class="small text-secondary d-none d-sm-block">@yield('page-subtitle', 'Manage your student records')</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 ms-auto">
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none text-reset p-0 d-flex align-items-center gap-2 gap-sm-3" type="button" id="adminProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="text-end d-none d-sm-block">
                                    <span class="d-block small fw-semibold">{{ auth()->user()->name }}</span>
                                    <span class="d-block small text-secondary">{{ auth()->user()->email }}</span>
                                </span>
                                <span class="metric-icon bg-primary-subtle text-primary" aria-hidden="true">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <i class="bi bi-chevron-down small text-secondary" aria-hidden="true"></i>
                                <span class="visually-hidden">Open admin profile menu</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2 mt-2" aria-labelledby="adminProfileDropdown" style="min-width: 16rem;">
                                <div class="px-3 py-2">
                                    <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                                    <div class="small text-secondary text-truncate">{{ auth()->user()->email }}</div>
                                </div>
                                <hr class="dropdown-divider">
                                <a class="dropdown-item rounded-2 py-2" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person-vcard me-2 text-secondary" aria-hidden="true"></i>My Profile
                                </a>
                                <a class="dropdown-item rounded-2 py-2" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-pencil-square me-2 text-secondary" aria-hidden="true"></i>Edit Profile
                                </a>
                                <a class="dropdown-item rounded-2 py-2" href="{{ route('profile.edit') }}#change-password">
                                    <i class="bi bi-shield-lock me-2 text-secondary" aria-hidden="true"></i>Change Password
                                </a>
                                <hr class="dropdown-divider">
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-2 py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="container-fluid px-3 px-lg-4 py-4 py-xl-5">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
