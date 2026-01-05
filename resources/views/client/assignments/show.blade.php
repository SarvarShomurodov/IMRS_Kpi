@extends('layouts.client')

@section('content')

<!-- Page Header -->
<div class="page-header-wrapper">
    <div class="page-header-content">
        <div class="page-title">
            <i class="fas fa-eye"></i>
            <h3>Vazifalarni ko'rish</h3>
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

<!-- Assignment Details -->
<div class="details-container">
    <div class="details-card">
        <!-- Topshiriq nomi -->
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="detail-content">
                <span class="detail-label">Бажарилган топшириқ номи</span>
                <p class="detail-value">{{ $assignment->name }}</p>
            </div>
        </div>

        <!-- Kim berdi -->
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="detail-content">
                <span class="detail-label">Ким томонидан берилди</span>
                <p class="detail-value">{{ $assignment->who_from }}</p>
            </div>
        </div>

        <!-- Fayl -->
        @if ($assignment->file)
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-paperclip"></i>
            </div>
            <div class="detail-content">
                <span class="detail-label">Yuklangan fayl</span>
                <div class="file-download-wrapper">
                    <a href="{{ asset('storage/assignments/' . basename($assignment->file)) }}" 
                       target="_blank"
                       class="btn-download">
                        <i class="fas fa-download"></i>
                        <span>Faylni ko'rish / yuklash</span>
                    </a>
                    <span class="file-name">{{ basename($assignment->file) }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Sana -->
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="detail-content">
                <span class="detail-label">Топширган санаси</span>
                <p class="detail-value">
                    <span class="date-badge-large">
                        <i class="fas fa-calendar-day"></i>
                        {{ $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d M Y') : 'N/A' }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Kimga topshirildi -->
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="detail-content">
                <span class="detail-label">Кимга топширилди</span>
                <p class="detail-value">{{ $assignment->who_hand }}</p>
            </div>
        </div>

        <!-- Ijrochilar -->
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="detail-content">
                <span class="detail-label">Лойиҳадаги ижрочилар ва ҳиссалар</span>
                <p class="detail-value">{{ $assignment->people ?? 'Ijrochilar haqida ma\'lumot yo\'q' }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="detail-actions">
        @can('update', $assignment)
            <a href="{{ route('assignments.edit', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}" 
               class="btn-action btn-action-warning">
                <i class="fas fa-edit"></i>
                <span>Tahrirlash</span>
            </a>
        @endcan
        
        <!-- Copy Button -->
        <button type="button" class="btn-action btn-action-info" id="copyAssignmentBtn">
            <i class="fas fa-copy"></i>
            <span>Nusxalash</span>
        </button>
        
        @can('delete', $assignment)
            <form action="{{ route('assignments.destroy', ['assignment' => $assignment->id, 'year' => request('year'), 'month' => request('month')]) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Rostdan ham o\'chirmoqchimisiz?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-action-danger">
                    <i class="fas fa-trash"></i>
                    <span>O'chirish</span>
                </button>
            </form>
        @endcan
    </div>
</div>

<!-- Hidden textarea for copying (fallback method) -->
<textarea id="copyContent" style="position: absolute; left: -9999px;">

📝 Топшириқ номи:
{{ $assignment->name }}

👤 Ким томонидан берилди:
{{ $assignment->who_from }}

📅 Топширган санаси:
{{ $assignment->date ? \Carbon\Carbon::parse($assignment->date)->format('d M Y') : 'N/A' }}

✅ Кимга топширилди:
{{ $assignment->who_hand }}

👥 Ижрочилар ва ҳиссалар:
{{ $assignment->people ?? 'Ijrochilar haqida ma\'lumot yo\'q' }}</textarea>

<script>
document.getElementById('copyAssignmentBtn').addEventListener('click', function() {
    const textarea = document.getElementById('copyContent');
    const btn = document.getElementById('copyAssignmentBtn');
    const originalHTML = btn.innerHTML;
    
    // Method 1: Modern clipboard API (HTTPS required)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textarea.value).then(function() {
            // Success
            btn.innerHTML = '<i class="fas fa-check"></i><span>Nusxalandi!</span>';
            btn.style.backgroundColor = '#10b981';
            
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.style.backgroundColor = '';
            }, 2000);
        }).catch(function(err) {
            // Fallback to method 2
            fallbackCopy();
        });
    } else {
        // Method 2: Fallback for HTTP or older browsers
        fallbackCopy();
    }
    
    function fallbackCopy() {
        try {
            textarea.style.position = 'fixed';
            textarea.style.left = '0';
            textarea.style.top = '0';
            textarea.style.opacity = '0';
            textarea.select();
            textarea.setSelectionRange(0, 99999);
            
            const successful = document.execCommand('copy');
            
            if (successful) {
                btn.innerHTML = '<i class="fas fa-check"></i><span>Nusxalandi!</span>';
                btn.style.backgroundColor = '#10b981';
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.style.backgroundColor = '';
                }, 2000);
            } else {
                btn.innerHTML = '<i class="fas fa-times"></i><span>Xatolik!</span>';
                btn.style.backgroundColor = '#ef4444';
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.style.backgroundColor = '';
                }, 2000);
            }
            
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
        } catch (err) {
            console.error('Copy error:', err);
            alert('Nusxalashda xatolik yuz berdi. Iltimos, matnni qo\'lda nusxalang.');
        }
    }
});
</script>

<style>
.btn-action-info {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-action-info:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
}

.btn-action-info i {
    font-size: 16px;
}
</style>

@endsection