@extends('layouts.admin')

@section('title', 'Xisobot ko\'rib chiqish')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Report Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-{{ $report->type === 'weekly' ? 'calendar-week' : 'calendar-alt' }} me-2"></i>
                            {{ $report->period_text }}
                            @if ($report->type === 'weekly')
                                <span class="badge bg-primary-subtle text-primary ms-2">Haftalik</span>
                            @else
                                <span class="badge bg-info-subtle text-info ms-2">Oylik</span>
                            @endif
                        </h5>

                        <div class="d-flex gap-2">
                            {{-- <a href="{{ route('admin.reports.export-single', ['id' => $report->id, 'user_id' => $report->user_id]) }}"
                                class="btn btn-info">
                                <i class="fas fa-download"></i> Yuklab olish(Word)
                            </a> --}}
                            <a href="{{ route('admin.reports.index', ['user_id' => request('user_id')]) }}"
                                class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Orqaga
                            </a>
                        </div>
                    </div>
                </div>
                {{-- <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle me-3">
                                    {{ substr($report->user->full_name, 0, 2) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $report->user->full_name }}</h6>
                                    <small class="text-muted">{{ $report->user->email }}</small>
                                </div>
                            </div>

                            <small class="text-muted">Yaratilgan:</small>
                            <p class="mb-2">{{ $report->created_at->format('d.m.Y H:i') }}</p>

                            @if ($report->updated_at != $report->created_at)
                                <small class="text-muted">Oxirgi yangilanish:</small>
                                <p class="mb-2">{{ $report->updated_at->format('d.m.Y H:i') }}</p>
                            @endif

                            @if ($report->type === 'monthly')
                                <small class="text-muted">Aniq davr:</small>
                                <p class="mb-2">{{ $report->start_date->format('d.m.Y') }} -
                                    {{ $report->end_date->format('d.m.Y') }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="text-end">
                                @if ($report->isFullyApproved())
                                    <span class="badge bg-success fs-6 p-2 mb-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Tasdiqlangan
                                    </span>
                                @elseif($report->rejected_count > 0)
                                    <span class="badge bg-danger fs-6 p-2 mb-2">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Rad etilgan
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6 p-2 mb-2">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        Ko'rib chiqish kutilmoqda
                                    </span>
                                @endif

                                @php
                                    $reviews = $report->admin_reviews ?? [];
                                    $hasReview = count($reviews) > 0;
                                @endphp

                                @if ($hasReview)
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Administrator baholovi:</small>
                                        @php
                                            $firstReview = reset($reviews);
                                            $adminId = array_key_first($reviews);
                                            $admin = \App\Models\User::find($adminId);
                                        @endphp
                                        <span class="badge bg-{{ $firstReview['status'] === 'approved' ? 'success' : 'danger' }}">
                                            {{ $admin->full_name ?? 'Unknown' }}
                                        </span>
                                        <small class="d-block text-muted mt-1">{{ \Carbon\Carbon::parse($firstReview['reviewed_at'])->format('d.m.Y H:i') }}</small>
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <small class="text-muted">Hali hech kim ko'rib chiqmagan</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Report Content -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Xisobot matni
                    </h6>
                </div>
                <div class="card-body">
                    <div class="report-content">
                        {!! $report->content !!}
                    </div>
                </div>
            </div>

            {{-- ✅ YANGI: Biriktirilgan fayllar (Admin uchun) --}}
            @if(count($attachments) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-paperclip me-2"></i>
                            Biriktirilgan fayllar ({{ count($attachments) }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($attachments as $attachment)
                                <div class="col-md-4 mb-3">
                                    <div class="attachment-card-admin">
                                        <div class="attachment-header">
                                            @php
                                                $extension = pathinfo($attachment['original_name'], PATHINFO_EXTENSION);
                                                $iconClass = 'file-earmark-text';
                                                $iconColor = 'primary';
                                                
                                                if (in_array($extension, ['pdf'])) {
                                                    $iconClass = 'file-earmark-pdf';
                                                    $iconColor = 'danger';
                                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                                    $iconClass = 'file-earmark-word';
                                                    $iconColor = 'primary';
                                                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                    $iconClass = 'file-earmark-excel';
                                                    $iconColor = 'success';
                                                } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                                    $iconClass = 'file-earmark-image';
                                                    $iconColor = 'info';
                                                }
                                            @endphp
                                            <i class="bi bi-{{ $iconClass }} text-{{ $iconColor }} attachment-icon-large"></i>
                                        </div>
                                        <div class="attachment-body">
                                            <div class="attachment-title" title="{{ $attachment['original_name'] }}">
                                                {{ $attachment['original_name'] }}
                                            </div>
                                            <div class="attachment-details">
                                                <span class="badge bg-secondary">{{ strtoupper($extension) }}</span>
                                                <span class="text-muted">{{ number_format($attachment['size'] / 1024 / 1024, 2) }} MB</span>
                                            </div>
                                            <div class="attachment-date">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ \Carbon\Carbon::parse($attachment['uploaded_at'])->format('d.m.Y H:i') }}
                                            </div>
                                        </div>
                                        <div class="attachment-footer">
                                            <a href="{{ route('admin.reports.download-attachment', [$report, $attachment['filename']]) }}" 
                                               class="btn btn-primary btn-sm w-100" target="_blank">
                                                <i class="bi bi-download me-1"></i> Yuklab olish
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- My Review Section -->
            {{-- @if ($canReview || $myReview)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user-check me-2"></i>
                            Mening ko'rib chiqishim
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($myReview)
                            <div class="alert alert-{{ $myReview['status'] === 'approved' ? 'success' : 'danger' }} mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>
                                            @if ($myReview['status'] === 'approved')
                                                <i class="fas fa-check-circle me-1"></i>
                                                Siz bu xisobotni tasdiqlagan ekansiz
                                            @else
                                                <i class="fas fa-times-circle me-1"></i>
                                                Siz bu xisobotni rad etgan ekansiz
                                            @endif
                                        </strong>
                                        <small class="d-block text-muted mt-1">{{ \Carbon\Carbon::parse($myReview['reviewed_at'])->format('d.m.Y H:i') }}</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick="toggleEditReview()">
                                        <i class="fas fa-edit me-1"></i>
                                        O'zgartirish
                                    </button>
                                </div>
                                @if ($myReview['comment'])
                                    <div class="mt-2">
                                        <small class="text-muted">Sizning izohingiz:</small>
                                        <p class="mb-0 mt-1">{{ $myReview['comment'] }}</p>
                                    </div>
                                @endif
                            </div>

                            <div id="edit-review-form" style="display: none;">
                                <form action="{{ route('admin.reports.update-review', $report) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    @if (request('user_id'))
                                        <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label">Yangi qaror</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="status" id="approve-edit"
                                                value="approved" {{ $myReview['status'] === 'approved' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success" for="approve-edit">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Tasdiqlash
                                            </label>

                                            <input type="radio" class="btn-check" name="status" id="reject-edit"
                                                value="rejected" {{ $myReview['status'] === 'rejected' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-danger" for="reject-edit">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Rad etish
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comment-edit" class="form-label">Izoh (ixtiyoriy)</label>
                                        <textarea name="comment" id="comment-edit" class="form-control" rows="3" placeholder="Qo'shimcha izoh yozing...">{{ $myReview['comment'] }}</textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            Yangilash
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="toggleEditReview()">
                                            <i class="fas fa-times me-1"></i>
                                            Bekor qilish
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @elseif($canReview)
                            <form action="{{ route('admin.reports.review', $report) }}" method="POST">
                                @csrf
                                @if (request('user_id'))
                                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Qaroringiz <span class="text-danger">*</span></label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="status" id="approve"
                                            value="approved" required>
                                        <label class="btn btn-outline-success" for="approve">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Tasdiqlash
                                        </label>

                                        <input type="radio" class="btn-check" name="status" id="reject"
                                            value="rejected" required>
                                        <label class="btn btn-outline-danger" for="reject">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Rad etish
                                        </label>
                                    </div>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Izoh (ixtiyoriy)</label>
                                    <textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" rows="3"
                                        placeholder="Qo'shimcha izoh yozing...">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    Ko'rib chiqishni yuborish
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Siz bu xisobotni ko'rib chiqa olmaysiz.
                                @php
                                    $reviews = $report->admin_reviews ?? [];
                                @endphp
                                @if (count($reviews) > 0)
                                    Boshqa administrator allaqachon ko'rib chiqgan.
                                @else
                                    Noma'lum sabab.
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif --}}

            <!-- Admin Review Section -->
            {{-- @if (count($reviews) > 0)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>
                            Administrator baholovi
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach ($reviews as $review)
                            @php
                                $isApproved = isset($review['is_approved'])
                                    ? $review['is_approved']
                                    : $review['status'] === 'approved';
                            @endphp
                            <div class="card border-{{ $isApproved ? 'success' : 'danger' }}">
                                <div class="card-header bg-{{ $isApproved ? 'success' : 'danger' }} bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-{{ $isApproved ? 'success' : 'danger' }}"
                                            style="color: {{ $isApproved ? '#1e1e1e' : '#1e1e1e' }} !important;">
                                            @if ($isApproved)
                                                <i class="fas fa-check-circle me-1"></i>
                                                Tasdiqlangan
                                            @else
                                                <i class="fas fa-times-circle me-1"></i>
                                                Rad etilgan
                                            @endif
                                        </strong>
                                        <small class="text-muted">{{ $review['reviewed_at'] ?? 'N/A' }}</small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">Administrator:</small>
                                        <p class="mb-1 fw-bold">{{ auth()->user()->full_name ?? 'Unknown' }}</p>
                                    </div>
                                    @if (isset($review['comment']) && $review['comment'])
                                        <div>
                                            <small class="text-muted">Izoh:</small>
                                            <p class="mb-0 mt-1">{{ $review['comment'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            Ko'rib chiqish jarayoni yakunlandi.
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-hourglass-half text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-3">Hali hech kim ko'rib chiqmagan</h6>
                        <p class="text-muted">Administrator ko'rib chiqishi kutilmoqda</p>
                        @if ($canReview)
                            <p class="text-success"><i class="fas fa-arrow-up me-1"></i> Siz ko'rib chiqa olasiz</p>
                        @endif
                    </div>
                </div>
            @endif --}}
        </div>
    </div>

    <script>
        function toggleEditReview() {
            const form = document.getElementById('edit-review-form');
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>

    <style>
        .report-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .report-content h3,
        .report-content h4,
        .report-content h5 {
            color: var(--bs-primary);
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .badge.fs-6 {
            font-size: 0.9rem !important;
        }

        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }

        .btn-check:checked+.btn-outline-success {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
            color: white;
        }

        .btn-check:checked+.btn-outline-danger {
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
            color: white;
        }

        .badge.bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .badge.bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        /* ✅ Admin Attachment Card Styling */
        .attachment-card-admin {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .attachment-card-admin:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .attachment-header {
            padding: 1.5rem;
            text-align: center;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .attachment-icon-large {
            font-size: 3.5rem;
        }

        .attachment-body {
            padding: 1rem;
            flex-grow: 1;
        }

        .attachment-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: #212529;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attachment-details {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .attachment-date {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .attachment-footer {
            padding: 1rem;
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
        }
    </style>
@endsection