@extends('layouts.admin')

@section('title', 'Xodimlar xisobotlari')

@section('content')
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-12 mb-4">
            <div class="row">
                <div class="col-md-2">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fs-2"></i>
                            <h4 class="mt-2">{{ $stats['total_reports'] }}</h4>
                            <small>Jami xisobotlar</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stats-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-hourglass-half fs-2"></i>
                            <h4 class="mt-2">{{ $stats['pending_reports'] }}</h4>
                            <small>Ko'rib chiqish kutilmoqda</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fs-2"></i>
                            <h4 class="mt-2">{{ $stats['approved_reports'] }}</h4>
                            <small>Tasdiqlangan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stats-card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-times-circle fs-2"></i>
                            <h4 class="mt-2">{{ $stats['rejected_reports'] }}</h4>
                            <small>Rad etilgan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-week fs-2"></i>
                            <h4 class="mt-2">{{ $stats['weekly_reports'] }}</h4>
                            <small>Haftalik xisobotlar</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stats-card bg-secondary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-alt fs-2"></i>
                            <h4 class="mt-2">{{ $stats['monthly_reports'] }}</h4>
                            <small>Oylik xisobotlar</small>
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
                        @if ($userId)
                            <input type="hidden" name="user_id" value="{{ $userId }}">
                        @endif

                        <div class="col-md-3">
                            <label class="form-label">Turi</label>
                            <select name="type" class="form-select">
                                <option value="all" {{ $type == 'all' ? 'selected' : '' }}>Barchasi</option>
                                <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Haftalik</option>
                                <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Oylik</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Holat</label>
                            <select name="status" class="form-select">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Barchasi</option>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Ko'rib chiqish
                                    kutilmoqda</option>
                                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Tasdiqlangan
                                </option>
                                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rad etilgan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Boshlanish sanasi</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tugash sanasi</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('admin.reports.index', ['user_id' => $userId]) }}"
                                    class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
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
                    <h6 class="mb-0">
                        <i class="fas fa-list-ul me-2"></i>
                        Xodimlar xisobotlari
                    </h6>
                    <div>
                        <a href="{{ route('admin.attendance.index') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Orqaga
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($reports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Xodim</th>
                                        <th>Turi</th>
                                        <th>Bajarilgan ishlar va topshiriqlar</th>
                                        <th>Sanasi</th>
                                        {{-- <th>Holat</th> --}}
                                        {{-- <th>Admin baholovi</th> --}}
                                        <th>Fayllar</th>
                                        {{-- <th>Sana</th> --}}
                                        {{-- <th>Amallar</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reports as $report)
                                        @php
                                            $attachments = json_decode($report->attachments, true) ?? [];
                                            $attachmentCount = count($attachments);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        {{ substr($report->user->full_name, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $report->user->full_name }}</strong>
                                                        <small class="d-block text-muted">{{ $report->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($report->type === 'weekly')
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-calendar-week me-1"></i>
                                                        Haftalik
                                                    </span>
                                                @else
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        Oylik
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.reports.export-single', ['id' => $report->id, 'user_id' => $report->user_id]) }}"
                                >
                                <i class="fas fa-download"></i> Xisobotni yuklab olish
                            </a> yoki <a href="{{ route('admin.reports.show', ['report' => $report->id, 'user_id' => request('user_id')]) }}"
                                                        >
                                                        <i class="fas fa-eye"></i>Saytda ko'rish
                                                    </a>
                                            </td>
                                            <td>
                                                <small>{{ $report->period_text }}</small>
                                            </td>
                                            {{-- <td>
                                                @if ($report->isFullyApproved())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Tasdiqlangan
                                                    </span>
                                                @elseif($report->rejected_count > 0)
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        Rad etilgan
                                                    </span>
                                                @elseif($report->approved_count > 0)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Tasdiqlangan
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-hourglass me-1"></i>
                                                        Yangi
                                                    </span>
                                                @endif
                                            </td> --}}
                                            {{-- <td>
                                                @php
                                                    $reviews = $report->admin_reviews ?? [];
                                                    $hasReview = count($reviews) > 0;
                                                @endphp

                                                @if ($hasReview)
                                                    @php
                                                        $firstReview = reset($reviews);
                                                        $adminId = array_key_first($reviews);
                                                        $admin = \App\Models\User::find($adminId);
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle-sm me-2 bg-{{ $firstReview['status'] === 'approved' ? 'success' : 'danger' }}">
                                                            <i class="fas fa-{{ $firstReview['status'] === 'approved' ? 'check' : 'times' }}"></i>
                                                        </div>
                                                        <small>
                                                            {{ $admin->full_name ?? 'Unknown' }}<br>
                                                            <span class="text-muted">{{ \Carbon\Carbon::parse($firstReview['reviewed_at'])->format('d.m.Y') }}</span>
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-clock me-1"></i>
                                                        Kutilmoqda
                                                    </span>
                                                @endif
                                            </td> --}}
                                            <td>
                                                @if($attachmentCount > 0)
                                                    {{-- ✅ YANGI: Fayllar ustiga hover qilganda dropdown ko'rsatish --}}
                                                    <div class="files-dropdown-wrapper">
                                                        <span class="badge bg-info files-badge" data-report-id="{{ $report->id }}">
                                                            <i class="fas fa-paperclip me-1"></i>
                                                            {{ $attachmentCount }}
                                                        </span>
                                                        
                                                        {{-- Dropdown menu --}}
                                                        <div class="files-dropdown" id="files-dropdown-{{ $report->id }}">
                                                            <div class="files-dropdown-header">
                                                                <strong>Biriktirilgan fayllar</strong>
                                                                <small class="text-muted">({{ $attachmentCount }} ta)</small>
                                                            </div>
                                                            <div class="files-dropdown-body">
                                                                @foreach($attachments as $index => $attachment)
                                                                    @php
                                                                        $extension = pathinfo($attachment['original_name'], PATHINFO_EXTENSION);
                                                                        $iconClass = 'file-alt';
                                                                        $iconColor = 'primary';
                                                                        
                                                                        if (in_array($extension, ['pdf'])) {
                                                                            $iconClass = 'file-pdf';
                                                                            $iconColor = 'danger';
                                                                        } elseif (in_array($extension, ['doc', 'docx'])) {
                                                                            $iconClass = 'file-word';
                                                                            $iconColor = 'primary';
                                                                        } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                                            $iconClass = 'file-excel';
                                                                            $iconColor = 'success';
                                                                        } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                                                            $iconClass = 'file-image';
                                                                            $iconColor = 'info';
                                                                        }
                                                                    @endphp
                                                                    <a href="{{ route('admin.reports.download-attachment', [$report, $attachment['filename']]) }}" 
                                                                       class="file-item-dropdown" target="_blank">
                                                                        <div class="file-icon-wrapper">
                                                                            <i class="fas fa-{{ $iconClass }} text-{{ $iconColor }}"></i>
                                                                        </div>
                                                                        <div class="file-details">
                                                                            <div class="file-name" title="{{ $attachment['original_name'] }}">
                                                                                {{ Str::limit($attachment['original_name'], 30) }}
                                                                            </div>
                                                                            <div class="file-meta">
                                                                                <span class="badge bg-secondary badge-sm">{{ strtoupper($extension) }}</span>
                                                                                <span class="file-size">{{ number_format($attachment['size'] / 1024 / 1024, 2) }} MB</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="file-download-icon">
                                                                            <i class="fas fa-download text-primary"></i>
                                                                        </div>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            {{-- <td>
                                                <small>{{ $report->created_at->format('d.m.Y H:i') }}</small>
                                            </td> --}}
                                            {{-- <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.reports.show', ['report' => $report->id, 'user_id' => request('user_id')]) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if ($report->canBeReviewedByAdmin(auth()->id()))
                                                        <a href="{{ route('admin.reports.show', ['report' => $report->id, 'user_id' => request('user_id')]) }}"
                                                            class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-check-square"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

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
                            <i class="fas fa-file-text text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">Xisobotlar topilmadi</h5>
                            <p class="text-muted">Filterlarga mos xisobotlar mavjud emas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .stats-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .avatar-circle-sm {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        .bg-success {
            background-color: #198754 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        /* ✅ Files Dropdown Styling */
        .files-dropdown-wrapper {
            position: relative;
            display: inline-block;
        }

        .files-badge {
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }

        .files-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .files-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 8px;
            width: 320px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: fadeInDown 0.3s ease;
        }

        .files-dropdown-wrapper:hover .files-dropdown {
            display: block;
        }

        .files-dropdown-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 0.5rem 0.5rem 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .files-dropdown-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .file-item-dropdown {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.375rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .file-item-dropdown:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
            transform: translateX(5px);
        }

        .file-icon-wrapper {
            font-size: 1.5rem;
            flex-shrink: 0;
            width: 35px;
            text-align: center;
        }

        .file-details {
            flex-grow: 1;
            min-width: 0;
        }

        .file-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.25rem;
        }

        .file-meta {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            font-size: 0.75rem;
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .file-size {
            color: #6c757d;
        }

        .file-download-icon {
            flex-shrink: 0;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .file-item-dropdown:hover .file-download-icon {
            opacity: 1;
        }

        /* Scrollbar styling */
        .files-dropdown-body::-webkit-scrollbar {
            width: 6px;
        }

        .files-dropdown-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .files-dropdown-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .files-dropdown-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Animation */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        /* Arrow indicator */
        .files-dropdown::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid #dee2e6;
        }

        .files-dropdown::after {
            content: '';
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-bottom: 7px solid #f8f9fa;
        }
    </style>
@endsection