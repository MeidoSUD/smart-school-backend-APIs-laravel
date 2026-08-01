<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart School ERP')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-width: 250px; --primary: #4361ee; --sidebar-bg: #1a1d2e; --sidebar-text: #a4a6b3; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; }
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); color: var(--sidebar-text); z-index: 1000; overflow-y: auto; transition: all 0.3s; }
        .sidebar .nav-link { color: var(--sidebar-text); padding: 10px 20px; border-radius: 8px; margin: 2px 10px; font-size: 14px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(67,97,238,0.2); color: #fff; }
        .sidebar .nav-link i { width: 25px; text-align: center; margin-right: 8px; }
        .sidebar .brand { padding: 20px; font-size: 18px; font-weight: 700; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .section-title { padding: 15px 20px 5px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        .main-content { margin-left: var(--sidebar-width); padding: 20px 30px; }
        .topbar { background: #fff; padding: 12px 30px; margin: -20px -30px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .card-stat .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="brand"><i class="fas fa-graduation-cap me-2"></i>Smart School</div>
        <nav class="mt-2">
            <div class="section-title">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i>Dashboard</a>

            <div class="section-title">Academics</div>
            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i>Students</a>
            <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i>Classes</a>
            <a href="{{ route('admin.sections.index') }}" class="nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}"><i class="fas fa-object-group"></i>Sections</a>
            <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book"></i>Subjects</a>
            <a href="{{ route('admin.exams.index') }}" class="nav-link {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i>Exams</a>
            <a href="{{ route('admin.homework.index') }}" class="nav-link {{ request()->routeIs('admin.homework.*') ? 'active' : '' }}"><i class="fas fa-tasks"></i>Homework</a>

            <div class="section-title">People</div>
            <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i>Staff</a>

            <div class="section-title">Finance</div>
            <a href="{{ route('admin.fee-masters.index') }}" class="nav-link {{ request()->routeIs('admin.fee-masters.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i>Fees</a>
            <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}"><i class="fas fa-receipt"></i>Expenses</a>
            <a href="{{ route('admin.incomes.index') }}" class="nav-link {{ request()->routeIs('admin.incomes.*') ? 'active' : '' }}"><i class="fas fa-coins"></i>Income</a>

            <div class="section-title">Operations</div>
            <a href="{{ route('admin.books.index') }}" class="nav-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}"><i class="fas fa-book-reader"></i>Library</a>
            <a href="{{ route('admin.hostels.index') }}" class="nav-link {{ request()->routeIs('admin.hostels.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i>Hostel</a>
            <a href="{{ route('admin.vehicles.index') }}" class="nav-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}"><i class="fas fa-bus"></i>Transport</a>

            <div class="section-title">System</div>
            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="fas fa-user-shield"></i>Roles</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div><h5 class="mb-0">@yield('page-title', 'Dashboard')</h5></div>
            <div class="d-flex align-items-center">
                <span class="me-3">{{ Auth::user()->email ?? 'Admin' }}</span>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle text-dark text-decoration-none" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
