@extends('layouts.admin')

<style>
    .td {
        color: #9a9a9a;
    }
</style>

@section('title', 'Xodimlar Davomat Nazorati')

@section('content')
    <h4 class="mb-5"><b>Texnik Xodimlar Davomat Nazorati</b></h4>

    <div class="table-container" style="max-height: 650px; overflow-y: auto;">
        <table class="table table-bordered mt-5" id="myTable2">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ismi</th>
                    <th>Email</th>
                    <th>Xodim bo’limi</th>
					@if(!in_array(Auth::user()->email, config('admin.restricted_report_emails', [])))
                        <th>Xisobotlari</th>
                    @endif
                    <th>Bugungi davomat</th>
                    <th>Boshqa kun</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach ($users as $user)
                    <tr>
                        <th>{{ $i++ }}</th>
                        <td><strong>{{ $user->firstName }} {{ $user->lastName }}</strong></td>
                        <td class="td">{{ $user->email }}</td>
                        <td class="td">{{ $user->project ? $user->project->name : 'Loyiha biriktirilmagan' }}</td>
                		@if(!in_array(Auth::user()->email, config('admin.restricted_report_emails', [])))
                            <td>
                                <a href="{{ route('admin.reports.index', ['user_id' => $user->id]) }}" 
                                   class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file-alt"></i> Xisobotlarini ko'rish
                                </a>
                            </td>
                        @endif
                        <td>
                            <a href="{{ route('admin.attendance.show', ['user' => $user->id, 'date' => $today]) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-clock"></i> Bugungi davomat
                            </a>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" onclick="showDatePicker({{ $user->id }})">
                                <i class="fas fa-calendar"></i> Boshqa kun
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($users->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                <h4 class="text-muted">Texnik xodimlar topilmadi</h4>
                <p class="text-muted">Avval Texnik rolida foydalanuvchilar yarating</p>
            </div>
        @endif
    </div>

    <!-- Sana tanlash modali -->
    <div class="modal fade" id="datePickerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sanani tanlang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="datePickerForm">
                        <div class="mb-3">
                            <label for="selectedDate" class="form-label">Sana</label>
                            <input type="date" class="form-control" id="selectedDate" value="{{ $today }}"
                                max="{{ $today }}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="button" class="btn btn-primary" onclick="goToSelectedDate()">O'tish</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let selectedUserId = null;

        function showDatePicker(userId) {
            selectedUserId = userId;
            const modal = new bootstrap.Modal(document.getElementById('datePickerModal'));
            modal.show();
        }

        function goToSelectedDate() {
            const selectedDate = document.getElementById('selectedDate').value;
            if (selectedUserId && selectedDate) {
                window.location.href = `/admin/attendance/${selectedUserId}?date=${selectedDate}`;
            }
        }
    </script>
@endpush