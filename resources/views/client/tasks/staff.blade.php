@extends('layouts.admin')
<style>
    .td {
        color: #9a9a9a;
    }

    .user-inactive {
        opacity: 0.6;
        background-color: #f8f9fa;
    }

    .status-badge {
        font-size: 11px;
        padding: 2px 6px;
    }
</style>

@section('content')
    <h4 class="mb-5"><b>Xodimlar ro'yxati</b></h4>

    <div class="table-container" style="max-height: 650px; overflow-y: auto;">
        <table class="table table-bordered mt-3" id="myTable2">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ismi</th>
                    <th>Berilgan loyiha</th>
                    <th>Lavozim</th>
                    <th>Status</th>
                    <th>Telefon raqam</th>
                    <th>Vazifalar</th>
                    <th>KPI natija</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                    $activeCount = 0;
                @endphp
                @foreach ($staffUsers as $index => $staffUser)
                    @php
                        $roles = $staffUser->getRoleNames();
                        if (
                            $roles->contains('Super Admin') ||
                            $roles->contains('Admin') ||
                            $roles->contains('Texnik')
                        ) {
                            continue;
                        }
                        $activeCount++;
                    @endphp

                    <tr class="user-row active-user {{ $staffUser->trashed() ? 'user-inactive' : '' }}">
                        <th>{{ $i++ }}</th>
                        <td>
                            <strong>{{ $staffUser->firstName }} {{ $staffUser->lastName }}</strong>
                            <br>
                            <small class="text-muted">{{ $staffUser->email }}</small>
                        </td>
                        <td>
                            @if ($staffUser->project)
                                <span class="badge bg-primary">{{ $staffUser->project->name }}</span>
                            @else
                                <span class="badge bg-secondary">Loyiha biriktirilmagan</span>
                            @endif
                        </td>
                        <td>
                            @if ($staffUser->position)
                                <span class="badge bg-info">{{ $staffUser->position }}</span>
                            @else
                                <span class="text-muted">Belgilanmagan</span>
                            @endif
                        </td>
                        <td>
                            @if ($staffUser->trashed())
                                <span class="badge bg-danger status-badge">
                                    <i class="fas fa-trash"></i> O'chirilgan
                                </span>
                                <br>
                                <small class="text-muted">{{ $staffUser->deleted_at->format('d.m.Y') }}</small>
                            @else
                                <span class="badge bg-success status-badge">
                                    <i class="fas fa-check-circle"></i> Faol
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($staffUser->phone)
                                <a href="tel:{{ $staffUser->phone }}" class="text-decoration-none">
                                    <i class="fas fa-phone"></i> {{ $staffUser->phone }}
                                </a>
                            @else
                                <span class="text-muted">Telefon yo'q</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('assignment.user', $staffUser->id) }}" class="btn btn-sm btn-outline-primary"
                                title="Vazifalarni ko'rish">
                                <i class="fas fa-tasks"></i> Vazifalar
                            </a>
                        </td>
                        <td>
                            @if ($staffUser->trashed())
                                {{-- O'chirilgan user uchun KPI (tarixiy) --}}
                                <a href="{{ route('accounts.staff.kpi', $staffUser->id) }}"
                                    class="btn btn-sm btn-outline-secondary" title="Tarixiy KPI ko'rish">
                                    <i class="fas fa-history"></i> KPI
                                </a>
                            @else
                                {{-- Faol user uchun KPI --}}
                                <a href="{{ route('accounts.staff.kpi', $staffUser->id) }}"
                                    class="btn btn-sm btn-outline-success" title="KPI natijalarini ko'rish">
                                    <i class="fas fa-chart-line"></i> KPI
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if ($activeCount == 0)
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Hech qanday xodim topilmadi!</strong>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        function showActiveOnly() {
            // Barcha active userlarni ko'rsatish
            document.querySelectorAll('.active-user').forEach(row => {
                row.style.display = '';
            });

            // O'chirilganlarni yashirish
            document.querySelectorAll('.user-inactive').forEach(row => {
                row.style.display = 'none';
            });

            // Statistikani yangilash
            const activeRows = document.querySelectorAll('.active-user:not(.user-inactive)').length;
            document.getElementById('totalCount').textContent = activeRows;
        }

        // DataTable initialization
        $(document).ready(function() {
            if (typeof $.fn.dataTable !== 'undefined') {
                $('#myTable2').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Uzbek.json"
                    },
                    "pageLength": 25,
                    "order": [
                        [1, "asc"]
                    ], // Ism bo'yicha
                    "columnDefs": [{
                        "orderable": false,
                        "targets": [6, 7]
                    }]
                });
            }
        });
    </script>
@endsection
