<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Xodimlar KPI natijalari</title>
    <link rel="icon" href="{{ asset('admin/images/logo.png') }}" type="image/png">
    <script>
        (function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('client/css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/main-content.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/assignments.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/assignments-list.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/dataset.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/charts.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/assignment-show.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/dark-mode.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/pagination.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            @include('layouts.inc.client.sidebar')
            <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 content">
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('client.index', ['year' => request('year'), 'month' => request('month')]) }}"
                        class="btn btn-outline-light {{ request()->routeIs('client.index') ? 'active' : '' }}">
                        <i class="fas fa-list"></i> DATASET
                    </a>

                    <a href="{{ route('client.subtask', ['year' => request('year'), 'month' => request('month')]) }}"
                        class="btn btn-outline-light {{ request()->routeIs('client.subtask') ? 'active' : '' }}">
                        <i class="fas fa-folder-open"></i> ALL DATA FOR EACH SUBTASK
                    </a>

                    <a href="{{ route('client.allsubtask', ['year' => request('year'), 'month' => request('month')]) }}"
                        class="btn btn-outline-light {{ request()->routeIs('client.allsubtask') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> REYTING
                    </a>

                    <a href="{{ route('assignments.index', ['year' => request('year'), 'month' => request('month')]) }}"
                        class="btn btn-outline-light {{ request()->routeIs('assignments*') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i> VAZIFALAR
                    </a>

                    <a href="{{ route('profile.edit', ['year' => request('year'), 'month' => request('month')]) }}"
                        class="btn btn-outline-light {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="fas fa-cogs"></i> PROFIL MA'LUMOTLARI
                    </a>
                    <button id="darkModeToggle" class="btn btn-outline-light">
                        <i class="fas fa-moon"></i> <span id="modeText">Tungi rejim</span>
                    </button>
                </div>
                <hr />
                {{-- <div class="info-banner">
                    <div class="info-banner-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="info-banner-content">
                        <span class="info-banner-text">
                            Ushbu sayt dizayni yangilangan. Qandaydir xatoliklar yuzaga kelsa ma'lumot bering
                        </span>
                    </div>
                </div> --}}
                @yield('content')
                @yield('scripts')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('client/js/dark-mode.js') }}"></script>
    <script src="{{ asset('client/js/datatables-init.js') }}"></script>
</body>

</html>
        