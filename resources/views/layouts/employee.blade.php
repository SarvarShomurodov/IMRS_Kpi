<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Xodim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --dark-bg: #1e2139;
            --darker-bg: #252841;
            --primary-purple: #7c5dfa;
            --secondary-purple: #9277ff;
            --success-green: #33d69f;
            --warning-orange: #ff8f00;
            --danger-red: #ec5757;
            --text-light: #dfe3fa;
            --text-muted: #888eb0;
            --card-bg: #1e2139;
            --border-color: #252841;
        }

        body {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: var(--text-light);
            font-family: 'Spartan', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 340px;
            background: linear-gradient(180deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-logo {
            background: var(--danger-red);
            color: white;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            font-weight: 800;
            font-size: 1.5rem;
            text-decoration: none;
            display: inline-block;
            letter-spacing: 1px;
        }

        .sidebar-user {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-user h6 {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .user-info {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.3rem;
        }

        .user-info i {
            width: 16px;
            margin-right: 0.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: linear-gradient(135deg, rgba(124, 93, 250, 0.1), rgba(146, 119, 255, 0.05));
            color: var(--text-light);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple));
            color: white;
            box-shadow: 0 4px 15px rgba(124, 93, 250, 0.3);
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .logout-btn {
            margin-top: 1rem;
            margin-left: 1.5rem;
            margin-right: 1.5rem;
        }

        .btn-logout {
            width: 100%;
            background: linear-gradient(135deg, var(--danger-red), #dc3545);
            border: none;
            color: white;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 87, 87, 0.3);
        }

        .content-wrapper {
            flex: 1;
            margin-left: 340px;
            min-height: 100vh;
            background: transparent;
        }

        .navbar {
            background: rgba(30, 33, 57, 0.8) !important;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-light) !important;
        }

        .nav-link {
            color: #ffffff !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--text-light) !important;
        }

        .dropdown-menu {
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 80px rgba(0, 0, 0, 0.25);
        }

        .dropdown-item {
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: var(--primary-purple);
            color: white;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--secondary-purple) 100%);
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(124, 93, 250, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(124, 93, 250, 0.3);
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            border-bottom: 1px solid var(--border-color);
            border-radius: 16px 16px 0 0 !important;
            color: var(--text-light);
            font-weight: 600;
        }

        .attendance-card {
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--success-green);
            border-radius: 12px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .attendance-card:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .attendance-card.absent {
            border-left-color: var(--danger-red);
            background: linear-gradient(135deg, rgba(236, 87, 87, 0.1) 0%, var(--darker-bg) 100%);
        }

        .attendance-card.late {
            border-left-color: var(--warning-orange);
            background: linear-gradient(135deg, rgba(255, 143, 0, 0.1) 0%, var(--darker-bg) 100%);
        }

        .badge-custom {
            font-size: 0.85rem;
            padding: 0.6rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .bg-success {
            background: linear-gradient(135deg, #324640, #203f27) !important;
        }

        .bg-danger {
            background: linear-gradient(135deg, var(--danger-red), #dc3545) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, var(--warning-orange), #ffc107) !important;
        }

        .bg-info {
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple)) !important;
        }

        .bg-secondary {
            background: linear-gradient(135deg, #6c757d, #495057) !important;
        }

        .alert {
            border: none;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(51, 214, 159, 0.2), rgba(40, 167, 69, 0.2));
            border-left: 4px solid var(--success-green);
            color: var(--success-green);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(236, 87, 87, 0.2), rgba(220, 53, 69, 0.2));
            border-left: 4px solid var(--danger-red);
            color: var(--danger-red);
        }

        .text-success {
            color: var(--success-green) !important;
        }

        .text-warning {
            color: var(--warning-orange) !important;
        }

        .text-danger {
            color: var(--danger-red) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .border {
            border-color: var(--border-color) !important;
        }

        .bg-warning.bg-opacity-25 {
            background: linear-gradient(135deg, rgba(255, 143, 0, 0.15), rgba(255, 193, 7, 0.1)) !important;
            border: 1px solid rgba(255, 143, 0, 0.2);
        }

        .bg-info.bg-opacity-25 {
            background: linear-gradient(135deg, rgba(124, 93, 250, 0.15), rgba(146, 119, 255, 0.1)) !important;
            border: 1px solid rgba(124, 93, 250, 0.2);
        }

        .bg-secondary.bg-opacity-25 {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.15), rgba(73, 80, 87, 0.1)) !important;
            border: 1px solid rgba(108, 117, 125, 0.2);
        }

        h3,
        h4,
        h5 {
            color: var(--text-light);
            font-weight: 600;
        }

        small {
            color: var(--text-muted);
        }

        .pagination {
            --bs-pagination-bg: var(--darker-bg);
            --bs-pagination-border-color: var(--border-color);
            --bs-pagination-color: var(--text-light);
            --bs-pagination-hover-bg: var(--primary-purple);
            --bs-pagination-hover-border-color: var(--primary-purple);
            --bs-pagination-hover-color: white;
            --bs-pagination-active-bg: var(--primary-purple);
            --bs-pagination-active-border-color: var(--primary-purple);
        }

        .btn-close {
            filter: invert(1);
        }

        /* Responsive design */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content-wrapper {
                margin-left: 0;
            }
        }

        .user-info-nav {
            text-align: right;
        }

        .user-info-nav .fw-bold {
            font-size: 0.9rem;
        }

        .user-info-nav small {
            font-size: 0.75rem;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-purple);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-purple);
        }

        /* Animation keyframes */
        @keyframes slideInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .attendance-card {
            animation: slideInUp 0.6s ease forwards;
        }

        .stats-card {
            animation: slideInUp 0.4s ease forwards;
        }

        /* Empty state styling */
        .empty-state {
            color: var(--text-muted);
        }

        .empty-state i {
            color: var(--primary-purple);
            opacity: 0.7;
        }

        /* Toolbar backgroundni oq qilish */
        .note-toolbar {
            background-color: #fff !important;
        }

        /* Toolbar ichidagi iconlarni qora emas oq qilish */
        .note-toolbar .btn,
        .note-toolbar .dropdown-toggle,
        .note-toolbar .note-btn {
            color: #000 !important;
            /* qora */
        }

        /* Agar oq qilib chiqarmoqchi bo'lsangiz */
        .note-toolbar .btn i,
        .note-toolbar .dropdown-toggle i {
            color: #000000 !important;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Logo -->
            <div class="sidebar-header">
                <img class="mt-3" style="width: 45%" src="{{ asset('admin/images/screen.png') }}" alt="logo" />
            </div>

            <!-- User Info -->
            <div class="sidebar-user">
                <h6>Xodimlar uchun</h6>
                <div class="user-info">
                    <i class="bi bi-envelope"></i> Xodim emaili: <b>{{ auth()->user()->email }}</b>
                </div>
                <div class="user-info">
                    <i class="bi bi-person-badge"></i> Xodim IDsi: <b>{{ auth()->user()->id }}</b>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <!-- Navigation -->
                <nav class="sidebar-nav">
                    <div class="nav-item">
                        <a href="{{ route('employee.attendance.index') }}"
                            class="nav-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i>
                            Davomatlar Tarixi
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('employee.reports.index') }}"
                            class="nav-link {{ request()->routeIs('employee.reports.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            Xisobotlar
                        </a>
                    </div>
                </nav>
            </nav>

            <!-- Logout Button -->
            <div class="logout-btn">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-logout">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Chiqish
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link text-light me-3 d-lg-none" type="button" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <h4 class="mb-0 text-light">@yield('title', 'Dashboard')</h4>
                    </div>

                    <div class="navbar-nav ms-auto">
                        <div class="nav-item">
                            <div class="d-flex align-items-center" style="margin-right: 30px">
                                <div class="user-avatar me-2">
                                    <i class="bi bi-person-circle fs-4" style="margin-right: 0rem;"></i>
                                </div>
                                <div class="user-info-nav">
                                    <div class="fw-bold">{{ auth()->user()->full_name ?? 'Xodim' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        // Auto-hide sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');

            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 992) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
