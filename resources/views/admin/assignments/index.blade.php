@extends('layouts.admin')

@section('content')
    <div class="mt-4"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-5"><strong>{{ $staffUser->firstName }} {{ $staffUser->lastName }}</strong> - vazifalar ro'yxati</h4>
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('User'))
            <a href="{{ route('accounts.staffs') }}" class="btn btn-info">Orqaga</a>
        @endif
    </div>

    <!-- Filter tugmalari -->
    <div class="mb-3 d-flex gap-2">
        @if (!request()->has('show_all'))
            <a href="{{ route('assignment.user', ['id' => $staffUser->id, 'show_all' => 1]) }}" class="btn btn-success">
                <i class="fas fa-eye"></i> Hammasini ko'rish
            </a>
        @else
            <a href="{{ route('assignment.user', ['id' => $staffUser->id]) }}" class="btn btn-primary">
                <i class="fas fa-filter"></i> Baxolanmaganlar
            </a> 
        @endif
        <!-- ✅ YANGI - Jarima tugmasi (faqat shu xodim uchun) -->
        <a href="{{ route('assignment.user.penalties', $staffUser->id) }}" class="btn btn-danger">
            <i class="fas fa-exclamation-triangle"></i> Jarimalarni baxolash
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-container" style="max-height: 650px; overflow-y: auto;">
        <table class="table-bordered" id="myTable2">
            <thead>
                <tr>
                    <th style="width: 40px;">№</th>
                    <th style="width: 150px;" class="truncate-header" data-bs-toggle="tooltip"
                        title="Бажарилган топшириқ номи">
                        Бажарил...
                    </th>
                    <th style="width: 120px;" class="truncate-header" data-bs-toggle="tooltip"
                        title="Ким томонидан берилди">
                        Ким томо...
                    </th>
                    <th style="width: 100px;" class="truncate-header" data-bs-toggle="tooltip"
                        title="Материални топширган санаси (якуний вариант)">
                        Материал...
                    </th>
                    <th style="width: 100px;" class="truncate-header" data-bs-toggle="tooltip" title="Кимга топширилди">
                        Кимга то...
                    </th>
                    <th style="width: 120px;" class="truncate-header" data-bs-toggle="tooltip"
                        title="Лойиҳадаги ижрочилар ва ҳиссалар">
                        Лойиҳада...
                    </th>
                    <th style="width: 60px;">Fayl</th>
                    <th style="width: 120px;" class="truncate-header" data-bs-toggle="tooltip" title="Izoh">
                        Izoh
                    </th>
                    <th style="width: 220px;">Task</th>
                    <th style="width: 180px;">Subtask</th>
                    <th style="width: 100px;">Baho</th>
                    <th style="width: 200px;">Comment</th>
                </tr>
            </thead>
            <tbody>
                @php $index = 0; @endphp
                @if (isset($assignments) && count($assignments) > 0)
                    @foreach ($assignments as $assignment)
                        @php $index++; @endphp
                        <tr data-assignment-id="{{ $assignment->id }}">
                            <td>
                                @if (isset($showPagination) && $showPagination)
                                    {{ ($assignments->currentPage() - 1) * $assignments->perPage() + $index }}
                                @else
                                    {{ $index }}
                                @endif
                            </td>

                            <!-- Topshiriq nomi -->
                            <td>
                                <div class="text-content" data-bs-toggle="tooltip" data-bs-html="true"
                                    title="{{ $assignment->name }}">
                                    {{ Str::limit($assignment->name, 20) }}
                                </div>
                            </td>

                            <!-- Kim berdi -->
                            <td>
                                <div class="text-content" data-bs-toggle="tooltip" data-bs-html="true"
                                    title="{{ $assignment->who_from }}">
                                    {{ Str::limit($assignment->who_from, 15) }}
                                </div>
                            </td>

                            <!-- Sana -->
                            <td>{{ $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d M Y') : 'N/A' }}
                            </td>

                            <!-- Kimga topshirildi -->
                            <td>
                                <div class="text-content" data-bs-toggle="tooltip" data-bs-html="true"
                                    title="{{ $assignment->who_hand }}">
                                    {{ Str::limit($assignment->who_hand, 15) }}
                                </div>
                            </td>

                            <!-- Ijrochilar -->
                            <td>
                                <div class="text-content" data-bs-toggle="tooltip" data-bs-html="true"
                                    title="{{ $assignment->people }}">
                                    {{ Str::limit($assignment->people, 15) }}
                                </div>
                            </td>

                            <!-- Fayl -->
                            <td class="text-center">
                                @if ($assignment->file)
                                    <a href="{{ asset('storage/assignments/' . basename($assignment->file)) }}"
                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Izoh -->
                            <td>
                                <div class="text-content" data-bs-toggle="tooltip" data-bs-html="true"
                                    title="{{ $assignment->comment }}">
                                    {{ Str::limit($assignment->comment, 15) }}
                                </div>
                            </td>

                            <!-- Task select -->
                            <td class="task-cell">
                                @if ($assignment->task_id)
                                    <div class="task-display">
                                        <span class="badge bg-primary" data-task-id="{{ $assignment->task_id }}">
                                            {{ $assignment->task->taskName ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <select class="form-select form-select-sm task-select d-none" name="task_id"
                                        data-row="{{ $assignment->id }}">
                                        <option value="">Tanlang</option>
                                        @foreach ($tasks as $task)
                                            <option value="{{ $task->id }}"
                                                {{ $assignment->task_id == $task->id ? 'selected' : '' }}>
                                                {{ $task->taskName }}
                                            </option>
                                        @endforeach
                                        {{-- ✅ Agar bu assignment 11-taskdan foydalangan bo'lsa, uni ko'rsatish --}}
                                        @if ($assignment->task_id == 11 && $task11)
                                            <option value="11" selected>{{ $task11->taskName }}</option>
                                        @endif
                                    </select>
                                @else
                                    <select class="form-select form-select-sm task-select" name="task_id"
                                        data-row="{{ $assignment->id }}">
                                        <option value="">Tanlang</option>
                                        @foreach ($tasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->taskName }}</option>
                                        @endforeach
                                        {{-- ✅ Agar bu assignment 11-taskdan foydalanishi mumkin bo'lsa --}}
                                        @if (in_array($assignment->id, $assignmentsWithTask11) && $task11)
                                            <option value="11">{{ $task11->taskName }}</option>
                                        @endif
                                    </select>
                                @endif
                            </td>

                            <!-- Subtask select -->
                            <td class="subtask-cell">
                                @if ($assignment->subtask_id)
                                    <div class="subtask-display">
                                        <span class="badge bg-info" data-bs-toggle="tooltip"
                                            data-subtask-id="{{ $assignment->subtask_id }}"
                                            data-min="{{ $assignment->subtask->min }}"
                                            data-max="{{ $assignment->subtask->max }}"
                                            title="{{ $assignment->subtask->title }} ({{ $assignment->subtask->min }}-{{ $assignment->subtask->max }})">
                                            {{ Str::limit($assignment->subtask->title, 20) }}
                                        </span>
                                    </div>
                                    <div class="d-none subtask-edit">
                                        <select class="form-select form-select-sm subtask-select" name="subtask_id"
                                            data-row="{{ $assignment->id }}">
                                            <option value="">Subtask tanlang</option>
                                        </select>
                                        <small class="text-muted rating-range" data-row="{{ $assignment->id }}"></small>
                                    </div>
                                @else
                                    <select class="form-select form-select-sm subtask-select" name="subtask_id"
                                        data-row="{{ $assignment->id }}" disabled>
                                        <option value="">Task tanlang</option>
                                    </select>
                                    <small class="text-muted rating-range" data-row="{{ $assignment->id }}"></small>
                                @endif
                            </td>

                            <!-- Baho -->
                            <td class="rating-cell">
                                @if ($assignment->rating)
                                    <div class="rating-display">
                                        <span class="badge bg-success fs-6" data-rating="{{ $assignment->rating }}">
                                            {{ $assignment->rating }}
                                        </span>
                                    </div>
                                    <input type="number" class="form-control form-control-sm rating-input d-none"
                                        name="rating" step="0.01" placeholder="Ball"
                                        value="{{ $assignment->rating }}" data-row="{{ $assignment->id }}"
                                        style="width: 90px;">
                                @else
                                    <input type="number" class="form-control form-control-sm rating-input"
                                        name="rating" step="0.01" placeholder="Ball"
                                        data-row="{{ $assignment->id }}" style="width: 90px;">
                                @endif
                            </td>

                            <!-- Comment / Amallar -->
                            <td class="comment-cell">
                                @if ($assignment->rating)
                                    <div class="comment-display">
                                        <div class="d-flex flex-column gap-1">
                                            @if ($assignment->custom_comment)
                                                <small
                                                    class="text-muted mb-1">{{ Str::limit($assignment->custom_comment, 30) }}</small>
                                            @endif
                                            <span class="badge bg-success mb-1">
                                                <i class="fas fa-check-circle"></i> Baholangan
                                            </span>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning btn-edit"
                                                    data-row="{{ $assignment->id }}" style="font-size: 0.65rem;">
                                                    <i class="fas fa-edit"></i> O'zgartirish
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-row="{{ $assignment->id }}" style="font-size: 0.65rem;">
                                                    <i class="fas fa-trash"></i> O'chirish
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment-edit d-none">
                                        <div class="d-flex flex-column gap-1">
                                            <textarea class="form-control form-control-sm comment-input" rows="2" placeholder="Izoh (opsional)"
                                                data-row="{{ $assignment->id }}" style="resize: none; font-size: 0.7rem;">{{ $assignment->custom_comment }}</textarea>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-success btn-save"
                                                    data-row="{{ $assignment->id }}" style="font-size: 0.7rem;">
                                                    <i class="fas fa-check"></i> Saqlash
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary btn-cancel"
                                                    data-row="{{ $assignment->id }}" style="font-size: 0.7rem;">
                                                    <i class="fas fa-times"></i> Bekor
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex flex-column gap-1">
                                        <textarea class="form-control form-control-sm comment-input" rows="2" placeholder="Izoh (opsional)"
                                            data-row="{{ $assignment->id }}" style="resize: none; font-size: 0.7rem;"></textarea>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-success btn-accept"
                                                data-row="{{ $assignment->id }}" style="font-size: 0.7rem;">
                                                <i class="fas fa-check"></i> Qabul
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-reject"
                                                data-row="{{ $assignment->id }}" style="font-size: 0.7rem;">
                                                <i class="fas fa-times"></i> Rad
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if (isset($showPagination) && $showPagination && method_exists($assignments, 'links'))
        <div class="d-flex justify-content-end align-items-center mt-4">
            <div class="pagination-info me-4">
                <span class="text-muted">
                    {{ $assignments->firstItem() }} dan {{ $assignments->lastItem() }} gacha,
                    jami {{ $assignments->total() }} ta natija
                </span>
            </div>
            <div class="pagination-wrapper">
                {{ $assignments->appends(request()->query())->links('custom.pagination') }}
            </div>
        </div>
    @endif

    <style>
        /* Table styling */
        #myTable2 {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        #myTable2 th,
        #myTable2 td {
            padding: 6px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }

        #myTable2 thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            cursor: help;
        }

        #myTable2 tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Truncate header text */
        .truncate-header {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Text content with tooltip */
        .text-content {
            cursor: help;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Form controls */
        .form-select-sm,
        .form-control-sm {
            font-size: 0.95rem;
            padding: 0.2rem 0.4rem;
        }

        /* Rating range */
        .rating-range {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Badges */
        .badge {
            font-size: 0.75rem;
            padding: 0.3em 0.6em;
        }

        /* Button group */
        .btn-group {
            width: 100%;
        }

        .btn-group .btn {
            flex: 1;
            padding: 0.25rem 0.3rem;
        }

        /* Comment input */
        .comment-input {
            margin-bottom: 4px;
            width: 100%;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            align-items: center;
        }

        .pagination-info {
            font-size: 12px;
            color: #6c757d;
        }

        .pagination {
            margin: 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 5px;
        }

        .page-link {
            border: none;
            padding: 8px 12px;
            margin: 0 2px;
            border-radius: 6px;
            color: #495057;
            transition: all 0.2s ease;
        }

        .page-link:hover {
            background-color: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        }

        .page-item.disabled .page-link {
            color: #adb5bd;
            background-color: transparent;
        }

        /* Tooltip uchun maxsus stil */
        .tooltip {
            max-width: 400px !important;
            font-size: 15px;
        }

        .tooltip-inner {
            max-width: 400px !important;
            text-align: left;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .d-flex.justify-content-end {
                flex-direction: column;
                align-items: center;
            }

            .pagination-info {
                margin-bottom: 10px;
                margin-right: 0 !important;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ JavaScript yuklandi');
            console.log('Current URL:', window.location.href);

            // ✅ Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    boundary: 'window',
                    html: true,
                    sanitize: false
                });
            });

            // Task tanlanganda subtasklarni yuklash
            document.querySelectorAll('.task-select').forEach(select => {
                select.addEventListener('change', function() {
                    handleTaskChange(this);
                });
            });

            function handleTaskChange(selectElement) {
                const taskId = selectElement.value;
                const rowId = selectElement.dataset.row;
                const row = document.querySelector(`tr[data-assignment-id="${rowId}"]`);
                const subtaskCell = row.querySelector('.subtask-cell');
                const subtaskSelect = subtaskCell.querySelector('.subtask-select');
                const ratingRange = subtaskCell.querySelector('.rating-range');

                console.log('Task tanlandi:', taskId, 'Row:', rowId);

                if (!taskId) {
                    subtaskSelect.disabled = true;
                    subtaskSelect.innerHTML = '<option value="">Task tanlang</option>';
                    if (ratingRange) ratingRange.textContent = '';
                    return;
                }

                subtaskSelect.innerHTML = '<option value="">Yuklanmoqda...</option>';

                fetch(`/get-subtasks/${taskId}`)
                    .then(response => {
                        console.log('✅ Response keldi');
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(subtasks => {
                        console.log('✅ Subtasklar:', subtasks);
                        subtaskSelect.disabled = false;
                        subtaskSelect.innerHTML = '<option value="">Subtask tanlang</option>';

                        subtasks.forEach(subtask => {
                            const option = document.createElement('option');
                            option.value = subtask.id;
                            option.textContent = `${subtask.title} (${subtask.min} - ${subtask.max})`;
                            option.dataset.min = subtask.min;
                            option.dataset.max = subtask.max;
                            subtaskSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('❌ Xatolik:', error);
                        subtaskSelect.innerHTML = '<option value="">Xatolik yuz berdi</option>';
                    });
            }

            // Subtask tanlanganda min-max ko'rsatish
            document.querySelectorAll('.subtask-select').forEach(select => {
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const rowId = this.dataset.row;
                    const row = document.querySelector(`tr[data-assignment-id="${rowId}"]`);
                    const ratingInput = row.querySelector('.rating-input');
                    const ratingRange = row.querySelector('.rating-range');

                    if (selectedOption.dataset.min && selectedOption.dataset.max) {
                        const min = selectedOption.dataset.min;
                        const max = selectedOption.dataset.max;
                        if (ratingRange) ratingRange.textContent = `(${min} - ${max})`;
                        ratingInput.min = min;
                        ratingInput.max = max;
                    } else {
                        if (ratingRange) ratingRange.textContent = '';
                        ratingInput.removeAttribute('min');
                        ratingInput.removeAttribute('max');
                    }
                });
            });

            // Qabul qilindi tugmasi
            document.querySelectorAll('.btn-accept').forEach(btn => {
                btn.addEventListener('click', function() {
                    const rowId = this.dataset.row;
                    console.log('🔵 Qabul qilindi bosildi! Row:', rowId);
                    submitRating(rowId, 'accept');
                });
            });

            // Qabul qilinmadi tugmasi
            document.querySelectorAll('.btn-reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const rowId = this.dataset.row;
                    console.log('🔴 Qabul qilinmadi bosildi! Row:', rowId);
                    submitRating(rowId, 'reject');
                });
            });

            // O'zgartirish tugmasi
            document.querySelectorAll('.btn-edit').forEach(btn => {
                console.log('✏️ Edit tugmasi topildi:', btn);
                btn.addEventListener('click', function() {
                    const rowId = this.dataset.row;
                    console.log('✏️ O\'zgartirish bosildi! Row:', rowId);

                    if (confirm('Ushbu baholashni o\'zgartirmoqchimisiz?')) {
                        enableEditMode(rowId);
                    }
                });
            });

            // O'chirish tugmasi
            console.log('🔍 O\'chirish tugmalarini qidirish...');
            const deleteButtons = document.querySelectorAll('.btn-delete');
            console.log('Topilgan o\'chirish tugmalari soni:', deleteButtons.length);
            console.log('O\'chirish tugmalari:', deleteButtons);

            deleteButtons.forEach((btn, index) => {
                console.log(`O'chirish tugmasi #${index}:`, btn);
                console.log(`Row ID:`, btn.dataset.row);

                btn.addEventListener('click', function(e) {
                    console.log('🗑️ O\'chirish tugmasi bosildi!');
                    console.log('Event:', e);
                    console.log('Target:', e.target);
                    console.log('CurrentTarget:', e.currentTarget);

                    const rowId = this.dataset.row;
                    console.log('Row ID:', rowId);
                    console.log('Button element:', this);

                    if (confirm(
                            'Ushbu baholashni butunlay o\'chirmoqchimisiz? Bu amalni qaytarib bo\'lmaydi!'
                        )) {
                        console.log('✅ Foydalanuvchi tasdiqladi');
                        deleteRating(rowId);
                    } else {
                        console.log('❌ Foydalanuvchi bekor qildi');
                    }
                });
            });

            // Saqlash tugmasi (tahrirlash rejimida)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-save')) {
                    const btn = e.target.closest('.btn-save');
                    const rowId = btn.dataset.row;
                    console.log('💾 Saqlash bosildi! Row:', rowId);
                    submitRating(rowId, 'accept');
                }
            });

            // Bekor qilish tugmasi
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-cancel')) {
                    const btn = e.target.closest('.btn-cancel');
                    const rowId = btn.dataset.row;
                    console.log('❌ Bekor qilindi! Row:', rowId);
                    disableEditMode(rowId);
                }
            });

            function enableEditMode(rowId) {
                const row = document.querySelector(`tr[data-assignment-id="${rowId}"]`);
                if (!row) return;

                // Task - display yashirish, select ko'rsatish
                const taskCell = row.querySelector('.task-cell');
                const taskDisplay = taskCell.querySelector('.task-display');
                const taskSelect = taskCell.querySelector('.task-select');

                if (taskDisplay) taskDisplay.classList.add('d-none');
                if (taskSelect) {
                    taskSelect.classList.remove('d-none');
                    // Subtasklar yuklanishi uchun
                    const taskId = taskSelect.value;
                    if (taskId) {
                        handleTaskChange(taskSelect);
                    }
                }

                // Subtask - display yashirish, edit ko'rsatish
                const subtaskCell = row.querySelector('.subtask-cell');
                const subtaskDisplay = subtaskCell.querySelector('.subtask-display');
                const subtaskEdit = subtaskCell.querySelector('.subtask-edit');
                const subtaskBadge = subtaskDisplay?.querySelector('.badge');

                if (subtaskDisplay) subtaskDisplay.classList.add('d-none');
                if (subtaskEdit) {
                    subtaskEdit.classList.remove('d-none');

                    // Subtaskni yuklash
                    const taskSelect = taskCell.querySelector('.task-select');
                    if (taskSelect && taskSelect.value) {
                        const subtaskSelect = subtaskEdit.querySelector('.subtask-select');
                        subtaskSelect.disabled = false;

                        // Joriy subtask ID ni olish
                        const currentSubtaskId = subtaskBadge?.dataset.subtaskId;
                        const currentMin = subtaskBadge?.dataset.min;
                        const currentMax = subtaskBadge?.dataset.max;

                        fetch(`/get-subtasks/${taskSelect.value}`)
                            .then(response => response.json())
                            .then(subtasks => {
                                subtaskSelect.innerHTML = '<option value="">Subtask tanlang</option>';
                                subtasks.forEach(subtask => {
                                    const option = document.createElement('option');
                                    option.value = subtask.id;
                                    option.textContent =
                                        `${subtask.title} (${subtask.min} - ${subtask.max})`;
                                    option.dataset.min = subtask.min;
                                    option.dataset.max = subtask.max;

                                    // Joriy subtaskni tanlangan qilish
                                    if (currentSubtaskId && subtask.id == currentSubtaskId) {
                                        option.selected = true;
                                    }

                                    subtaskSelect.appendChild(option);
                                });

                                // Rating range ni ko'rsatish
                                if (currentMin && currentMax) {
                                    const ratingRange = subtaskEdit.querySelector('.rating-range');
                                    if (ratingRange) {
                                        ratingRange.textContent = `(${currentMin} - ${currentMax})`;
                                    }
                                }
                            });
                    }
                }

                // Rating - display yashirish, input ko'rsatish
                const ratingCell = row.querySelector('.rating-cell');
                const ratingDisplay = ratingCell.querySelector('.rating-display');
                const ratingInput = ratingCell.querySelector('.rating-input');

                if (ratingDisplay) ratingDisplay.classList.add('d-none');
                if (ratingInput) {
                    ratingInput.classList.remove('d-none');
                    // Min-max ni subtask badge dan olish
                    if (subtaskBadge) {
                        ratingInput.min = subtaskBadge.dataset.min;
                        ratingInput.max = subtaskBadge.dataset.max;
                    }
                }

                // Comment - display yashirish, edit ko'rsatish
                const commentCell = row.querySelector('.comment-cell');
                const commentDisplay = commentCell.querySelector('.comment-display');
                const commentEdit = commentCell.querySelector('.comment-edit');

                if (commentDisplay) commentDisplay.classList.add('d-none');
                if (commentEdit) commentEdit.classList.remove('d-none');
            }

            function disableEditMode(rowId) {
                const row = document.querySelector(`tr[data-assignment-id="${rowId}"]`);
                if (!row) return;

                // Task
                const taskCell = row.querySelector('.task-cell');
                const taskDisplay = taskCell.querySelector('.task-display');
                const taskSelect = taskCell.querySelector('.task-select');

                if (taskDisplay) taskDisplay.classList.remove('d-none');
                if (taskSelect) taskSelect.classList.add('d-none');

                // Subtask
                const subtaskCell = row.querySelector('.subtask-cell');
                const subtaskDisplay = subtaskCell.querySelector('.subtask-display');
                const subtaskEdit = subtaskCell.querySelector('.subtask-edit');

                if (subtaskDisplay) subtaskDisplay.classList.remove('d-none');
                if (subtaskEdit) subtaskEdit.classList.add('d-none');

                // Rating
                const ratingCell = row.querySelector('.rating-cell');
                const ratingDisplay = ratingCell.querySelector('.rating-display');
                const ratingInput = ratingCell.querySelector('.rating-input');

                if (ratingDisplay) ratingDisplay.classList.remove('d-none');
                if (ratingInput) ratingInput.classList.add('d-none');

                // Comment
                const commentCell = row.querySelector('.comment-cell');
                const commentDisplay = commentCell.querySelector('.comment-display');
                const commentEdit = commentCell.querySelector('.comment-edit');

                if (commentDisplay) commentDisplay.classList.remove('d-none');
                if (commentEdit) commentEdit.classList.add('d-none');
            }

            function deleteRating(rowId) {
                console.log('🗑️ deleteRating function chaqirildi');
                console.log('Row ID:', rowId);

                // CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                console.log('CSRF token elementi:', csrfToken);

                if (!csrfToken) {
                    alert('CSRF token topilmadi!');
                    console.error('❌ CSRF token yo\'q!');
                    console.log('Meta taglar:', document.querySelectorAll('meta'));
                    return;
                }

                const tokenValue = csrfToken.getAttribute('content');
                console.log('✅ CSRF token topildi:', tokenValue);

                // Form yaratish
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/assignment/${rowId}/delete-rating`;

                console.log('Form action:', form.action);
                console.log('Form method:', form.method);

                form.innerHTML = `
                    <input type="hidden" name="_token" value="${tokenValue}">
                    <input type="hidden" name="_method" value="DELETE">
                `;

                console.log('📝 O\'chirish formi yaratildi');
                console.log('Form HTML:', form.innerHTML);
                console.log('Form:', form);

                document.body.appendChild(form);
                console.log('Form body ga qo\'shildi');
                console.log('Body dagi formlar:', document.querySelectorAll('form'));

                console.log('🚀 Form yuborilmoqda...');
                form.submit();
                console.log('✅ Form yuborildi');
            }

            function submitRating(rowId, action) {
                console.log('📤 submitRating chaqirildi:', {
                    rowId,
                    action
                });

                const row = document.querySelector(`tr[data-assignment-id="${rowId}"]`);
                const taskSelect = row.querySelector('.task-select');
                const subtaskSelect = row.querySelector('.subtask-select');
                const ratingInput = row.querySelector('.rating-input');
                const commentInput = row.querySelector('.comment-input');

                const taskId = taskSelect ? taskSelect.value : '';
                const subtaskId = subtaskSelect ? subtaskSelect.value : '';
                const rating = ratingInput ? ratingInput.value : '';
                const comment = commentInput ? commentInput.value : '';

                console.log('📊 Ma\'lumotlar:', {
                    taskId,
                    subtaskId,
                    rating,
                    comment,
                    action
                });

                // Validatsiya
                if (action === 'accept') {
                    if (!taskId || !subtaskId || !rating) {
                        alert('Iltimos, Task, Subtask va Baho kiriting!');
                        console.log('❌ Validatsiya xatosi');
                        return;
                    }

                    const min = parseFloat(ratingInput.min);
                    const max = parseFloat(ratingInput.max);
                    const ratingValue = parseFloat(rating);

                    if (min && max && (ratingValue < min || ratingValue > max)) {
                        alert(`Baho ${min} dan ${max} gacha bo'lishi kerak!`);
                        console.log('❌ Baho chegaradan chiqdi');
                        return;
                    }
                }

                // CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    alert('CSRF token topilmadi!');
                    console.error('❌ CSRF token yo\'q!');
                    return;
                }

                console.log('✅ CSRF token topildi');

                // Form yaratish
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/assignment/${rowId}/save-rating`;

                form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken.getAttribute('content')}">
                    <input type="hidden" name="task_id" value="${taskId || ''}">
                    <input type="hidden" name="subtask_id" value="${subtaskId || ''}">
                    <input type="hidden" name="rating" value="${rating || ''}">
                    <input type="hidden" name="custom_comment" value="${comment || ''}">
                    <input type="hidden" name="action" value="${action}">
                `;

                console.log('📝 Form yaratildi');
                console.log('🚀 Form yuborilmoqda...');

                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
@endsection
