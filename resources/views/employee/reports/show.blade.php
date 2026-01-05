{{-- resources/views/employee/reports/show.blade.php --}}

@extends('layouts.employee')

@section('title', 'Xisobot ko\'rish')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
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
                        <div>
                            @if ($report->isEditable())
                                <a href="{{ route('employee.reports.edit', $report) }}" class="btn btn-warning me-2">
                                    <i class="fas fa-edit me-1"></i>
                                    Tahrirlash
                                </a>
                            @endif
                            <a href="{{ route('employee.reports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Orqaga
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
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
                            @if (!$report->isEditable())
                                <div class="text-end mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Tahrirlash mumkin emas
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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

            {{-- ✅ Biriktirilgan fayllar - BARCHAGA KO'RSATISH --}}
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
                                <div class="col-md-6 mb-3">
                                    <div class="attachment-card">
                                        <div class="attachment-icon">
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
                                            <i class="bi bi-{{ $iconClass }} text-{{ $iconColor }}"></i>
                                        </div>
                                        <div class="attachment-info">
                                            <div class="attachment-name">{{ $attachment['original_name'] }}</div>
                                            <div class="attachment-meta">
                                                <span class="attachment-size">
                                                    <i class="bi bi-hdd me-1"></i>
                                                    {{ number_format($attachment['size'] / 1024 / 1024, 2) }} MB
                                                </span>
                                                <span class="attachment-date">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($attachment['uploaded_at'])->format('d.m.Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="{{ route('employee.reports.download-attachment', [$report, $attachment['filename']]) }}" 
                                               class="btn btn-sm btn-primary" target="_blank" title="Yuklab olish">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                @if ($report->isEditable())
                    <a href="{{ route('employee.reports.edit', $report) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>
                        Tahrirlash
                    </a>
                @endif
                <a href="{{ route('employee.reports.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-1"></i>
                    Barcha xisobotlar
                </a>
            </div>
        </div>
    </div>
@endsection

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

    .report-content ul,
    .report-content ol {
        margin-left: 1rem;
    }

    .report-content blockquote {
        border-left: 4px solid var(--bs-primary);
        padding-left: 1rem;
        margin-left: 0;
        font-style: italic;
    }

    .badge.fs-6 {
        font-size: 0.9rem !important;
    }

    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    .badge.bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    .badge.bg-info-subtle {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }

    /* Attachment card styling */
    .attachment-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .attachment-card:hover {
        background: #e9ecef;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .attachment-icon {
        font-size: 2.5rem;
        flex-shrink: 0;
    }

    .attachment-info {
        flex-grow: 1;
        min-width: 0;
    }

    .attachment-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.25rem;
    }

    .attachment-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .attachment-actions {
        flex-shrink: 0;
    }
</style>