@extends('layouts.client')

@section('content')

<!-- Page Header -->
<div class="page-header-wrapper">
    <div class="page-header-content">
        <div class="page-title">
            <i class="fas fa-plus-circle"></i>
            <h3>Vazifalarni qo'shish</h3>
        </div>
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('User'))
            <a href="{{ route('assignments.index',['year' => request('year'), 'month' => request('month')]) }}" 
               class="btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Orqaga</span>
            </a>
        @endif
    </div>
</div>

<!-- Form Container -->
<div class="form-container">
    <form action="{{ route('assignments.store', ['year' => request('year'), 'month' => request('month')]) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="assignment-form">
        @csrf

        <div class="row g-4">
            <!-- Topshiriq nomi -->
            <div class="col-md-6">
                <div class="form-group-custom">
                    <label for="name" class="form-label-custom">
                        <i class="fas fa-file-alt"></i>
                        Бажарилган топшириқ номи
                        <span class="required">*</span>
                    </label>
                    <textarea name="name" 
                              id="name" 
                              class="form-control-custom" 
                              rows="4" 
                              placeholder="Topshiriq nomini kiriting..."
                              required></textarea>
                </div>
            </div>

            <!-- Kim berdi -->
            <div class="col-md-6">
                <div class="form-group-custom">
                    <label for="who_from" class="form-label-custom">
                        <i class="fas fa-user"></i>
                        Ким томонидан берилди
                        <span class="required">*</span>
                    </label>
                    <textarea name="who_from" 
                              id="who_from" 
                              class="form-control-custom" 
                              rows="4" 
                              placeholder="Kim tomonidan berilganini kiriting..."
                              required></textarea>
                </div>
            </div>

            <!-- Fayl -->
            <div class="col-md-6">
                <div class="form-group-custom">
                    <label for="file" class="form-label-custom">
                        <i class="fas fa-paperclip"></i>
                        Fayl yuklash
                    </label>
                    <div class="file-input-wrapper">
                        <input type="file" 
                               class="form-control-file" 
                               id="file" 
                               name="file">
                        <div class="file-input-info">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Faylni tanlang yoki shu yerga torting</span>
                            <small>Ruxsat etilgan formatlar: jpg, jpeg, png, pdf, doc, docx, xlsx, zip</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sana -->
            <div class="col-md-6">
                <div class="form-group-custom">
                    <label for="date" class="form-label-custom">
                        <i class="fas fa-calendar"></i>
                        Sana
                        <span class="required">*</span>
                    </label>
                    <input type="date" 
                           class="form-control-custom" 
                           id="date" 
                           name="date"
                           required>
                </div>
            </div>

            <!-- Kimga topshirildi -->
            <div class="col-md-6">
                <div class="form-group-custom">
                    <label for="who_hand" class="form-label-custom">
                        <i class="fas fa-user-check"></i>
                        Кимга топширилди
                        <span class="required">*</span>
                    </label>
                    <textarea name="who_hand" 
                              id="who_hand" 
                              class="form-control-custom" 
                              rows="4" 
                              placeholder="Kimga topshirilganini kiriting..."
                              required></textarea>
                </div>
            </div>

            <!-- Ijrochilar -->
            <div class="col-md-6">
                <div class="form-group-custom">
                    <label for="people" class="form-label-custom">
                        <i class="fas fa-users"></i>
                        Лойиҳадаги ижрочилар ва ҳиссалар
                    </label>
                    <textarea name="people" 
                              id="people" 
                              class="form-control-custom" 
                              rows="4" 
                              placeholder="Ijrochilar va ularning hissalarini kiriting..."></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i>
                <span>Jo'natish</span>
            </button>
            <a href="{{ route('assignments.index',['year' => request('year'), 'month' => request('month')]) }}" 
               class="btn-cancel">
                <i class="fas fa-times"></i>
                <span>Bekor qilish</span>
            </a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    // File input custom
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const infoSpan = document.querySelector('.file-input-info span');
        if (fileName) {
            infoSpan.textContent = fileName;
            infoSpan.style.color = '#0d6efd';
        }
    });
</script>
@endsection