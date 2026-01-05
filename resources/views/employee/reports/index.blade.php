@extends('layouts.employee')

@section('title', 'Xisobotlarim')

@section('content')
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-text me-2"></i>
                        Yangi xisobot yozish
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Haftalik xisobotlar -->
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="fas fa-calendar-week text-primary me-2"></i>
                                Haftalik xisobotlar
                            </h6>

                            <!-- Joriy hafta -->
                            <div class="mb-3">
                                @if (!$hasWeeklyReport)
                                    <a href="{{ route('employee.reports.create', ['type' => 'weekly', 'week_offset' => 0]) }}"
                                        class="btn btn-primary w-100">
                                        <i class="fas fa-calendar-week me-2"></i>
                                        Joriy hafta xisoboti
                                        <small class="d-block opacity-75 mt-1">
                                            {{ $currentWeek['start_date']->format('d.m.Y') }} -
                                            {{ $currentWeek['end_date']->format('d.m.Y') }}
                                        </small>
                                    </a>
                                @else
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Joriy hafta xisoboti yozilgan</strong>
                                        <small class="d-block mt-1">
                                            {{ $currentWeek['start_date']->format('d.m.Y') }} -
                                            {{ $currentWeek['end_date']->format('d.m.Y') }}
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <!-- O'tgan haftalar uchun modal tugmasi -->
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#weeklyModal">
                                <i class="fas fa-history me-2"></i>
                                O'tgan haftalar uchun yozish
                            </button>
                        </div>

                        <!-- Oylik xisobotlar -->
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                <i class="fas fa-calendar-alt text-info me-2"></i>
                                Oylik xisobotlar
                            </h6>

                            <!-- Joriy oy -->
                            <div class="mb-3">
                                @if (!$hasMonthlyReport)
                                    <a href="{{ route('employee.reports.create', ['type' => 'monthly', 'month_offset' => 0]) }}"
                                        class="btn btn-info w-100">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Joriy oy xisoboti
                                        <small class="d-block opacity-75 mt-1">
                                            {{ $currentMonth['start_date']->format('d.m.Y') }} -
                                            {{ $currentMonth['end_date']->format('d.m.Y') }}
                                        </small>
                                    </a>
                                @else
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Joriy oy xisoboti yozilgan</strong>
                                        <small class="d-block mt-1">
                                            {{ $currentMonth['start_date']->format('d.m.Y') }} -
                                            {{ $currentMonth['end_date']->format('d.m.Y') }}
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <!-- O'tgan oylar uchun modal tugmasi -->
                            <button class="btn btn-outline-info w-100" data-bs-toggle="modal"
                                data-bs-target="#monthlyModal">
                                <i class="fas fa-history me-2"></i>
                                O'tgan oylar uchun yozish
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filterlash va qidirish
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Xisobot turi</label>
                            <select name="type" class="form-select">
                                <option value="all" {{ $type == 'all' ? 'selected' : '' }}>
                                    <i class="fas fa-list"></i> Barchasi
                                </option>
                                <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>
                                    <i class="fas fa-calendar-week"></i> Haftalik
                                </option>
                                <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>
                                    <i class="fas fa-calendar-alt"></i> Oylik
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Boshlanish sanasi</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tugash sanasi</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Qidirish
                                </button>
                                <a href="{{ route('employee.reports.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Tozalash
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reports List -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ul me-2"></i>
                        Mening xisobotlarim
                    </h5>
                    @if ($reports->count() > 0)
                        <small class="text-muted">
                            Jami: {{ $reports->total() }} ta xisobot
                        </small>
                    @endif
                </div>
                <div class="card-body">
                    @if ($reports->count() > 0)
                        @foreach ($reports as $report)
                            <div
                                class="card mb-3 border-start border-4 report-card {{ $report->isFullyApproved() ? 'border-success' : ($report->rejected_count > 0 ? 'border-danger' : 'border-warning') }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center mb-2">
                                                @if ($report->type === 'weekly')
                                                    <i class="fas fa-calendar-week text-primary me-2"></i>
                                                    <span class="badge bg-primary-subtle text-primary me-2">Haftalik</span>
                                                @else
                                                    <i class="fas fa-calendar-alt text-info me-2"></i>
                                                    <span class="badge bg-info-subtle text-info me-2">Oylik</span>
                                                @endif
                                                <h6 class="mb-0">{{ $report->period_text }}</h6>
                                            </div>

                                            <p class="text-muted mb-2">
                                                {{ Str::limit(strip_tags($report->content), 120) }}
                                            </p>

                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-plus-circle me-1"></i>
                                                    {{ $report->created_at->format('d.m.Y H:i') }}
                                                </small>
                                                @if ($report->updated_at != $report->created_at)
                                                    <small class="text-muted">
                                                        <i class="fas fa-edit me-1"></i>
                                                        {{ $report->updated_at->format('d.m.Y H:i') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 text-end">
                                            <!-- Status badges -->
                                            {{-- <div class="mb-3">
                                                @if ($report->isFullyApproved())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        To'liq tasdiqlangan
                                                    </span>
                                                @elseif($report->rejected_count > 0)
                                                    <span class="badge bg-danger me-1">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        {{ $report->rejected_count }} ta rad
                                                    </span>
                                                    @if ($report->approved_count > 0)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            {{ $report->approved_count }} ta tasdiqlangan
                                                        </span>
                                                    @endif
                                                @elseif($report->approved_count > 0)
                                                    <span class="badge bg-success me-1">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $report->approved_count }} ta tasdiqlangan
                                                    </span>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-hourglass me-1"></i>
                                                        Ko'rib chiqilmoqda
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-hourglass me-1"></i>
                                                        Ko'rib chiqish kutilmoqda
                                                    </span>
                                                @endif
                                            </div> --}}

                                            <!-- Action buttons -->
                                            <div class="btn-group">
                                                <a href="{{ route('employee.reports.show', $report) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Xisobotni ko'rish">
                                                    <i class="fas fa-eye"></i> Ko'rish
                                                </a>
                                                @if ($report->isEditable())
                                                    <a href="{{ route('employee.reports.edit', $report) }}"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Xisobotni tahrirlash">
                                                        <i class="fas fa-pencil-alt"></i> Tahrirlash
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete('{{ $report->id }}')"
                                                        title="Xisobotni o'chirish">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-end align-items-center mt-4">
                            <div class="pagination-info me-4">
                                <span class="text-muted">
                                    {{ $reports->firstItem() }} dan {{ $reports->lastItem() }} gacha,
                                    jami {{ $reports->total() }} ta natija
                                </span>
                            </div>
                            <div class="pagination-wrapper">
                                {{ $reports->appends(request()->query())->links('custom.pagination') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">Xisobotlar topilmadi</h4>
                            <p class="text-muted">Hali hech qanday xisobot yozmagan yoki filterlarga mos xisobot yo'q</p>
                            <div class="mt-4">
                                <a href="{{ route('employee.reports.create', ['type' => 'weekly']) }}"
                                    class="btn btn-primary me-2">
                                    <i class="fas fa-calendar-week me-2"></i>
                                    Haftalik xisobot yozish
                                </a>
                                <a href="{{ route('employee.reports.create', ['type' => 'monthly']) }}"
                                    class="btn btn-info">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Oylik xisobot yozish
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Reports Modal -->
    <div class="modal fade" id="weeklyModal" tabindex="-1" aria-labelledby="weeklyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="weeklyModalLabel">
                        <i class="fas fa-calendar-week text-primary me-2"></i>
                        O'tgan haftalik xisobotlar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (
                        $availableWeeks->filter(function ($week) {
                                return $week['offset'] < 0;
                            })->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($availableWeeks as $week)
                                @if ($week['offset'] < 0)
                                    <a href="{{ route('employee.reports.create', ['type' => 'weekly', 'week_offset' => $week['offset']]) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <div class="fw-medium">{{ $week['week_description'] }}</div>
                                            <small class="text-muted">{{ $week['period_text'] }}</small>
                                        </div>
                                        @if ($week['has_report'])
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                Yozilgan
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-pencil me-1"></i>
                                                Yozish
                                            </span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-success">Barcha haftalik xisobotlar yozilgan!</h5>
                            <p class="text-muted">O'tgan haftalar uchun yoziladigan xisobotlar yo'q.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Yopish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Reports Modal -->
    <div class="modal fade" id="monthlyModal" tabindex="-1" aria-labelledby="monthlyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthlyModalLabel">
                        <i class="fas fa-calendar-alt text-info me-2"></i>
                        O'tgan oylik xisobotlar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (
                        $availableMonths->filter(function ($month) {
                                return $month['offset'] < 0;
                            })->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($availableMonths as $month)
                                @if ($month['offset'] < 0)
                                    <a href="{{ route('employee.reports.create', ['type' => 'monthly', 'month_offset' => $month['offset']]) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <div class="fw-medium">{{ $month['month_description'] }}</div>
                                            <small class="text-muted">{{ $month['month_name'] }}</small>
                                            <small class="d-block text-muted">{{ $month['period_text'] }}</small>
                                        </div>
                                        @if ($month['has_report'])
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                Yozilgan
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-pencil me-1"></i>
                                                Yozish
                                            </span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-success">Barcha oylik xisobotlar yozilgan!</h5>
                            <p class="text-muted">O'tgan oylar uchun yoziladigan xisobotlar yo'q.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Yopish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Xisobotni o'chirish
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Haqiqatan ham ushbu xisobotni o'chirmoqchimisiz?</p>
                    <p class="text-danger">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Eslatma:</strong> Bu amal bekor qilib bo'lmaydi!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Bekor qilish
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            O'chirish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Card animations */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Border styles */
        .border-start {
            border-left-width: 4px !important;
        }

        /* Button group styling */
        .btn-group .btn {
            border-radius: 0.375rem;
            margin-right: 0.25rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        /* Badge styling */
        .badge.bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.1) !important;
            border: 1px solid rgba(13, 110, 253, 0.2);
        }

        .badge.bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
            border: 1px solid rgba(13, 202, 240, 0.2);
        }

        /* List group item hover effect */
        .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            transition: all 0.2s ease;
        }

        /* Statistics cards */
        .card.bg-primary,
        .card.bg-success,
        .card.bg-info,
        .card.bg-warning {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-group .btn {
                margin-right: 0;
                margin-bottom: 0.25rem;
            }

            .col-md-8,
            .col-md-4 {
                text-align: center !important;
            }
        }

        /* Loading states */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Alert improvements */
        .alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Modal improvements - Dark theme */
        .modal-content {
            background-color: #1e2139 !important;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.5);
            color: #ffffff;
        }

        .modal-header {
            background-color: #1e2139;
            border-bottom: 1px solid #3a3f5c;
            border-radius: 1rem 1rem 0 0;
            color: #ffffff;
        }

        .modal-body {
            background-color: #1e2139;
            color: #ffffff;
        }

        .modal-footer {
            background-color: #1e2139;
            border-top: 1px solid #3a3f5c;
            border-radius: 0 0 1rem 1rem;
        }

        .modal-title {
            color: #ffffff !important;
        }

        /* Dark modal list group */
        .modal .list-group-item {
            background-color: #2a2f4a !important;
            border-color: #3a3f5c !important;
            color: #ffffff !important;
        }

        .modal .list-group-item:hover {
            background-color: #343a56 !important;
            color: #ffffff !important;
        }

        .modal .list-group-item .text-muted {
            color: #9ca3af !important;
        }

        /* Dark close button */
        .modal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Dark badges in modal */
        .modal .badge.bg-success {
            background-color: #10b981 !important;
        }

        .modal .badge.bg-warning {
            background-color: #f59e0b !important;
            color: #000000 !important;
        }

        /* Empty state styling in modal */
        .modal .text-success {
            color: #10b981 !important;
        }

        .modal .text-muted {
            color: #9ca3af !important;
        }

        /* Modal animation */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translate(0, -50px);
        }

        .modal.show .modal-dialog {
            transform: none;
        }
    </style>

    <script>
        // Delete confirmation function
        function confirmDelete(reportId) {
            const form = document.getElementById('deleteForm');
            form.action = '{{ route('employee.reports.destroy', ':id') }}'.replace(':id', reportId);

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Form validation improvements
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Yuklanmoqda...';

                    // Re-enable after 3 seconds to prevent permanent disable
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || submitBtn.innerHTML;
                    }, 3000);
                }
            });
        });

        // Modal link clicks - close modal after navigation
        document.querySelectorAll('.modal .list-group-item').forEach(link => {
            link.addEventListener('click', function() {
                // Close the modal before navigation
                const modal = this.closest('.modal');
                if (modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            });
        });
    </script>
@endsection
