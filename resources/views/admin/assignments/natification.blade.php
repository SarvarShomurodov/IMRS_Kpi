@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-bell me-2 text-primary"></i>
                                Bildirishnomalar
                            </h2>
                        </div>
                        
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill">
                                    {{ $unreadCount }} ta o'qilmagan
                                </span>
                            @endif
                            
                            <span class="badge bg-primary rounded-pill">
                                Jami: {{ $notifications->total() }}
                            </span>
                            
                            <!-- Filter tugmalari -->
                            <div class="btn-group" role="group">
                                <a href="{{ route('notifications.index') }}" 
                                   class="btn btn-sm {{ request()->routeIs('notifications.index') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i class="fas fa-list me-1"></i>
                                    Barchasi
                                </a>
                                <a href="{{ route('notifications.unread') }}" 
                                   class="btn btn-sm {{ request()->routeIs('notifications.unread') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i class="fas fa-envelope me-1"></i>
                                    O'qilmaganlar
                                </a>
                            </div>
                            
                            <!-- Barchasini o'qilgan qilish tugmasi -->
                            @if($unreadCount > 0)
                                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" 
                                            onclick="return confirm('Barcha bildirishnomalarni o\'qilgan deb belgilaysizmi?')">
                                        <i class="fas fa-check-double me-1"></i>
                                        Barchasini o'qilgan qilish
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @forelse ($notifications as $notification)
                        <div class="notification-item border-bottom p-4 hover-bg-light transition {{ !$notification->read_at ? 'unread-notification' : '' }}">
                            <div class="d-flex align-items-start">
                                <div class="notification-icon me-3">
                                    <div class="icon-wrapper {{ !$notification->read_at ? 'bg-primary' : 'bg-secondary' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 48px; height: 48px;">
                                        <i class="fas fa-tasks {{ !$notification->read_at ? 'text-primary' : 'text-secondary' }} fs-5"></i>
                                    </div>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-semibold {{ !$notification->read_at ? 'text-dark' : 'text-muted' }}">
                                        {{ $notification->data['message_full'] ?? $notification->data['message'] ?? $notification->data['message_short'] ?? 'Xabar topilmadi' }}
                                    </h5>
                                    
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="far fa-clock me-2"></i>
                                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                                        <span class="mx-2">•</span>
                                        <small>{{ $notification->created_at->format('d.m.Y H:i') }}</small>
                                    </div>
                                </div>
                                
                                <div class="ms-3 d-flex flex-column gap-2 align-items-end">
                                    @if(!$notification->read_at)
                                        <span class="badge bg-danger rounded-pill">Yangi</span>
                                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="O'qilgan deb belgilash">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">O'qilgan</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">Bildirishnomalar topilmadi</h5>
                            <p class="text-muted mb-0">
                                @if(request()->routeIs('notifications.unread'))
                                    Sizda o'qilmagan bildirishnomalar yo'q
                                @else
                                    Sizda hozircha yangi bildirishnomalar yo'q
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
                
                @if($notifications->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="pagination-info me-4">
                                <span class="text-muted">
                                    {{ $notifications->firstItem() }} dan {{ $notifications->lastItem() }} gacha,
                                    jami {{ $notifications->total() }} ta natija
                                </span>
                            </div>
                            <div class="pagination-wrapper">
                                {{ $notifications->appends(request()->query())->links('custom.pagination') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .notification-item {
        transition: all 0.2s ease;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .notification-item:last-child {
        border-bottom: none !important;
    }
    
    .unread-notification {
        background-color: #f0f7ff;
        border-left: 4px solid #0d6efd;
    }
    
    .icon-wrapper {
        transition: all 0.3s ease;
    }
    
    .notification-item:hover .icon-wrapper {
        transform: scale(1.1);
    }
    
    .hover-bg-light {
        cursor: pointer;
    }
    
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .transition {
        transition: all 0.3s ease;
    }
    
    .pagination-info {
        font-size: 0.9rem;
    }
    
    .btn-group {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 6px;
    }
    
    .btn-outline-success:hover {
        transform: scale(1.1);
    }
</style>
@endsection