<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Xodimlar KPI natijalari</title>
	<link rel="icon" href="{{ asset('admin/images/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('client/css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            @include('layouts.inc.client.sidebar')
            <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 content">
                <div class="d-flex gap-3 flex-wrap">
                <a href="{{ route('client.index',['year' => request('year'), 'month' => request('month')]) }}"
                    class="btn btn-outline-light {{ request()->routeIs('client.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> DATASET
                 </a>               
                 <a href="{{ route('client.subtask', ['year' => request('year'), 'month' => request('month')]) }}"
                    class="btn btn-outline-light {{ request()->routeIs('client.subtask') ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i> ALL DATA FOR EACH SUBTASK
                 </a>                 
                 <a href="{{ route('client.allsubtask',['year' => request('year'), 'month' => request('month')]) }}"
                    class="btn btn-outline-light {{ request()->routeIs('client.allsubtask') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> REYTING
                 </a>                   
                 <a href="{{ route('assignments.index',['year' => request('year'), 'month' => request('month')]) }}"
                    class="btn btn-outline-light {{ request()->routeIs('assignments*') ? 'active' : '' }}">
                    <i class="fas fa-tasks"></i> VAZIFALAR
                 </a> 
                 <a href="{{ route('profile.edit',['year' => request('year'), 'month' => request('month')]) }}"
                    class="btn btn-outline-light {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <i class="fas fa-cogs"></i> PROFIL MA'LUMOTLARI
                 </a> 
                </div>
                <hr />
                @yield('content')
                @yield('scripts')
            </main>
        </div>
    </div> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
     $(document).ready(function() {
        $('table[id^="myTable_"]').each(function() {
            $(this).DataTable({
                ordering: true,
                order: [[0, 'asc']],
                paging: false,
                lengthChange: false,
                language: {
                    search: "Qidiruv:",
                    zeroRecords: "Hech qanday mos yozuv topilmadi",
                },
                dom: 'Bfrtip',
                buttons: ['excel', 'pdf', 'print'],
                info: false
            });
        });
    });
    $(document).ready(function() {
            $('#myTable2').DataTable({
                ordering: true,
                order: [[0, 'asc']],
                paging: true, // Sahifalashni yoqish
                pageLength: 10,
                lengthChange: false,
                language: {
                    search: "Qidiruv:",
                    info: "_TOTAL_ ta yozuvdan _START_ dan _END_ gacha ko'rsatilmoqda",
                    zeroRecords: "Hech qanday mos yozuv topilmadi",
                },
                dom: 'Bfrtip', // Buttonsni qo'shish uchun
                buttons: [
                    'excel', 'pdf', 'print'
                ]
            });
            $('#myTable2 td, #myTable2 th').css('font-size', '15px');
        });
    </script>
</body>

</html>
        