{{-- resources/views/employee/reports/edit.blade.php --}}

@extends('layouts.employee')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            {{ $title }}
                        </h5>
                        <div>
                            <a href="{{ route('employee.reports.show', $report) }}" class="btn btn-light btn-sm me-2">
                                <i class="bi bi-eye me-1"></i> Ko'rish
                            </a>
                            <a href="{{ route('employee.reports.index') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Orqaga
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Period info --}}
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-calendar-check me-2"></i>
                        <strong>Davr:</strong> {{ $period }}
                        @if ($report->type === 'monthly')
                            <br><small class="text-muted">
                                Aniq oraliq: {{ $report->start_date->format('d.m.Y') }} -
                                {{ $report->end_date->format('d.m.Y') }}
                            </small>
                        @endif
                    </div>

                    {{-- Review warning --}}
                    @if ($report->approved_count > 0 || $report->rejected_count > 0)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Diqqat!</strong> Bu xisobot allaqachon
                            {{ $report->approved_count + $report->rejected_count }} ta administrator tomonidan ko'rib
                            chiqilgan. O'zgarishlar qilsangiz, yangi ko'rib chiqish jarayoni boshlanishi mumkin.
                        </div>
                    @endif

                    {{-- Validation errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Xatoliklar topildi:
                            </h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form id="reportForm" action="{{ route('employee.reports.update', $report) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="editor" class="form-label fw-bold">
                                Xisobot matni <span class="text-danger">*</span>
                            </label>

                            <div id="editor"></div>
                            <textarea id="content" name="content" style="display:none;">{!! old('content', $report->content) !!}</textarea>

                            @error('content')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror

                            <div class="alert alert-light border mt-3">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                <strong>Qoidalar:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>✅ Faqat <strong>raqamli ro'yxat</strong> (numbered list) formatida yozing</li>
                                    <li>✅ Har bir vazifa <strong>3 ta belgidan</strong> ko'p bo'lishi kerak</li>
                                </ul>
                            </div>
                        </div>

                        {{-- ✅ Mavjud fayllar - FAQAT RUXSAT BERILGAN EMAILLAR UCHUN --}}
                        @if(auth()->user()->canUploadReportFiles() && count($attachments) > 0)
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-paperclip me-1"></i>
                                    Mavjud fayllar
                                </label>
                                <div class="existing-files">
                                    @foreach($attachments as $attachment)
                                        <div class="file-item existing-file" id="file-{{ $attachment['filename'] }}">
                                            <div class="file-info">
                                                <i class="bi bi-file-earmark-text file-icon text-primary"></i>
                                                <div>
                                                    <div class="fw-bold">{{ $attachment['original_name'] }}</div>
                                                    <div class="file-size">{{ number_format($attachment['size'] / 1024 / 1024, 2) }} MB</div>
                                                </div>
                                            </div>
                                            <div class="file-actions">
                                                <a href="{{ route('employee.reports.download-attachment', [$report, $attachment['filename']]) }}" 
                                                   class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                                    <i class="bi bi-download"></i> Yuklab olish
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="removeFile('{{ $attachment['filename'] }}')">
                                                    <i class="bi bi-trash"></i> O'chirish
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                {{-- O'chiriladigan fayllar uchun hidden input --}}
                                <div id="removeFilesContainer"></div>
                            </div>
                        @endif

                        {{-- ✅ Yangi fayllar yuklash - FAQAT RUXSAT BERILGAN EMAILLAR UCHUN --}}
                        @if(auth()->user()->canUploadReportFiles())
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-paperclip me-1"></i>
                                    Yangi fayllar yuklash (ixtiyoriy)
                                </label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control @error('attachments.*') is-invalid @enderror" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                
                                @error('attachments')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('attachments.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Jami maksimal 5 ta fayl bo'lishi mumkin. Har bir fayl 10MB dan oshmasligi kerak.
                                    <br>
                                    <small>Hozirda: <span id="currentFileCount">{{ count($attachments) }}</span> ta fayl mavjud</small>
                                </div>

                                <div id="selectedFiles" class="mt-3"></div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('employee.reports.show', $report) }}" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle me-1"></i> Bekor qilish
                                </a>
                                @if ($report->approved_count == 0 && $report->rejected_count == 0)
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="bi bi-trash me-1"></i> O'chirish
                                    </button>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save me-1"></i> Xisobotni yangilash
                            </button>
                        </div>
                    </form>

                    {{-- Delete form (hidden) --}}
                    @if ($report->approved_count == 0 && $report->rejected_count == 0)
                        <form id="delete-form" action="{{ route('employee.reports.destroy', $report) }}" method="POST"
                            style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

    <style>
        .ck-editor__editable {
            min-height: 400px;
            max-height: 600px;
        }
        
        .ck.ck-editor__main > .ck-editor__editable {
            background: #ffffff !important;
            color: #212529 !important;
            font-size: 15px;
            line-height: 1.8;
        }
        
        .ck.ck-toolbar {
            background: #f8f9fa !important;
            border-bottom: 2px solid #e9ecef !important;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            background: #f8f9fa;
        }

        .file-item .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-item .file-icon {
            font-size: 1.5rem;
        }

        .file-item .file-size {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .file-item .file-actions {
            display: flex;
            gap: 0.5rem;
        }

        .existing-file {
            background: #e7f3ff;
            border-color: #b3d9ff;
        }
    </style>

    <script>
        let editorInstance;
        let currentFileCount = {{ count($attachments) }};
        let removedFiles = [];

        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: {
                    items: [
                        'numberedList',
                        '|',
                        'bold', 'italic',
                        '|',
                        'undo', 'redo'
                    ],
                    shouldNotGroupWhenFull: true
                },
                
                language: 'ru',
                placeholder: "Birinchi vazifa haqida yozing..."
            })
            .then(editor => {
                editorInstance = editor;
                console.log('✅ CKEditor yuklandi');
                
                const contentTextarea = document.getElementById('content');
                const htmlContent = contentTextarea.value;
                editor.setData(htmlContent);
                
                document.getElementById('reportForm').addEventListener('submit', function(e) {
                    const data = editor.getData();
                    document.getElementById('content').value = data;
                    
                    if (!data.includes('<ol>') || !data.includes('<li>')) {
                        e.preventDefault();
                        alert('❌ XATO!\n\nFaqat raqamli ro\'yxat (numbered list) formatida yozing!\n\n👉 Toolbar\'dan "Numbered List" tugmasini bosing.');
                        return false;
                    }
                    
                    const itemCount = (data.match(/<li>/g) || []).length;
                    if (itemCount < 1) {
                        e.preventDefault();
                        alert(`❌ XATO!\n\nKamida 1 ta vazifa yozish kerak!\n\nHozir: ${itemCount} ta`);
                        return false;
                    }
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    const items = tempDiv.querySelectorAll('li');
                    
                    for (let i = 0; i < items.length; i++) {
                        const text = items[i].textContent.trim();
                        if (text.length < 3) {
                            e.preventDefault();
                            alert(`❌ XATO!\n\n${i + 1}-vazifa juda qisqa!\n\nKamida 3 ta belgi yozing.\nHozir: ${text.length} ta belgi`);
                            return false;
                        }
                    }
                    
                    const totalText = tempDiv.textContent.trim();
                    if (totalText.length < 2) {
                        e.preventDefault();
                        alert(`❌ XATO!\n\nXisobot juda qisqa!\n\nKamida 2 ta belgi bo'lishi kerak.\nHozir: ${totalText.length} ta belgi`);
                        return false;
                    }
                    
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Yangilanmoqda...';
                });
            })
            .catch(error => {
                console.error('❌ CKEditor yuklashda xatolik:', error);
                alert('Editor yuklashda xatolik. Sahifani yangilang.');
            });

        // Faylni o'chirish
        function removeFile(filename) {
            if (confirm('Haqiqatan ham bu faylni o\'chirmoqchimisiz?')) {
                const fileElement = document.getElementById('file-' + filename);
                if (fileElement) {
                    fileElement.remove();
                    currentFileCount--;
                    document.getElementById('currentFileCount').textContent = currentFileCount;
                    
                    // O'chiriladigan fayllar ro'yxatiga qo'shish
                    removedFiles.push(filename);
                    const container = document.getElementById('removeFilesContainer');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_attachments[]';
                    input.value = filename;
                    container.appendChild(input);
                }
            }
        }

        // Yangi fayllarni tanlash - FAQAT AGAR INPUT MAVJUD BO'LSA
        @if(auth()->user()->canUploadReportFiles())
        const attachmentsInput = document.getElementById('attachments');
        if (attachmentsInput) {
            attachmentsInput.addEventListener('change', function(e) {
                const files = e.target.files;
                const container = document.getElementById('selectedFiles');
                container.innerHTML = '';

                if (files.length === 0) return;

                const totalFiles = currentFileCount + files.length;
                if (totalFiles > 5) {
                    alert(`❌ Jami maksimal 5 ta fayl bo'lishi mumkin!\nHozirda ${currentFileCount} ta fayl mavjud.`);
                    e.target.value = '';
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);

                    if (file.size > 10 * 1024 * 1024) {
                        alert(`❌ "${file.name}" fayli juda katta! Maksimal 10MB`);
                        e.target.value = '';
                        container.innerHTML = '';
                        return;
                    }

                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    fileItem.innerHTML = `
                        <div class="file-info">
                            <i class="bi bi-file-earmark-text file-icon text-success"></i>
                            <div>
                                <div class="fw-bold">${file.name}</div>
                                <div class="file-size">${fileSize} MB</div>
                            </div>
                        </div>
                        <span class="badge bg-success">Yangi</span>
                    `;
                    container.appendChild(fileItem);
                }
            });
        }
        @endif

        function confirmDelete() {
            if (confirm("❌ Haqiqatan ham bu xisobotni o'chirmoqchimisiz?\n\nBu amalni bekor qilish mumkin emas!")) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
@endsection