@extends('layouts.admin')

@section('content')
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <strong>{{ $staffUser->firstName }} {{ $staffUser->lastName }}</strong> - Jarimalar
                </h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-calculator"></i>
                    Jami jarima bali:
                    <span class="badge bg-danger fs-6">{{ number_format($totalPenaltyScore, 2) }}</span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('assignment.user', $staffUser->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Vazifalar ro'yxatiga
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addPenaltyModal">
                    <i class="fas fa-plus"></i> Jarima qo'shish
                </button>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('assignment.user.penalties', $staffUser->id) }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Sanadan</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sanagacha</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Qidirish
                        </button>
                        <a href="{{ route('assignment.user.penalties', $staffUser->id) }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Tozalash
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" @if ($penalties->count() > 0) id="myTable2" @endif>
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">№</th>
                                <th>Jarima turi</th>
                                <th style="width: 100px;">Ball</th>
                                <th style="width: 120px;">Sana</th>
                                <th>Izoh</th>
                                <th style="width: 150px;">Amallar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penalties as $index => $penalty)
                                <tr>
                                    <td>{{ ($penalties->currentPage() - 1) * $penalties->perPage() + $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            {{ $penalty->subtask->title }}
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            Chegara: {{ $penalty->subtask->min }} - {{ $penalty->subtask->max }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fs-6">{{ $penalty->rating }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($penalty->addDate)->format('d.m.Y') }}</td>
                                    <td>
                                        <small>{{ Str::limit($penalty->comment, 50) }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-penalty"
                                                data-id="{{ $penalty->id }}" data-subtask-id="{{ $penalty->subtask_id }}"
                                                data-rating="{{ $penalty->rating }}"
                                                data-comment="{{ $penalty->comment }}" data-date="{{ $penalty->addDate }}"
                                                data-bs-toggle="modal" data-bs-target="#editPenaltyModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form
                                                action="{{ route('assignment.user.penalties.delete', [$staffUser->id, $penalty->id]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Jarimani o\'chirmoqchimisiz?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Hozircha jarimalar yo'q
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($penalties->count() > 0)
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="2" class="text-end">JAMI:</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-danger fs-6">{{ number_format($totalPenaltyScore, 2) }}</span>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $penalties->appends(request()->query())->links('custom.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Penalty Modal -->
    <div class="modal fade" id="addPenaltyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('assignment.user.penalties.store', $staffUser->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle"></i>
                            {{ $staffUser->firstName }} {{ $staffUser->lastName }} uchun jarima qo'shish
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Jarima turi -->
                        <div class="mb-3">
                            <label class="form-label">Jarima turi <span class="text-danger">*</span></label>
                            <select name="subtask_id" id="addSubtaskSelect" class="form-select" required>
                                <option value="">Tanlang</option>
                                @foreach ($penaltySubtasks as $subtask)
                                    <option value="{{ $subtask->id }}" data-min="{{ $subtask->min }}"
                                        data-max="{{ $subtask->max }}">
                                        {{ $subtask->title }} ({{ $subtask->min }} - {{ $subtask->max }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="addRatingRange"></small>
                        </div>

                        <!-- Ball -->
                        <div class="mb-3">
                            <label class="form-label">Jarima bali <span class="text-danger">*</span></label>
                            <input type="number" name="rating" id="addRatingInput" class="form-control"
                                step="0.01" required>
                        </div>

                        <!-- Sana -->
                        <div class="mb-3">
                            <label class="form-label">Sana <span class="text-danger">*</span></label>
                            <input type="date" name="addDate" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <!-- Izoh -->
                        <div class="mb-3">
                            <label class="form-label">Izoh</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Qo'shimcha izoh..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save"></i> Saqlash
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Penalty Modal -->
    <div class="modal fade" id="editPenaltyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPenaltyForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Jarimani tahrirlash
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Jarima turi -->
                        <div class="mb-3">
                            <label class="form-label">Jarima turi <span class="text-danger">*</span></label>
                            <select name="subtask_id" id="editSubtaskSelect" class="form-select" required>
                                <option value="">Tanlang</option>
                                @foreach ($penaltySubtasks as $subtask)
                                    <option value="{{ $subtask->id }}" data-min="{{ $subtask->min }}"
                                        data-max="{{ $subtask->max }}">
                                        {{ $subtask->title }} ({{ $subtask->min }} - {{ $subtask->max }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="editRatingRange"></small>
                        </div>

                        <!-- Ball -->
                        <div class="mb-3">
                            <label class="form-label">Jarima bali <span class="text-danger">*</span></label>
                            <input type="number" name="rating" id="editRatingInput" class="form-control"
                                step="0.01" required>
                        </div>

                        <!-- Sana -->
                        <div class="mb-3">
                            <label class="form-label">Sana <span class="text-danger">*</span></label>
                            <input type="date" name="addDate" id="editDateInput" class="form-control" required>
                        </div>

                        <!-- Izoh -->
                        <div class="mb-3">
                            <label class="form-label">Izoh</label>
                            <textarea name="comment" id="editCommentInput" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Yangilash
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add modal - subtask tanlanganda min/max ko'rsatish
            document.getElementById('addSubtaskSelect').addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const min = option.dataset.min;
                const max = option.dataset.max;
                const ratingInput = document.getElementById('addRatingInput');
                const ratingRange = document.getElementById('addRatingRange');

                if (min && max) {
                    ratingRange.textContent = `Ball chegarasi: ${min} - ${max}`;
                    ratingInput.min = min;
                    ratingInput.max = max;
                } else {
                    ratingRange.textContent = '';
                    ratingInput.removeAttribute('min');
                    ratingInput.removeAttribute('max');
                }
            });

            // Edit modal - subtask tanlanganda min/max ko'rsatish
            document.getElementById('editSubtaskSelect').addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const min = option.dataset.min;
                const max = option.dataset.max;
                const ratingInput = document.getElementById('editRatingInput');
                const ratingRange = document.getElementById('editRatingRange');

                if (min && max) {
                    ratingRange.textContent = `Ball chegarasi: ${min} - ${max}`;
                    ratingInput.min = min;
                    ratingInput.max = max;
                } else {
                    ratingRange.textContent = '';
                    ratingInput.removeAttribute('min');
                    ratingInput.removeAttribute('max');
                }
            });

            // Edit button click - modal ma'lumotlarini to'ldirish
            document.querySelectorAll('.btn-edit-penalty').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const subtaskId = this.dataset.subtaskId;
                    const rating = this.dataset.rating;
                    const comment = this.dataset.comment;
                    const date = this.dataset.date;

                    // Form action ni o'zgartirish
                    document.getElementById('editPenaltyForm').action =
                        `/assignment/user/{{ $staffUser->id }}/penalties/${id}/update`;

                    // Ma'lumotlarni to'ldirish
                    document.getElementById('editSubtaskSelect').value = subtaskId;
                    document.getElementById('editRatingInput').value = rating;
                    document.getElementById('editDateInput').value = date;
                    document.getElementById('editCommentInput').value = comment;

                    // Subtask o'zgarishini trigger qilish (min/max ko'rsatish uchun)
                    document.getElementById('editSubtaskSelect').dispatchEvent(new Event('change'));
                });
            });
        });
    </script>

    <style>
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .btn-group {
            display: flex;
            gap: 0;
        }

        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
    </style>
@endsection
