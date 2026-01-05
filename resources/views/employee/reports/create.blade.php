{{-- resources/views/employee/reports/create.blade.php --}}

@extends('layouts.employee')

@section('title', $title ?? 'Yangi xisobot')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-plus me-2"></i>
                            {{ $title ?? 'Yangi xisobot' }}
                        </h5>
                        <a href="{{ route('employee.reports.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Orqaga
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Period info --}}
                    @if (!empty($period))
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-calendar-check me-2"></i>
                            <strong>Davr:</strong> {{ $period }}
                            @if (($type ?? 'weekly') === 'monthly' && !empty($dates))
                                <br><small class="text-muted">
                                    Aniq oraliq: {{ \Carbon\Carbon::parse($dates['start_date'])->format('d.m.Y') }} -
                                    {{ \Carbon\Carbon::parse($dates['end_date'])->format('d.m.Y') }}
                                </small>
                            @endif
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
                    <form id="reportForm" action="{{ route('employee.reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="type" value="{{ $type ?? 'weekly' }}">
                        @if (!empty($dates['start_date']))
                            <input type="hidden" name="start_date"
                                value="{{ \Carbon\Carbon::parse($dates['start_date'])->format('Y-m-d') }}">
                        @endif
                        @if (!empty($dates['end_date']))
                            <input type="hidden" name="end_date"
                                value="{{ \Carbon\Carbon::parse($dates['end_date'])->format('Y-m-d') }}">
                        @endif

                        <div class="mb-3">
                            <label for="editor" class="form-label fw-bold">
                                Xisobot matni <span class="text-danger">*</span>
                            </label>

                            <div id="editor"></div>
                            <textarea id="content" name="content" style="display:none;">{{ old('content') }}</textarea>

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
                                <div class="mt-2 p-2 bg-white rounded border">
                                    <small class="text-muted"><strong>Misol:</strong></small>
                                    <ol class="mb-0 mt-1" style="font-size: 13px;">
                                        <li>Laravel Boost paketini loyihaga o'rnatdim va konfiguratsiya qildim</li>
                                        <li>Task assignment yaratish funksiyasini ishlab chiqdim</li>
                                        <li>Database index'lar yaratib, performance'ni optimizatsiya qildim</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ Fayl yuklash bo'limi - FAQAT RUXSAT BERILGAN EMAILLAR UCHUN --}}
                        @if(auth()->user()->canUploadReportFiles())
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-paperclip me-1"></i>
                                    Fayllar yuklash (ixtiyoriy)
                                </label>
                                <input type="file" 
                                       name="attachments[]" 
                                       id="attachments" 
                                       class="form-control @error('attachments.*') is-invalid @enderror" 
                                       multiple 
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                
                                @error('attachments')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('attachments.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Maksimal 5 ta fayl (PDF, Word, Excel, Rasm). Har bir fayl 10MB dan oshmasligi kerak.
                                </div>

                                {{-- Tanlangan fayllar ro'yxati --}}
                                <div id="selectedFiles" class="mt-3"></div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('employee.reports.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Bekor qilish
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save me-1"></i> Xisobotni saqlash
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- CKEditor 5 --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

    <style>
        .ck-editor__editable {
            min-height: 400px;
            max-height: 600px;
        }
        
        .ck.ck-editor__main > .ck-editor__editable {
            background: #ffffff;
            color: #212529;
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
    </style>

    <script>
        let editorInstance;

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
                placeholder: "1. Birinchi vazifa haqida yozing..."
            })
            .then(editor => {
                editorInstance = editor;
                console.log('✅ CKEditor yuklandi');
                
                const oldContent = `{{ old('content') }}`;
                if (oldContent && oldContent.trim() !== '') {
                    editor.setData(oldContent);
                } else {
                    editor.setData('');
                }
                
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
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saqlanmoqda...';
                });
            })
            .catch(error => {
                console.error('❌ CKEditor yuklashda xatolik:', error);
                alert('Editor yuklashda xatolik. Sahifani yangilang.');
            });

        // ✅ Fayl tanlash hodisasi - FAQAT AGAR INPUT MAVJUD BO'LSA
        @if(auth()->user()->canUploadReportFiles())
        document.getElementById('attachments').addEventListener('change', function(e) {
            const files = e.target.files;
            const container = document.getElementById('selectedFiles');
            container.innerHTML = '';

            if (files.length === 0) return;

            if (files.length > 5) {
                alert('❌ Maksimal 5 ta fayl yuklash mumkin!');
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
                        <i class="bi bi-file-earmark-text file-icon text-primary"></i>
                        <div>
                            <div class="fw-bold">${file.name}</div>
                            <div class="file-size">${fileSize} MB</div>
                        </div>
                    </div>
                    <span class="badge bg-success">Tanlangan</span>
                `;
                container.appendChild(fileItem);
            }
        });
        @endif
    </script>
@endsection